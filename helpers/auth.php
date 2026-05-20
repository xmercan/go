<?php

/**
 * Kimlik doğrulama ve yetki helper'ları.
 * Oturum kontrolü, requireCustomer, requireAdmin.
 */

// ─── Müşteri oturum fonksiyonları ────────────────────────────────────────────

if (!function_exists('auth_customer')) {
    /**
     * Oturumdaki müşteri verisini döner veya null.
     */
    function auth_customer(): ?array
    {
        return $_SESSION['customer'] ?? null;
    }
}

if (!function_exists('auth_customer_id')) {
    function auth_customer_id(): ?int
    {
        return isset($_SESSION['customer']['id']) ? (int)$_SESSION['customer']['id'] : null;
    }
}

if (!function_exists('is_customer_logged_in')) {
    function is_customer_logged_in(): bool
    {
        return !empty($_SESSION['customer']['id']);
    }
}

// Layout'larda kullanım kolaylığı için alias
if (!function_exists('is_logged_in')) {
    function is_logged_in(): bool
    {
        return is_customer_logged_in();
    }
}

if (!function_exists('require_customer')) {
    /**
     * Müşteri girişi zorunlu. Giriş yoksa login'e yönlendir.
     */
    function require_customer(): void
    {
        if (!is_customer_logged_in()) {
            flash_warning('Bu sayfaya erişmek için giriş yapmanız gerekiyor.');
            header('Location: ' . APP_URL . '/giris?redirect=' . urlencode($_SERVER['REQUEST_URI'] ?? ''));
            exit;
        }
    }
}

if (!function_exists('login_customer')) {
    /**
     * Müşteri oturumunu başlat.
     */
    function login_customer(array $user): void
    {
        session_regenerate_id(true);
        csrf_regenerate();
        $_SESSION['customer'] = [
            'id'        => $user['id'],
            'uuid'      => $user['uuid'] ?? null,
            'full_name' => $user['full_name'],
            'email'     => $user['email'],
            'role'      => 'customer',
        ];
        $_SESSION['customer_login_at'] = time();
    }
}

if (!function_exists('logout_customer')) {
    /**
     * Müşteri oturumunu sonlandır.
     */
    function logout_customer(): void
    {
        unset($_SESSION['customer'], $_SESSION['customer_login_at']);
        session_regenerate_id(true);
    }
}

// ─── Admin oturum fonksiyonları ───────────────────────────────────────────────

if (!function_exists('auth_admin')) {
    function auth_admin(): ?array
    {
        return $_SESSION['admin'] ?? null;
    }
}

if (!function_exists('auth_admin_id')) {
    function auth_admin_id(): ?int
    {
        return isset($_SESSION['admin']['id']) ? (int)$_SESSION['admin']['id'] : null;
    }
}

if (!function_exists('is_admin_logged_in')) {
    function is_admin_logged_in(): bool
    {
        return !empty($_SESSION['admin']['id']);
    }
}

if (!function_exists('require_admin')) {
    /**
     * Admin girişi zorunlu. Giriş yoksa admin login'e yönlendir.
     */
    function require_admin(): void
    {
        if (!is_admin_logged_in()) {
            flash_warning('Bu alana erişmek için yönetici girişi gerekiyor.');
            header('Location: ' . APP_URL . '/admin/giris');
            exit;
        }
    }
}

if (!function_exists('require_super_admin')) {
    /**
     * Süper admin rolü zorunlu.
     */
    function require_super_admin(): void
    {
        require_admin();
        if (($_SESSION['admin']['role'] ?? '') !== 'super_admin') {
            flash_error('Bu işlem için yetersiz yetki.');
            header('Location: ' . APP_URL . '/admin');
            exit;
        }
    }
}

if (!function_exists('login_admin')) {
    function login_admin(array $admin): void
    {
        session_regenerate_id(true);
        csrf_regenerate();
        $_SESSION['admin'] = [
            'id'        => $admin['id'],
            'full_name' => $admin['full_name'],
            'email'     => $admin['email'],
            'role'      => $admin['role'],
        ];
        $_SESSION['admin_login_at'] = time();
    }
}

if (!function_exists('logout_admin')) {
    function logout_admin(): void
    {
        unset($_SESSION['admin'], $_SESSION['admin_login_at']);
        session_regenerate_id(true);
    }
}

// ─── Genel yardımcılar ────────────────────────────────────────────────────────

if (!function_exists('is_logged_in')) {
    /**
     * Herhangi biri giriş yapmış mı?
     */
    function is_logged_in(): bool
    {
        return is_customer_logged_in() || is_admin_logged_in();
    }
}

if (!function_exists('guest_only')) {
    /**
     * Sadece giriş yapmamış kullanıcılar. Giriş yapmışsa dashboard'a yönlendir.
     */
    function guest_only(): void
    {
        if (is_customer_logged_in()) {
            header('Location: ' . APP_URL . '/panel');
            exit;
        }
        if (is_admin_logged_in()) {
            header('Location: ' . APP_URL . '/admin/dashboard');
            exit;
        }
    }
}

if (!function_exists('admin_guest_only')) {
    function admin_guest_only(): void
    {
        if (is_admin_logged_in()) {
            header('Location: ' . APP_URL . '/admin/dashboard');
            exit;
        }
    }
}

if (!function_exists('current_user')) {
    function current_user(): array
    {
        return $_SESSION['customer'] ?? [];
    }
}

if (!function_exists('current_admin')) {
    function current_admin(): array
    {
        return $_SESSION['admin'] ?? [];
    }
}

if (!function_exists('store_old_input')) {
    function store_old_input(array $data): void
    {
        $_SESSION['_old_input'] = $data;
    }
}

if (!function_exists('old')) {
    function old(string $key, string $default = ''): string
    {
        $val = $_SESSION['_old_input'][$key] ?? $default;
        unset($_SESSION['_old_input'][$key]);
        return (string)$val;
    }
}
