<?php

namespace GO\Controllers\Admin;

use GO\Core\BaseController;

class LogController extends BaseController
{
    public function __construct()
    {
        require_admin();
    }

    public function index(): void
    {
        $tab     = $_GET['tab'] ?? 'activity';
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 50;
        $offset  = ($page - 1) * $perPage;

        $logs  = [];
        $total = 0;

        try {
            $pdo = \GO\Core\Database::getInstance();

            switch ($tab) {
                case 'login':
                    $total = (int)$pdo->query("SELECT COUNT(*) FROM login_logs")->fetchColumn();
                    $stmt  = $pdo->prepare("SELECT * FROM login_logs ORDER BY created_at DESC LIMIT ? OFFSET ?");
                    $stmt->execute([$perPage, $offset]);
                    $logs  = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                    break;

                case 'audit':
                    $total = (int)$pdo->query("SELECT COUNT(*) FROM audit_trail")->fetchColumn();
                    $stmt  = $pdo->prepare(
                        "SELECT at.*, a.full_name as actor_name
                         FROM audit_trail at
                         LEFT JOIN admins a ON a.id = at.actor_id
                         ORDER BY at.created_at DESC LIMIT ? OFFSET ?"
                    );
                    $stmt->execute([$perPage, $offset]);
                    $logs = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                    break;

                case 'email':
                    $total = (int)$pdo->query("SELECT COUNT(*) FROM email_logs")->fetchColumn();
                    $stmt  = $pdo->prepare("SELECT * FROM email_logs ORDER BY created_at DESC LIMIT ? OFFSET ?");
                    $stmt->execute([$perPage, $offset]);
                    $logs  = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                    break;

                default: // activity
                    $tab   = 'activity';
                    $total = (int)$pdo->query("SELECT COUNT(*) FROM activity_logs")->fetchColumn();
                    $stmt  = $pdo->prepare(
                        "SELECT al.*, u.full_name as user_name, a.full_name as admin_name
                         FROM activity_logs al
                         LEFT JOIN users u  ON al.actor_type='user' AND al.actor_id = u.id
                         LEFT JOIN admins a ON al.actor_type='admin' AND al.actor_id = a.id
                         ORDER BY al.created_at DESC LIMIT ? OFFSET ?"
                    );
                    $stmt->execute([$perPage, $offset]);
                    $logs = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            }
        } catch (\Throwable) {}

        $this->view('admin/logs/index', [
            'title'   => 'Sistem Logları',
            'layout'  => 'layouts/admin',
            'tab'     => $tab,
            'logs'    => $logs,
            'total'   => $total,
            'page'    => $page,
            'perPage' => $perPage,
        ]);
    }

    public function deleted(): void
    {
        try {
            $pdo  = \GO\Core\Database::getInstance();
            $stmt = $pdo->prepare("SELECT * FROM deleted_records ORDER BY created_at DESC LIMIT 100");
            $stmt->execute();
            $records = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable) {
            $records = [];
        }

        $this->view('admin/logs/deleted', [
            'title'   => 'Silinen Kayıtlar',
            'layout'  => 'layouts/admin',
            'records' => $records,
        ]);
    }
}
