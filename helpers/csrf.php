<?php

/**
 * CSRF token üretme ve doğrulama.
 * Her POST formunda zorunludur.
 */

if (!function_exists('csrf_token')) {
    /**
     * Mevcut CSRF token'ı al veya yeni üret.
     */
    function csrf_token(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Gizli CSRF input alanı HTML olarak döner.
     * Kullanım: <?= csrf_field() ?>
     */
    function csrf_field(): string
    {
        return '<input type="hidden" name="_csrf_token" value="' . e(csrf_token()) . '">';
    }
}

if (!function_exists('csrf_meta')) {
    /**
     * AJAX fetch istekleri için meta tag.
     * Kullanım: <meta name="csrf-token" content="<?= csrf_meta() ?>">
     */
    function csrf_meta(): string
    {
        return csrf_token();
    }
}

if (!function_exists('csrf_verify')) {
    /**
     * POST isteğindeki token'ı doğrula.
     * Geçersizse false döner.
     */
    function csrf_verify(): bool
    {
        // AJAX header kontrolü
        $headerToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        $postToken   = $_POST['_csrf_token'] ?? '';
        $sessionToken = $_SESSION['csrf_token'] ?? '';

        if ($sessionToken === '') {
            return false;
        }

        $submittedToken = $headerToken !== '' ? $headerToken : $postToken;

        return hash_equals($sessionToken, $submittedToken);
    }
}

if (!function_exists('csrf_regenerate')) {
    /**
     * Token'ı yenile (giriş sonrası).
     */
    function csrf_regenerate(): string
    {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        return $_SESSION['csrf_token'];
    }
}
