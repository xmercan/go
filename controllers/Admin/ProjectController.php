<?php

namespace GO\Controllers\Admin;

use GO\Core\BaseController;
use GO\Models\ProjectModel;

class ProjectController extends BaseController
{
    private ProjectModel $projects;

    public function __construct()
    {
        require_admin();
        $this->projects = new ProjectModel();
    }

    public function index(): void
    {
        $page     = max(1, (int)($_GET['page'] ?? 1));
        $status   = $_GET['status'] ?? '';
        $perPage  = 20;
        $offset   = ($page - 1) * $perPage;

        try {
            $pdo = \GO\Core\Database::getInstance();

            $where  = $status ? "AND p.process_status = ?" : '';
            $params = $status ? [$status] : [];

            $totalRow = $pdo->prepare("SELECT COUNT(*) as cnt FROM projects p WHERE p.deleted_at IS NULL {$where}");
            $totalRow->execute($params);
            $total = (int)($totalRow->fetch(\PDO::FETCH_ASSOC)['cnt'] ?? 0);

            $stmt = $pdo->prepare(
                "SELECT p.*, u.full_name as user_name, u.email as user_email
                 FROM projects p
                 LEFT JOIN users u ON u.id = p.user_id
                 WHERE p.deleted_at IS NULL {$where}
                 ORDER BY p.created_at DESC
                 LIMIT {$perPage} OFFSET {$offset}"
            );
            $stmt->execute($params);
            $projects = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            $projects = [];
            $total    = 0;
        }

        $this->view('admin/projects/index', [
            'title'    => 'Projeler',
            'layout'   => 'layouts/admin',
            'projects' => $projects,
            'total'    => $total,
            'page'     => $page,
            'perPage'  => $perPage,
            'status'   => $status,
        ]);
    }

    public function show(string $uuid): void
    {
        $project = $this->projects->findByUuid($uuid);

        if (!$project) {
            flash_error('Proje bulunamadı.');
            $this->redirect('admin/projeler');
        }

        try {
            $pdo    = \GO\Core\Database::getInstance();
            $user   = $pdo->prepare("SELECT full_name, email FROM users WHERE id = ? LIMIT 1");
            $user->execute([$project['user_id']]);
            $owner  = $user->fetch(\PDO::FETCH_ASSOC) ?: [];

            $msgs   = $pdo->prepare(
                "SELECT * FROM chat_messages WHERE project_id = ? ORDER BY created_at ASC"
            );
            $msgs->execute([$project['id']]);
            $messages = $msgs->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable) {
            $owner    = [];
            $messages = [];
        }

        $this->view('admin/projects/show', [
            'title'    => 'Proje Detayı',
            'layout'   => 'layouts/admin',
            'project'  => $project,
            'owner'    => $owner,
            'messages' => $messages,
        ]);
    }

    public function updateStatus(string $uuid): void
    {
        $this->verifyCsrf();

        $project = $this->projects->findByUuid($uuid);
        if (!$project) {
            $this->jsonError('Proje bulunamadı.', 404);
        }

        $status  = $_POST['status'] ?? '';
        $allowed = ['draft', 'queued', 'in_review', 'completed', 'rejected'];

        if (!in_array($status, $allowed, true)) {
            $this->jsonError('Geçersiz durum.', 422);
        }

        $this->projects->update((int)$project['id'], ['process_status' => $status]);

        $this->jsonSuccess(['status' => $status], 'Durum güncellendi.');
    }

    public function addNote(string $uuid): void
    {
        $this->verifyCsrf();

        $project = $this->projects->findByUuid($uuid);
        if (!$project) {
            $this->jsonError('Proje bulunamadı.', 404);
        }

        $note = trim($_POST['note'] ?? '');
        if (empty($note)) {
            $this->jsonError('Not boş olamaz.', 422);
        }

        $this->projects->update((int)$project['id'], ['admin_notes' => $note]);

        $this->jsonSuccess([], 'Not kaydedildi.');
    }
}
