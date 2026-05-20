<?php

namespace GO\Controllers\Customer;

use GO\Core\BaseController;
use GO\Models\SupportTicketModel;
use GO\Services\MailService;

class SupportController extends BaseController
{
    private SupportTicketModel $tickets;

    public function __construct()
    {
        require_customer();
        $this->tickets = new SupportTicketModel();
    }

    public function index(): void
    {
        $userId  = (int)current_user()['id'];
        $tickets = $this->tickets->findByUser($userId);

        $this->view('customer/support/index', [
            'title'   => 'Destek Talepleri',
            'layout'  => 'layouts/panel',
            'tickets' => $tickets,
        ]);
    }

    public function create(): void
    {
        $projects = $this->getUserProjects();
        $this->view('customer/support/create', [
            'title'    => 'Yeni Destek Talebi',
            'layout'   => 'layouts/panel',
            'projects' => $projects,
        ]);
    }

    public function store(): void
    {
        $this->verifyCsrf();
        $user   = current_user();
        $userId = (int)$user['id'];

        $subject   = trim($this->input('subject', ''));
        $category  = $this->input('category', 'genel');
        $priority  = $this->input('priority', 'normal');
        $message   = trim($this->input('message', ''));
        $projectId = (int)$this->input('project_id', 0);

        if (strlen($subject) < 5 || strlen($message) < 10) {
            flash_error('Konu en az 5, mesaj en az 10 karakter olmalı.');
            $this->redirect('panel/destek/yeni');
        }

        $validPriorities = ['low','normal','high','urgent'];
        if (!in_array($priority, $validPriorities, true)) $priority = 'normal';

        $ticketId = $this->tickets->create([
            'uuid'          => \GO\Core\BaseModel::generateUuid(),
            'user_id'       => $userId,
            'project_id'    => $projectId ?: null,
            'subject'       => $subject,
            'category'      => $category,
            'priority'      => $priority,
            'status'        => 'open',
            'kanban_status' => 'pending',
        ]);

        // İlk mesajı kaydet
        $this->tickets->insertReply($ticketId, 'user', $userId, $message);

        // Kuyruğa admin bildirimi
        \GO\Services\QueueService::push('admin_notification', [
            'type'      => 'support_ticket',
            'ticket_id' => $ticketId,
            'user_id'   => $userId,
        ]);

        // Mail: ticket oluşturuldu
        (new MailService())->queue($user['email'], 'ticket_created', [
            'name'    => $user['full_name'],
            'subject' => $subject,
        ]);

        $ticket = $this->tickets->find($ticketId);
        flash_success('Destek talebiniz oluşturuldu. Ekibimiz en kısa sürede yanıtlayacak.');
        $this->redirect('panel/destek/' . ($ticket['uuid'] ?? ''));
    }

    public function show(string $uuid): void
    {
        $user   = current_user();
        $ticket = $this->tickets->findByUuid($uuid);

        if (!$ticket || (int)$ticket['user_id'] !== (int)$user['id']) {
            flash_error('Talep bulunamadı.');
            $this->redirect('panel/destek');
        }

        $replies = $this->tickets->getReplies((int)$ticket['id']);

        $this->view('customer/support/show', [
            'title'  => 'Ticket: ' . e($ticket['subject']),
            'layout' => 'layouts/panel',
            'ticket' => $ticket,
            'replies'=> $replies,
        ]);
    }

    public function reply(string $uuid): void
    {
        $this->verifyCsrf();
        $user   = current_user();
        $ticket = $this->tickets->findByUuid($uuid);

        if (!$ticket || (int)$ticket['user_id'] !== (int)$user['id']) {
            $this->jsonError('Erişim reddedildi.', 403);
        }

        if ($ticket['status'] === 'closed') {
            flash_error('Kapalı ticket\'a yanıt verilemez.');
            $this->redirect('panel/destek/' . $uuid);
        }

        $message = trim($this->input('message', ''));
        if (strlen($message) < 2) {
            flash_error('Yanıt boş olamaz.');
            $this->redirect('panel/destek/' . $uuid);
        }

        $this->tickets->insertReply((int)$ticket['id'], 'user', (int)$user['id'], $message);
        $this->tickets->update((int)$ticket['id'], [
            'status'     => 'open',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        flash_success('Yanıtınız eklendi.');
        $this->redirect('panel/destek/' . $uuid);
    }

    private function getUserProjects(): array
    {
        try {
            $pdo  = \GO\Core\Database::getInstance();
            $stmt = $pdo->prepare(
                "SELECT id, name FROM projects WHERE user_id = ? AND deleted_at IS NULL ORDER BY updated_at DESC"
            );
            $stmt->execute([(int)current_user()['id']]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable) {
            return [];
        }
    }
}
