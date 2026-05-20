<?php

namespace GO\Controllers\Admin;

use GO\Core\BaseController;
use GO\Models\AdminModel;
use GO\Models\LoginLogModel;
use GO\Services\RateLimitService;

class AuthController extends BaseController
{
    private AdminModel       $admins;
    private LoginLogModel    $loginLogs;
    private RateLimitService $rateLimit;

    public function __construct()
    {
        $this->admins    = new AdminModel();
        $this->loginLogs = new LoginLogModel();
        $this->rateLimit = new RateLimitService();
    }

    public function showLogin(): void
    {
        admin_guest_only();
        $this->view('admin/auth/login', ['title' => 'Admin Girişi']);
    }

    public function login(): void
    {
        admin_guest_only();
        $this->verifyCsrf();

        $email    = trim($this->input('email', ''));
        $password = $this->input('password', '');

        if (!$this->rateLimit->attemptAdminLogin($email)) {
            flash_error('Çok fazla başarısız deneme. Lütfen 10 dakika bekleyin.');
            $this->redirect('admin/giris');
        }

        $admin = $this->admins->findByEmail($email);

        if (!$admin || !password_verify($password, $admin['password'])) {
            $this->loginLogs->record('admin', $admin['id'] ?? null, $email, 'failed');
            flash_error('E-posta veya şifre hatalı.');
            $this->redirect('admin/giris');
        }

        if ($admin['status'] !== 'active') {
            flash_error('Hesabınız askıya alınmış.');
            $this->redirect('admin/giris');
        }

        $this->rateLimit->clearAdminLogin($email);
        $this->admins->updateLastLogin((int)$admin['id']);
        $this->loginLogs->record('admin', (int)$admin['id'], $email, 'success');
        login_admin($admin);

        $this->redirect('admin/dashboard');
    }

    public function logout(): void
    {
        logout_admin();
        flash_info('Çıkış yaptınız.');
        $this->redirect('admin/giris');
    }
}
