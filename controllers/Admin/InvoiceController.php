<?php

namespace GO\Controllers\Admin;

use GO\Core\BaseController;
use GO\Models\InvoiceModel;

class InvoiceController extends BaseController
{
    private InvoiceModel $invoices;

    public function __construct()
    {
        require_admin();
        $this->invoices = new InvoiceModel();
    }

    public function index(): void
    {
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $status  = $_GET['status'] ?? '';
        $perPage = 20;
        $offset  = ($page - 1) * $perPage;

        try {
            $pdo = \GO\Core\Database::getInstance();

            $where  = $status ? "AND i.status = ?" : '';
            $params = $status ? [$status] : [];

            $totalRow = $pdo->prepare("SELECT COUNT(*) as cnt FROM invoices i WHERE i.deleted_at IS NULL {$where}");
            $totalRow->execute($params);
            $total = (int)($totalRow->fetch(\PDO::FETCH_ASSOC)['cnt'] ?? 0);

            $stmt = $pdo->prepare(
                "SELECT i.*, u.full_name as user_name, u.email as user_email
                 FROM invoices i
                 LEFT JOIN users u ON u.id = i.user_id
                 WHERE i.deleted_at IS NULL {$where}
                 ORDER BY i.created_at DESC
                 LIMIT {$perPage} OFFSET {$offset}"
            );
            $stmt->execute($params);
            $invoices = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable) {
            $invoices = [];
            $total    = 0;
        }

        $this->view('admin/invoices/index', [
            'title'    => 'Faturalar',
            'layout'   => 'layouts/admin',
            'invoices' => $invoices,
            'total'    => $total,
            'page'     => $page,
            'perPage'  => $perPage,
            'status'   => $status,
        ]);
    }

    public function show(string $uuid): void
    {
        $invoice = $this->invoices->findByUuid($uuid);

        if (!$invoice) {
            flash_error('Fatura bulunamadı.');
            $this->redirect('admin/faturalar');
        }

        try {
            $pdo   = \GO\Core\Database::getInstance();
            $user  = $pdo->prepare("SELECT full_name, email FROM users WHERE id = ? LIMIT 1");
            $user->execute([$invoice['user_id']]);
            $owner = $user->fetch(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable) {
            $owner = [];
        }

        $this->view('admin/invoices/show', [
            'title'   => 'Fatura Detayı',
            'layout'  => 'layouts/admin',
            'invoice' => $invoice,
            'owner'   => $owner,
        ]);
    }

    public function create(): void
    {
        $this->verifyCsrf();
        require_super_admin();

        $userId  = (int)($_POST['user_id'] ?? 0);
        $total   = (float)($_POST['total'] ?? 0);
        $desc    = trim($_POST['description'] ?? '');
        $dueDate = trim($_POST['due_date'] ?? '');

        if ($userId < 1 || $total <= 0) {
            flash_error('Geçerli kullanıcı ve tutar gerekli.');
            $this->redirect('admin/faturalar');
        }

        try {
            $prefix   = get_setting('invoice_prefix', 'GO-');
            $pdo      = \GO\Core\Database::getInstance();
            $countRow = $pdo->query("SELECT COUNT(*) as cnt FROM invoices")->fetch(\PDO::FETCH_ASSOC);
            $invoiceNo = $prefix . str_pad((int)($countRow['cnt'] ?? 0) + 1, 4, '0', STR_PAD_LEFT);

            $this->invoices->create([
                'uuid'        => \GO\Core\BaseModel::generateUuid(),
                'user_id'     => $userId,
                'invoice_no'  => $invoiceNo,
                'total'       => $total,
                'description' => $desc,
                'due_date'    => $dueDate ?: null,
                'status'      => 'pending',
            ]);

            (new \GO\Services\AuditService())->log(
                \GO\Services\AuditService::ACTION_INVOICE_CREATED,
                'invoice',
                0,
                null,
                ['user_id' => $userId, 'total' => $total],
                'admin',
                (int)current_admin()['id']
            );

            flash_success("Fatura {$invoiceNo} oluşturuldu.");
        } catch (\Throwable $e) {
            flash_error('Fatura oluşturulamadı.');
        }

        $this->redirect('admin/faturalar');
    }

    public function approve(string $uuid): void
    {
        $this->verifyCsrf();

        $invoice = $this->invoices->findByUuid($uuid);
        if (!$invoice) {
            $this->jsonError('Fatura bulunamadı.', 404);
        }

        $this->invoices->update((int)$invoice['id'], ['status' => 'paid']);

        (new \GO\Services\AuditService())->log(
            \GO\Services\AuditService::ACTION_PAYMENT_APPROVED,
            'invoice',
            (int)$invoice['id'],
            ['status' => $invoice['status']],
            ['status' => 'paid'],
            'admin',
            (int)current_admin()['id']
        );

        $this->jsonSuccess([], 'Fatura onaylandı.');
    }
}
