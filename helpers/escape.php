<?php

/**
 * XSS koruma helper'ları.
 * e() fonksiyonu tüm çıktılarda kullanılmalıdır.
 */

if (!function_exists('e')) {
    /**
     * HTML özel karakterleri escape et (XSS önleme).
     * Kullanım: echo e($userInput);
     */
    function e(mixed $value): string
    {
        if ($value === null || $value === false) {
            return '';
        }
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('e_attr')) {
    /**
     * HTML attribute için escape.
     * Kullanım: <input value="<?= e_attr($val) ?>">
     */
    function e_attr(mixed $value): string
    {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('e_js')) {
    /**
     * JavaScript string için escape.
     * Kullanım: var x = "<?= e_js($val) ?>";
     */
    function e_js(mixed $value): string
    {
        return addslashes(strip_tags((string)($value ?? '')));
    }
}

if (!function_exists('e_url')) {
    /**
     * URL parametresi için encode.
     */
    function e_url(mixed $value): string
    {
        return urlencode((string)($value ?? ''));
    }
}

if (!function_exists('strip_xss')) {
    /**
     * Temel XSS etiket temizleme.
     * Form inputları için — rich text değil.
     */
    function strip_xss(string $value): string
    {
        return strip_tags(trim($value));
    }
}

if (!function_exists('safe_json')) {
    /**
     * Güvenli JSON encode.
     */
    function safe_json(mixed $data): string
    {
        return json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
    }
}
