<?php

namespace GO\Controllers;

use GO\Core\BaseController;
use GO\Models\ProjectModel;
use GO\Models\ChatMessageModel;
use GO\Services\ChatStateMachine;
use GO\Services\RateLimitService;

class ChatController extends BaseController
{
    private ProjectModel     $projects;
    private ChatMessageModel $messages;
    private ChatStateMachine $machine;
    private RateLimitService $rateLimit;

    public function __construct()
    {
        require_customer();
        $this->projects  = new ProjectModel();
        $this->messages  = new ChatMessageModel();
        $this->machine   = new ChatStateMachine();
        $this->rateLimit = new RateLimitService();
    }

    /**
     * Chat arayüzü — yeni proje oluştur veya mevcut projeyi aç.
     */
    public function index(): void
    {
        $user = current_user();
        $userId = (int)$user['id'];

        // Tamamlanmamış draft proje var mı?
        $project = $this->findDraftProject($userId);

        if (!$project) {
            // Yeni proje oluştur
            $projectId = $this->projects->create([
                'user_id'        => $userId,
                'process_status' => 'draft',
                'name'           => 'Yeni Proje',
            ]);
            $project   = $this->projects->find($projectId);

            // İlk mesajı oluştur
            $this->machine->initProject($projectId, $userId);
        }

        $history = $this->messages->getHistory((int)$project['id']);

        $this->view('customer/chat/index', [
            'title'   => 'GO! Chat',
            'project' => $project,
            'history' => $history,
            'layout'  => 'layouts/panel',
        ]);
    }

    /**
     * Belirli bir projeyi chat ile aç.
     */
    public function project(string $uuid): void
    {
        $user    = current_user();
        $project = $this->projects->findByUuid($uuid);

        if (!$project || (int)$project['user_id'] !== (int)$user['id']) {
            flash_error('Proje bulunamadı.');
            $this->redirect('panel');
        }

        if ($project['process_status'] !== 'draft') {
            flash_info('Bu projenin analizi tamamlanmış.');
            $this->redirect('panel/projeler/' . $uuid);
        }

        $history = $this->messages->getHistory((int)$project['id']);

        $this->view('customer/chat/index', [
            'title'   => 'GO! Chat — ' . e($project['name'] ?? 'Proje'),
            'project' => $project,
            'history' => $history,
            'layout'  => 'layouts/panel',
        ]);
    }

    /**
     * AJAX — kullanıcı mesajı gönder.
     */
    public function send(): void
    {
        $user = current_user();

        if (!$this->isAjax()) {
            $this->jsonError('Geçersiz istek.', 400);
        }

        $this->verifyCsrf();

        $userId    = (int)$user['id'];
        $projectId = (int)($_POST['project_id'] ?? 0);
        $message   = trim($_POST['message'] ?? '');

        if (empty($message) || $projectId < 1) {
            $this->jsonError('Mesaj ve proje ID gerekli.', 422);
        }

        // Rate limiting
        if (!$this->rateLimit->attemptChatMessage($userId)) {
            $this->jsonError('Çok hızlı mesaj gönderiyorsunuz. Lütfen bekleyin.', 429);
        }

        // Proje erişim kontrolü
        $project = $this->projects->findActiveByUser($userId, $projectId);
        if (!$project) {
            $this->jsonError('Proje bulunamadı.', 404);
        }

        if ($project['process_status'] !== 'draft') {
            $this->jsonError('Bu proje tamamlanmış, yeni mesaj gönderilemez.', 400);
        }

        // State machine ile işle
        try {
            $response = $this->machine->handle($projectId, $userId, $message);
        } catch (\Throwable $e) {
            $response = [
                'message'      => 'Üzgünüm, bir hata oluştu. Lütfen tekrar deneyin.',
                'state'        => 'error',
                'is_done'      => false,
                'quick_replies'=> [],
            ];
        }

        $this->jsonSuccess([
            'bot_message'  => $response['message'],
            'state'        => $response['state'],
            'is_done'      => $response['is_done'],
            'quick_replies'=> $response['quick_replies'],
        ], 'Mesaj gönderildi.');
    }

    /**
     * Chat geçmişini JSON olarak döndür.
     */
    public function history(string $uuid): void
    {
        $user    = current_user();
        $project = $this->projects->findByUuid($uuid);

        if (!$project || (int)$project['user_id'] !== (int)$user['id']) {
            $this->jsonError('Erişim reddedildi.', 403);
        }

        $history = $this->messages->getHistory((int)$project['id']);
        $this->jsonSuccess($history);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function findDraftProject(int $userId): ?array
    {
        try {
            $pdo  = \GO\Core\Database::getInstance();
            $stmt = $pdo->prepare(
                "SELECT * FROM projects WHERE user_id = ? AND process_status = 'draft' AND deleted_at IS NULL
                 ORDER BY updated_at DESC LIMIT 1"
            );
            $stmt->execute([$userId]);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\Throwable) {
            return null;
        }
    }
}
