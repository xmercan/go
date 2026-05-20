<?php

namespace GO\Controllers\Admin;

use GO\Core\BaseController;
use GO\Models\DomainRequestModel;
use GO\Models\HostingRequestModel;
use GO\Models\SoftwareRequestModel;
use GO\Models\SupportTicketModel;

class KanbanController extends BaseController
{
    public function __construct()
    {
        require_admin();
    }

    public function index(): void
    {
        $type = $_GET['type'] ?? 'domain';
        $validTypes = ['domain','hosting','software','support','payment'];
        if (!in_array($type, $validTypes, true)) $type = 'domain';

        $columns = ['pending', 'reviewing', 'in_progress', 'completed', 'rejected'];
        $cards   = $this->getCards($type);

        // Sütunlara göre ayır
        $board = [];
        foreach ($columns as $col) {
            $board[$col] = array_filter($cards, fn($c) => ($c['kanban_status'] ?? 'pending') === $col);
        }

        $this->view('admin/kanban/index', [
            'title'   => 'Kanban — ' . ucfirst($type),
            'layout'  => 'layouts/admin',
            'type'    => $type,
            'board'   => $board,
            'columns' => $columns,
        ]);
    }

    /**
     * AJAX: Kart sürükle bırak sonrası status güncelle.
     */
    public function updateStatus(): void
    {
        require_admin();
        $this->verifyCsrf();

        if (!$this->isAjax()) {
            $this->jsonError('AJAX gerekli.', 400);
        }

        $type      = $_POST['type'] ?? '';
        $id        = (int)($_POST['id'] ?? 0);
        $status    = $_POST['status'] ?? '';
        $sortOrder = (int)($_POST['sort_order'] ?? 0);

        $validStatuses = ['pending','reviewing','in_progress','completed','rejected'];
        if (!in_array($status, $validStatuses, true) || $id < 1) {
            $this->jsonError('Geçersiz veri.', 422);
        }

        try {
            switch ($type) {
                case 'domain':
                    (new DomainRequestModel())->updateKanbanStatus($id, $status, $sortOrder);
                    break;
                case 'hosting':
                    (new HostingRequestModel())->updateKanbanStatus($id, $status, $sortOrder);
                    break;
                case 'software':
                    (new SoftwareRequestModel())->updateKanbanStatus($id, $status, $sortOrder);
                    break;
                case 'support':
                    $ticketStatus = $status === 'completed' ? 'closed' : ($status === 'in_progress' ? 'answered' : 'open');
                    $pdo = \GO\Core\Database::getInstance();
                    $pdo->prepare("UPDATE support_tickets SET kanban_status = ?, sort_order = ?, status = ?, updated_at = NOW() WHERE id = ?")
                        ->execute([$status, $sortOrder, $ticketStatus, $id]);
                    break;
                case 'payment':
                    $payStatus = $status === 'completed' ? 'approved' : ($status === 'rejected' ? 'rejected' : 'pending');
                    $pdo = \GO\Core\Database::getInstance();
                    $pdo->prepare("UPDATE payment_notifications SET kanban_status = ?, sort_order = ?, status = ?, updated_at = NOW() WHERE id = ?")
                        ->execute([$status, $sortOrder, $payStatus, $id]);

                    // Ödeme onaylandıysa faturayı güncelle
                    if ($status === 'completed') {
                        $pn = $pdo->prepare("SELECT invoice_id FROM payment_notifications WHERE id = ?");
                        $pn->execute([$id]);
                        $row = $pn->fetch(\PDO::FETCH_ASSOC);
                        if ($row) {
                            $pdo->prepare("UPDATE invoices SET status = 'paid', updated_at = NOW() WHERE id = ?")
                                ->execute([$row['invoice_id']]);
                        }
                    }
                    break;
                default:
                    $this->jsonError('Geçersiz tür.', 400);
            }

            // Audit log
            (new \GO\Services\AuditService())->log(
                'kanban_move',
                $type . '_request',
                $id,
                null,
                ['status' => $status],
                'admin',
                (int)current_admin()['id']
            );

            $this->jsonSuccess(['id' => $id, 'status' => $status], 'Güncellendi.');
        } catch (\Throwable $e) {
            $this->jsonError('Güncelleme başarısız: ' . $e->getMessage(), 500);
        }
    }

    private function getCards(string $type): array
    {
        return match($type) {
            'domain'   => (new DomainRequestModel())->kanbanList(),
            'hosting'  => (new HostingRequestModel())->kanbanList(),
            'software' => (new SoftwareRequestModel())->kanbanList(),
            'support'  => (new SupportTicketModel())->kanbanList(),
            'payment'  => $this->getPaymentCards(),
            default    => [],
        };
    }

    private function getPaymentCards(): array
    {
        try {
            $pdo  = \GO\Core\Database::getInstance();
            $stmt = $pdo->prepare(
                "SELECT pn.*, u.full_name as user_name, i.invoice_no, i.total
                 FROM payment_notifications pn
                 LEFT JOIN users u ON u.id = pn.user_id
                 LEFT JOIN invoices i ON i.id = pn.invoice_id
                 ORDER BY pn.priority DESC, pn.sort_order ASC, pn.created_at ASC"
            );
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable) {
            return [];
        }
    }
}
