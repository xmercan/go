<?php

namespace GO\Controllers\Customer;

use GO\Core\BaseController;
use GO\Models\ProjectModel;

class PanelController extends BaseController
{
    public function __construct()
    {
        require_customer();
    }

    public function dashboard(): void
    {
        $user     = current_user();
        $userId   = (int)$user['id'];
        $projects = new ProjectModel();

        $myProjects   = $projects->findByUser($userId);
        $totalProjects = count($myProjects);

        $active = array_filter($myProjects, fn($p) => in_array($p['process_status'], ['in_progress', 'reviewing', 'queued']));
        $done   = array_filter($myProjects, fn($p) => $p['process_status'] === 'completed');
        $recent = array_slice($myProjects, 0, 5);

        $hasDraft = count(array_filter($myProjects, fn($p) => $p['process_status'] === 'draft')) > 0;

        $this->view('customer/panel/dashboard', [
            'title'         => 'Genel Bakış',
            'layout'        => 'layouts/panel',
            'user'          => $user,
            'myProjects'    => $recent,
            'totalProjects' => $totalProjects,
            'activeCount'   => count($active),
            'doneCount'     => count($done),
            'hasDraft'      => $hasDraft,
        ]);
    }

    public function settings(): void
    {
        $user = current_user();
        $this->view('customer/panel/settings', [
            'title'  => 'Hesap Ayarları',
            'layout' => 'layouts/panel',
            'user'   => $user,
        ]);
    }

    public function updateSettings(): void
    {
        require_customer();
        $this->verifyCsrf();

        $user   = current_user();
        $userId = (int)$user['id'];
        $users  = new \GO\Models\UserModel();

        $fullName = trim($this->input('full_name', ''));
        $phone    = trim($this->input('phone', ''));

        $updates = [];
        if (!empty($fullName) && strlen($fullName) >= 3) $updates['full_name'] = $fullName;
        if (!empty($phone))   $updates['phone'] = $phone;

        $password    = $this->input('password', '');
        $confirm     = $this->input('password_confirmation', '');
        $oldPassword = $this->input('current_password', '');

        if (!empty($password)) {
            if (!password_verify($oldPassword, $user['password'])) {
                flash_error('Mevcut şifreniz yanlış.');
                $this->redirect('panel/ayarlar');
            }
            if (strlen($password) < 8) {
                flash_error('Yeni şifre en az 8 karakter olmalı.');
                $this->redirect('panel/ayarlar');
            }
            if ($password !== $confirm) {
                flash_error('Şifreler eşleşmiyor.');
                $this->redirect('panel/ayarlar');
            }
            $updates['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        if (!empty($updates)) {
            $users->update($userId, $updates);
            flash_success('Ayarlarınız güncellendi.');
        }

        $this->redirect('panel/ayarlar');
    }
}
