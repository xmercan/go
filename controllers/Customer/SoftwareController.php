<?php

namespace GO\Controllers\Customer;

use GO\Core\BaseController;
use GO\Models\SoftwareRequestModel;

class SoftwareController extends BaseController
{
    private SoftwareRequestModel $requests;

    public function __construct()
    {
        require_customer();
        $this->requests = new SoftwareRequestModel();
    }

    public function index(): void
    {
        $userId   = (int)current_user()['id'];
        $requests = $this->requests->findByUser($userId);
        $services = $this->getServices($userId);

        $this->view('customer/software/index', [
            'title'    => 'Yazılım Yönetimi',
            'layout'   => 'layouts/panel',
            'services' => $services,
            'requests' => $requests,
        ]);
    }

    public function createRequest(): void
    {
        $this->verifyCsrf();
        $user      = current_user();
        $userId    = (int)$user['id'];
        $type      = $this->input('request_type', 'other');
        $message   = trim($this->input('message', ''));
        $serviceId = (int)$this->input('software_service_id', 0);

        $validTypes = ['install','update','support','other'];
        if (!in_array($type, $validTypes, true)) {
            flash_error('Geçersiz talep türü.');
            $this->redirect('panel/yazilim');
        }

        $this->requests->create([
            'software_service_id' => $serviceId ?: null,
            'user_id'             => $userId,
            'request_type'        => $type,
            'message'             => $message,
            'status'              => 'pending',
            'kanban_status'       => 'pending',
            'priority'            => 'normal',
        ]);

        \GO\Services\QueueService::push('admin_notification', [
            'type'    => 'software_request',
            'user_id' => $userId,
        ]);

        flash_success('Yazılım talebiniz alındı.');
        $this->redirect('panel/yazilim');
    }

    private function getServices(int $userId): array
    {
        try {
            $pdo  = \GO\Core\Database::getInstance();
            $stmt = $pdo->prepare("SELECT * FROM software_services WHERE user_id = ? AND deleted_at IS NULL ORDER BY created_at DESC");
            $stmt->execute([$userId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable) {
            return [];
        }
    }
}
