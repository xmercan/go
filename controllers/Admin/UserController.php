<?php

namespace GO\Controllers\Admin;

use GO\Core\BaseController;
use GO\Models\UserModel;

class UserController extends BaseController
{
    private UserModel $users;

    public function __construct()
    {
        require_admin();
        $this->users = new UserModel();
    }

    public function index(): void
    {
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $search  = trim($_GET['q'] ?? '');
        $perPage = 20;
        $offset  = ($page - 1) * $perPage;

        try {
            $pdo = \GO\Core\Database::getInstance();

            $where  = $search ? "AND (full_name LIKE ? OR email LIKE ?)" : '';
            $params = $search ? ["%{$search}%", "%{$search}%"] : [];

            $totalRow = $pdo->prepare("SELECT COUNT(*) as cnt FROM users WHERE deleted_at IS NULL {$where}");
            $totalRow->execute($params);
            $total = (int)($totalRow->fetch(\PDO::FETCH_ASSOC)['cnt'] ?? 0);

            $stmt = $pdo->prepare(
                "SELECT * FROM users WHERE deleted_at IS NULL {$where}
                 ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}"
            );
            $stmt->execute($params);
            $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable) {
            $users = [];
            $total = 0;
        }

        $this->view('admin/users/index', [
            'title'   => 'Kullanıcılar',
            'layout'  => 'layouts/admin',
            'users'   => $users,
            'total'   => $total,
            'page'    => $page,
            'perPage' => $perPage,
            'search'  => $search,
        ]);
    }

    public function show(int $id): void
    {
        $user = $this->users->find($id);

        if (!$user) {
            flash_error('Kullanıcı bulunamadı.');
            $this->redirect('admin/kullanicilar');
        }

        try {
            $pdo = \GO\Core\Database::getInstance();

            $projects = $pdo->prepare(
                "SELECT id, uuid, name, process_status, created_at FROM projects WHERE user_id = ? AND deleted_at IS NULL ORDER BY created_at DESC LIMIT 10"
            );
            $projects->execute([$id]);
            $recentProjects = $projects->fetchAll(\PDO::FETCH_ASSOC);

            $invoices = $pdo->prepare(
                "SELECT id, uuid, invoice_no, total, status, created_at FROM invoices WHERE user_id = ? AND deleted_at IS NULL ORDER BY created_at DESC LIMIT 10"
            );
            $invoices->execute([$id]);
            $recentInvoices = $invoices->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable) {
            $recentProjects = [];
            $recentInvoices = [];
        }

        $this->view('admin/users/show', [
            'title'          => 'Kullanıcı Detayı',
            'layout'         => 'layouts/admin',
            'user'           => $user,
            'recentProjects' => $recentProjects,
            'recentInvoices' => $recentInvoices,
        ]);
    }

    public function updateStatus(int $id): void
    {
        $this->verifyCsrf();

        $user = $this->users->find($id);
        if (!$user) {
            $this->jsonError('Kullanıcı bulunamadı.', 404);
        }

        $status  = $_POST['status'] ?? '';
        $allowed = ['active', 'suspended'];

        if (!in_array($status, $allowed, true)) {
            $this->jsonError('Geçersiz durum.', 422);
        }

        $this->users->update($id, ['status' => $status]);

        (new \GO\Services\AuditService())->log(
            'user_status_changed',
            'user',
            $id,
            ['status' => $user['status']],
            ['status' => $status],
            'admin',
            (int)current_admin()['id']
        );

        $this->jsonSuccess(['status' => $status], 'Kullanıcı durumu güncellendi.');
    }
}
