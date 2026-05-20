<?php

namespace GO\Controllers\Admin;

use GO\Core\BaseController;
use GO\Models\SupportTicketModel;
use GO\Services\MailService;

class SupportController extends BaseController
{
    private SupportTicketModel $tickets;

    public function __construct()
    {
        require_admin();
        $this->tickets = new SupportTicketModel();
    }

    public function index(): void
    {
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $status  = $_GET['status'] ?? '';
        $perPage = 20;
        $offset  = ($page - 1) * $perPage;

        try {
            $pdo = \GO\Core\Database::getInstance();

            $where  = $status ? "AND t.status = ?" : '';
            $params = $status ? [$status] : [];

            $totalRow = $pdo->prepare(
                "SELECT COUNT(*) as cnt FROM support_tickets t WHERE t.deleted_at IS NULL {$where}"
            );
            $totalRow->execute($params);
            $total = (int)($totalRow->fetch(\PDO::FETCH_ASSOC)['cnt'] ?? 0);

            $stmt = $pdo->prepare(
                "SELECT t.*, u.full_name as user_name, u.email as user_email
                 FROM support_tickets t
                 LEFT JOIN users u ON u.id = t.user_id
                 WHERE t.deleted_at IS NULL {$where}
                 ORDER BY
                   FIELD(t.priority, 'urgent','high','normal','low'),
                   t.updated_at DESC
                 LIMIT {$perPage} OFFSET {$offset}"
            );
            $stmt->execute($params);
            $tickets = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable) {
            $tickets = [];
            $total   = 0;
        }

        $this->view('admin/support/index', [
            'title'   => 'Destek Talepleri',
            'layout'  => 'layouts/admin',
            'tickets' => $tickets,
            'total'   => $total,
            'page'    => $page,
            'perPage' => $perPage,
            'status'  => $status,
        ]);
    }

    public function show(string $uuid): void
    {
        $ticket = $this->tickets->findByUuid($uuid);

        if (!$ticket) {
            flash_error('Talep bulunamadı.');
            $this->redirect('admin/destek');
        }

        $replies = $this->tickets->getReplies((int)$ticket['id']);

        $this->view('admin/support/show', [
            'title'   => 'Destek Talebi: ' . e($ticket['subject']),
            'layout'  => 'layouts/admin',
            'ticket'  => $ticket,
            'replies' => $replies,
        ]);
    }

    public function reply(string $uuid): void
    {
        $this->verifyCsrf();

        $ticket = $this->tickets->findByUuid($uuid);
        if (!$ticket) {
            $this->jsonError('Talep bulunamadı.', 404);
        }

        $message = trim($_POST['message'] ?? '');
        if (strlen($message) < 2) {
            flash_error('Yanıt boş olamaz.');
            $this->redirect('admin/destek/' . $uuid);
        }

        $adminId = (int)current_admin()['id'];
        $this->tickets->insertReply((int)$ticket['id'], 'admin', $adminId, $message);
        $this->tickets->update((int)$ticket['id'], [
            'status'     => 'answered',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Mail: admin yanıt
        try {
            (new MailService())->queue($ticket['user_email'], 'ticket_replied', [
                'name'    => $ticket['user_name'],
                'subject' => $ticket['subject'],
                'message' => $message,
            ]);
        } catch (\Throwable) {}

        flash_success('Yanıtınız gönderildi.');
        $this->redirect('admin/destek/' . $uuid);
    }

    public function close(string $uuid): void
    {
        $this->verifyCsrf();

        $ticket = $this->tickets->findByUuid($uuid);
        if (!$ticket) {
            $this->jsonError('Talep bulunamadı.', 404);
        }

        $this->tickets->update((int)$ticket['id'], [
            'status'        => 'closed',
            'kanban_status' => 'completed',
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);

        $this->jsonSuccess([], 'Talep kapatıldı.');
    }
}
