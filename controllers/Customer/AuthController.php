<?php

namespace GO\Controllers\Customer;

use GO\Core\BaseController;
use GO\Models\UserModel;
use GO\Models\PasswordResetModel;
use GO\Models\LoginLogModel;
use GO\Services\RateLimitService;
use GO\Services\MailService;

class AuthController extends BaseController
{
    private UserModel         $users;
    private PasswordResetModel $resets;
    private LoginLogModel     $loginLogs;
    private RateLimitService  $rateLimit;

    public function __construct()
    {
        $this->users     = new UserModel();
        $this->resets    = new PasswordResetModel();
        $this->loginLogs = new LoginLogModel();
        $this->rateLimit = new RateLimitService();
    }

    // ─── Giriş ────────────────────────────────────────────────────────────────

    public function showLogin(): void
    {
        guest_only();
        $this->view('customer/auth/login', [
            'title'    => 'Müşteri Girişi',
            'redirect' => $_GET['redirect'] ?? '',
        ]);
    }

    public function login(): void
    {
        guest_only();
        $this->verifyCsrf();

        $email    = trim($this->input('email', ''));
        $password = $this->input('password', '');
        $redirect = $this->input('redirect', '/panel');

        // Rate limit
        if (!$this->rateLimit->attemptLogin($email)) {
            flash_error('Çok fazla başarısız deneme. Lütfen 5 dakika bekleyin.');
            $this->redirect('giris');
        }

        $user = $this->users->findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            $this->loginLogs->record('user', $user['id'] ?? null, $email, 'failed');
            flash_error('E-posta veya şifre hatalı.');
            store_old_input(['email' => $email]);
            $this->redirect('giris');
        }

        if ($user['status'] !== 'active') {
            flash_error('Hesabınız askıya alınmış. Destek için iletişime geçin.');
            $this->redirect('giris');
        }

        // Başarılı giriş
        $this->rateLimit->clearLogin($email);
        $this->users->updateLastLogin((int)$user['id']);
        $this->loginLogs->record('user', (int)$user['id'], $email, 'success');
        login_customer($user);

        $safeRedirect = str_starts_with($redirect, '/') ? $redirect : '/panel';
        $this->redirect(ltrim($safeRedirect, '/'));
    }

    public function logout(): void
    {
        logout_customer();
        flash_info('Çıkış yaptınız. Görüşmek üzere!');
        $this->redirect('giris');
    }

    // ─── Kayıt ────────────────────────────────────────────────────────────────

    public function showRegister(): void
    {
        guest_only();
        $this->view('customer/auth/register', ['title' => 'Kayıt Ol']);
    }

    public function register(): void
    {
        guest_only();
        $this->verifyCsrf();

        $data = [
            'full_name'             => trim($this->input('full_name', '')),
            'email'                 => trim($this->input('email', '')),
            'phone'                 => trim($this->input('phone', '')),
            'password'              => $this->input('password', ''),
            'password_confirmation' => $this->input('password_confirmation', ''),
        ];

        $result = validate($data, [
            'full_name' => 'required|min:3|max:150',
            'email'     => 'required|email|max:191',
            'phone'     => 'required|phone',
            'password'  => 'required|min:8|confirmed',
        ]);

        if (!$result['valid']) {
            store_old_input($data);
            flash_error(implode(' ', array_column($result['errors'], 0)));
            $this->redirect('kayit');
        }

        if ($this->users->emailExists($data['email'])) {
            store_old_input($data);
            flash_error('Bu e-posta adresi zaten kayıtlı.');
            $this->redirect('kayit');
        }

        $uuid = $this->users->find(0) ? '' : ''; // generateUuid çağrısı model içinde
        $id = $this->users->create([
            'full_name' => $data['full_name'],
            'email'     => $data['email'],
            'phone'     => $data['phone'],
            'password'  => password_hash($data['password'], PASSWORD_DEFAULT),
            'status'    => 'active',
        ]);

        $user = $this->users->find($id);
        login_customer($user);

        // Mail kuyruğa (stub — Aşama 9'da aktif)
        (new MailService())->queue($data['email'], 'user_welcome', [
            'name' => $data['full_name'],
        ]);

        flash_success('Hoş geldiniz, ' . e($data['full_name']) . '! Hesabınız oluşturuldu.');
        $this->redirect('panel');
    }

    // ─── Şifre Sıfırlama ──────────────────────────────────────────────────────

    public function showForgotPassword(): void
    {
        guest_only();
        $this->view('customer/auth/forgot-password', ['title' => 'Şifremi Unuttum']);
    }

    public function forgotPassword(): void
    {
        guest_only();
        $this->verifyCsrf();

        $email = trim($this->input('email', ''));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash_error('Geçerli bir e-posta girin.');
            $this->redirect('sifremi-unuttum');
        }

        if (!$this->rateLimit->attemptForgotPassword($email)) {
            flash_error('Çok fazla istek. Lütfen 15 dakika bekleyin.');
            $this->redirect('sifremi-unuttum');
        }

        $user = $this->users->findByEmail($email);

        if ($user) {
            $token = $this->resets->create($email);
            (new MailService())->queue($email, 'user_password_reset', [
                'name'  => $user['full_name'],
                'link'  => APP_URL . '/sifre-sifirla/' . $token,
            ]);
        }

        // Güvenlik: kullanıcı olup olmadığını söyleme
        flash_success('E-posta adresiniz kayıtlıysa şifre sıfırlama bağlantısı gönderildi.');
        $this->redirect('sifremi-unuttum');
    }

    public function showResetPassword(string $token): void
    {
        $reset = $this->resets->findValid($token);
        if (!$reset) {
            flash_error('Bu bağlantı geçersiz veya süresi dolmuş.');
            $this->redirect('sifremi-unuttum');
        }

        $this->view('customer/auth/reset-password', [
            'title' => 'Yeni Şifre Belirle',
            'token' => $token,
        ]);
    }

    public function resetPassword(string $token): void
    {
        $this->verifyCsrf();

        $reset = $this->resets->findValid($token);
        if (!$reset) {
            flash_error('Bağlantı geçersiz veya süresi dolmuş.');
            $this->redirect('sifremi-unuttum');
        }

        $password = $this->input('password', '');
        $confirm  = $this->input('password_confirmation', '');

        if (strlen($password) < 8) {
            flash_error('Şifre en az 8 karakter olmalı.');
            $this->redirect('sifre-sifirla/' . $token);
        }
        if ($password !== $confirm) {
            flash_error('Şifreler eşleşmiyor.');
            $this->redirect('sifre-sifirla/' . $token);
        }

        $user = $this->users->findByEmail($reset['email']);
        if ($user) {
            $this->users->update((int)$user['id'], [
                'password' => password_hash($password, PASSWORD_DEFAULT),
            ]);
            $this->resets->markUsed($token);
            flash_success('Şifreniz başarıyla güncellendi. Giriş yapabilirsiniz.');
        }

        $this->redirect('giris');
    }
}
