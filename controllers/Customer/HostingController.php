<?php

namespace GO\Controllers\Customer;

use GO\Core\BaseController;
use GO\Models\HostingRequestModel;

class HostingController extends BaseController
{
    private HostingRequestModel $requests;

    public function __construct()
    {
        require_customer();
        $this->requests = new HostingRequestModel();
    }

    public function index(): void
    {
        $userId   = (int)current_user()['id'];
        $requests = $this->requests->findByUser($userId);
        $services = $this->getServices($userId);

        $this->view('customer/hosting/index', [
            'title'    => 'Hosting Yönetimi',
            'layout'   => 'layouts/panel',
            'services' => $services,
            'requests' => $requests,
        ]);
    }

    public function createRequest(): void
    {
        $this->verifyCsrf();
        $user   = current_user();
        $userId = (int)$user['id'];

        $type      = $this->input('request_type', 'other');
        $message   = trim($this->input('message', ''));
        $serviceId = (int)$this->input('hosting_service_id', 0);

        $validTypes = ['ftp_info','cpanel_info','backup','upgrade','other'];
        if (!in_array($type, $validTypes, true)) {
            flash_error('Geçersiz talep türü.');
            $this->redirect('panel/hosting');
        }

        $this->requests->create([
            'hosting_service_id' => $serviceId ?: null,
            'user_id'            => $userId,
            'request_type'       => $type,
            'message'            => $message,
            'status'             => 'pending',
            'kanban_status'      => 'pending',
            'priority'           => 'normal',
        ]);

        \GO\Services\QueueService::push('admin_notification', [
            'type'    => 'hosting_request',
            'user_id' => $userId,
        ]);

        flash_success('Hosting talebiniz alındı.');
        $this->redirect('panel/hosting');
    }

    private function getServices(int $userId): array
    {
        try {
            $pdo  = \GO\Core\Database::getInstance();
            $stmt = $pdo->prepare("SELECT * FROM hosting_services WHERE user_id = ? AND deleted_at IS NULL ORDER BY created_at DESC");
            $stmt->execute([$userId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable) {
            return [];
        }
    }
}
