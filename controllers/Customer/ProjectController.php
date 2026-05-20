<?php

namespace GO\Controllers\Customer;

use GO\Core\BaseController;
use GO\Models\ProjectModel;
use GO\Models\ChatMessageModel;

class ProjectController extends BaseController
{
    private ProjectModel $projects;

    public function __construct()
    {
        require_customer();
        $this->projects = new ProjectModel();
    }

    public function index(): void
    {
        $userId     = (int)current_user()['id'];
        $myProjects = $this->projects->findByUser($userId);

        $this->view('customer/projects/index', [
            'title'      => 'Projelerim',
            'layout'     => 'layouts/panel',
            'myProjects' => $myProjects,
        ]);
    }

    public function show(string $uuid): void
    {
        $user    = current_user();
        $project = $this->projects->findByUuid($uuid);

        if (!$project || (int)$project['user_id'] !== (int)$user['id']) {
            flash_error('Proje bulunamadı.');
            $this->redirect('panel/projeler');
        }

        $messages   = new ChatMessageModel();
        $history    = $messages->getHistory((int)$project['id']);
        $activities = $this->getActivities((int)$project['id']);
        $files      = $this->getGoWebFiles((int)$project['id']);

        $this->view('customer/projects/show', [
            'title'      => e($project['name'] ?? 'Proje'),
            'layout'     => 'layouts/panel',
            'project'    => $project,
            'history'    => $history,
            'activities' => $activities,
            'files'      => $files,
        ]);
    }

    /**
     * ZIP export: projeye ait GO! Web dosyalarını indir.
     */
    public function export(string $uuid): void
    {
        $user    = current_user();
        $project = $this->projects->findByUuid($uuid);

        if (!$project || (int)$project['user_id'] !== (int)$user['id']) {
            flash_error('Proje bulunamadı.');
            $this->redirect('panel/projeler');
        }

        $files = $this->getGoWebFiles((int)$project['id']);
        if (empty($files)) {
            flash_info('Bu proje için henüz dosya yok.');
            $this->redirect('panel/projeler/' . $uuid);
        }

        if (!extension_loaded('zip')) {
            flash_error('ZIP export için sunucuda ZipArchive gerekli.');
            $this->redirect('panel/projeler/' . $uuid);
        }

        $tmpFile  = tempnam(sys_get_temp_dir(), 'go_export_');
        $zipPath  = $tmpFile . '.zip';
        $zip      = new \ZipArchive();

        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            flash_error('ZIP dosyası oluşturulamadı.');
            $this->redirect('panel/projeler/' . $uuid);
        }

        $fileCount = 0;
        foreach ($files as $file) {
            $safePath = ltrim($file['file_path'], '/');
            // Sandbox kontrolü: yalnızca storage/sandbox altındaki dosyalara izin ver
            $fullPath = GO_ROOT . '/storage/sandbox/' . $safePath;
            if (file_exists($fullPath) && is_file($fullPath)) {
                $zip->addFile($fullPath, $file['file_name']);
                $fileCount++;
            } elseif (!empty($file['content'])) {
                // DB'de saklanan içerik
                $zip->addFromString($file['file_name'], $file['content']);
                $fileCount++;
            }
        }

        $zip->close();

        // Log kaydı
        $this->logExport((int)$project['id'], (int)$user['id'], $fileCount, file_exists($zipPath) ? filesize($zipPath) : 0);

        // İndir
        $filename = 'go_project_' . ($project['name'] ?? $uuid) . '_' . date('Ymd') . '.zip';
        $filename = preg_replace('/[^a-zA-Z0-9_\-.]/', '_', $filename);

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($zipPath));
        header('Cache-Control: no-store, no-cache, must-revalidate');
        readfile($zipPath);

        unlink($zipPath);
        unlink($tmpFile);
        exit;
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function getActivities(int $projectId): array
    {
        try {
            $pdo  = \GO\Core\Database::getInstance();
            $stmt = $pdo->prepare(
                "SELECT * FROM project_activities WHERE project_id = ? ORDER BY created_at ASC LIMIT 50"
            );
            $stmt->execute([$projectId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable) {
            return [];
        }
    }

    private function getGoWebFiles(int $projectId): array
    {
        try {
            $pdo  = \GO\Core\Database::getInstance();
            $stmt = $pdo->prepare(
                "SELECT * FROM go_web_files WHERE project_id = ? AND deleted_at IS NULL ORDER BY file_name ASC"
            );
            $stmt->execute([$projectId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable) {
            return [];
        }
    }

    private function logExport(int $projectId, int $userId, int $fileCount, int $zipSize): void
    {
        try {
            $pdo  = \GO\Core\Database::getInstance();
            $stmt = $pdo->prepare("
                INSERT INTO project_export_logs (uuid, project_id, user_id, exported_by, file_count, zip_size, ip_address, created_at)
                VALUES (?, ?, ?, 'user', ?, ?, ?, NOW())
            ");
            $stmt->execute([
                \GO\Core\BaseModel::generateUuid(),
                $projectId,
                $userId,
                $fileCount,
                $zipSize,
                substr($_SERVER['REMOTE_ADDR'] ?? '', 0, 45),
            ]);
        } catch (\Throwable) {}
    }
}
