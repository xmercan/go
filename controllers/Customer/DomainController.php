<?php

namespace GO\Controllers\Customer;

use GO\Core\BaseController;
use GO\Models\DomainRequestModel;

class DomainController extends BaseController
{
    private DomainRequestModel $requests;

    public function __construct()
    {
        require_customer();
        $this->requests = new DomainRequestModel();
    }

    public function index(): void
    {
        $userId   = (int)current_user()['id'];
        $requests = $this->requests->findByUser($userId);

        // Domains listesi
        $domains = $this->getDomains($userId);

        $this->view('customer/domain/index', [
            'title'    => 'Domain Yönetimi',
            'layout'   => 'layouts/panel',
            'domains'  => $domains,
            'requests' => $requests,
        ]);
    }

    public function createRequest(): void
    {
        $this->verifyCsrf();
        $user   = current_user();
        $userId = (int)$user['id'];

        $type    = $this->input('request_type', '');
        $message = trim($this->input('message', ''));
        $domainId= (int)$this->input('domain_id', 0);

        $validTypes = ['ns_change','dns_add','dns_delete','transfer_code','internal_transfer','renewal','other'];
        if (!in_array($type, $validTypes, true)) {
            flash_error('Geçersiz talep türü.');
            $this->redirect('panel/domain');
        }

        $this->requests->create([
            'user_id'      => $userId,
            'domain_id'    => $domainId ?: null,
            'request_type' => $type,
            'payload'      => json_encode(['message' => $message]),
            'user_note'    => $message,
            'status'       => 'pending',
            'kanban_status'=> 'pending',
            'priority'     => 'normal',
        ]);

        // Kuyruğa ekle: admin bildirimi
        \GO\Services\QueueService::push('admin_notification', [
            'type'    => 'domain_request',
            'user_id' => $userId,
        ]);

        flash_success('Domain talebiniz alındı. En kısa sürede işleme alınacak.');
        $this->redirect('panel/domain');
    }

    private function getDomains(int $userId): array
    {
        try {
            $pdo  = \GO\Core\Database::getInstance();
            $stmt = $pdo->prepare("SELECT * FROM domains WHERE user_id = ? AND deleted_at IS NULL ORDER BY created_at DESC");
            $stmt->execute([$userId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable) {
            return [];
        }
    }
}
