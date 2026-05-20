<?php

namespace GO\Controllers\Customer;

use GO\Core\BaseController;
use GO\Models\InvoiceModel;

class InvoiceController extends BaseController
{
    private InvoiceModel $invoices;

    public function __construct()
    {
        require_customer();
        $this->invoices = new InvoiceModel();
    }

    public function index(): void
    {
        $userId   = (int)current_user()['id'];
        $invoices = $this->invoices->findByUser($userId);

        $this->view('customer/invoices/index', [
            'title'    => 'Faturalarım',
            'layout'   => 'layouts/panel',
            'invoices' => $invoices,
        ]);
    }

    public function show(string $uuid): void
    {
        $user    = current_user();
        $invoice = $this->invoices->findByUuid($uuid);

        if (!$invoice || (int)$invoice['user_id'] !== (int)$user['id']) {
            flash_error('Fatura bulunamadı.');
            $this->redirect('panel/faturalar');
        }

        $items          = $this->invoices->getItemsByInvoice((int)$invoice['id']);
        $paymentMethods = $this->invoices->getPaymentMethods((int)$invoice['id']);
        $notifications  = $this->getNotifications((int)$invoice['id']);

        $this->view('customer/invoices/show', [
            'title'          => 'Fatura #' . $invoice['invoice_no'],
            'layout'         => 'layouts/panel',
            'invoice'        => $invoice,
            'items'          => $items,
            'paymentMethods' => $paymentMethods,
            'notifications'  => $notifications,
        ]);
    }

    /**
     * Ödeme bildirimi gönder (banka transferi veya online link).
     */
    public function notifyPayment(string $uuid): void
    {
        $this->verifyCsrf();
        $user    = current_user();
        $invoice = $this->invoices->findByUuid($uuid);

        if (!$invoice || (int)$invoice['user_id'] !== (int)$user['id']) {
            $this->jsonError('Fatura bulunamadı.', 404);
        }

        if (!in_array($invoice['status'], ['waiting','payment_pending'], true)) {
            flash_error('Bu fatura için ödeme bildirimi gönderilemez.');
            $this->redirect('panel/faturalar/' . $uuid);
        }

        $methodType = $this->input('method_type', 'bank_transfer');
        $note       = trim($this->input('user_note', ''));

        // Ödeme bildirimi kaydı
        try {
            $pdo  = \GO\Core\Database::getInstance();
            $stmt = $pdo->prepare("
                INSERT INTO payment_notifications
                    (invoice_id, user_id, method_type, user_note, status, kanban_status, priority, sort_order, created_at, updated_at)
                VALUES (?, ?, ?, ?, 'pending', 'pending', 'normal', 0, NOW(), NOW())
            ");
            $stmt->execute([(int)$invoice['id'], (int)$user['id'], $methodType, $note]);
        } catch (\Throwable $e) {
            flash_error('Bildirim kaydedilemedi.');
            $this->redirect('panel/faturalar/' . $uuid);
        }

        // Fatura durumunu güncelle
        $this->invoices->update((int)$invoice['id'], ['status' => 'payment_reported']);

        // Kuyruğa admin bildirimi
        \GO\Services\QueueService::push('admin_notification', [
            'type'       => 'payment_notification',
            'invoice_id' => (int)$invoice['id'],
            'user_id'    => (int)$user['id'],
        ]);

        flash_success('Ödeme bildiriminiz alındı. En kısa sürede onaylanacak.');
        $this->redirect('panel/faturalar/' . $uuid);
    }

    private function getNotifications(int $invoiceId): array
    {
        try {
            $pdo  = \GO\Core\Database::getInstance();
            $stmt = $pdo->prepare(
                "SELECT * FROM payment_notifications WHERE invoice_id = ? ORDER BY created_at DESC"
            );
            $stmt->execute([$invoiceId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable) {
            return [];
        }
    }
}
