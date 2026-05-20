<?php

/**
 * Session flash mesajları.
 * Yönlendirmeler arası tek kullanımlık mesajlar.
 */

if (!function_exists('flash_set')) {
    /**
     * Flash mesajı ayarla.
     *
     * @param string $type    success | error | warning | info
     * @param string $message Gösterilecek mesaj
     */
    function flash_set(string $type, string $message): void
    {
        $_SESSION['flash'][] = [
            'type'    => $type,
            'message' => $message,
        ];
    }
}

if (!function_exists('flash_get')) {
    /**
     * Tüm flash mesajlarını al ve temizle.
     */
    function flash_get(): array
    {
        $messages = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $messages;
    }
}

if (!function_exists('flash_has')) {
    /**
     * Flash mesajı var mı?
     */
    function flash_has(): bool
    {
        return !empty($_SESSION['flash']);
    }
}

if (!function_exists('flash_render')) {
    /**
     * Flash mesajlarını HTML olarak render et.
     * Toast sistemi JS üzerinden çalışacak; bu fallback statik HTML.
     */
    function flash_render(): string
    {
        $messages = flash_get();
        if (empty($messages)) {
            return '';
        }

        $html = '<div id="flash-messages" data-messages="' . e_attr(json_encode($messages)) . '" hidden></div>';
        return $html;
    }
}

if (!function_exists('flash_success')) {
    function flash_success(string $message): void
    {
        flash_set('success', $message);
    }
}

if (!function_exists('flash_error')) {
    function flash_error(string $message): void
    {
        flash_set('error', $message);
    }
}

if (!function_exists('flash_info')) {
    function flash_info(string $message): void
    {
        flash_set('info', $message);
    }
}

if (!function_exists('flash_warning')) {
    function flash_warning(string $message): void
    {
        flash_set('warning', $message);
    }
}

if (!function_exists('get_flashes')) {
    /**
     * Layout'larda kullanmak için get_flashes() kısayolu.
     */
    function get_flashes(): array
    {
        return flash_get();
    }
}
