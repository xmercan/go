<?php

namespace GO\Core;

/**
 * Tüm controller'ların temel sınıfı.
 * View render, JSON response, redirect, flash.
 */
abstract class BaseController
{
    // ─── View ─────────────────────────────────────────────────────────────────

    /**
     * View dosyasını render et.
     *
     * @param string $view  'customer/projects/index' → views/customer/projects/index.php
     * @param array  $data  View'a aktarılacak değişkenler
     */
    protected function view(string $view, array $data = []): void
    {
        $file = GO_ROOT . '/views/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($file)) {
            if (App::isDebug()) {
                echo "View bulunamadı: {$file}";
            }
            http_response_code(500);
            return;
        }

        // View içeriğini buffer ile al — $layout değişkeni set edilebilir
        extract($data, EXTR_SKIP);
        ob_start();
        require $file;
        $content = ob_get_clean();

        // View dosyası $layout tanımladıysa o layout'a sar
        if (!empty($layout)) {
            $layoutFile = GO_ROOT . '/views/' . $layout . '.php';
            if (file_exists($layoutFile)) {
                extract($data, EXTR_SKIP);
                require $layoutFile;
                return;
            }
        }

        echo $content;
    }

    /**
     * Layout ile view render et (explicit).
     */
    protected function layout(string $layout, string $view, array $data = []): void
    {
        $data['layout'] = $layout;
        $this->view($view, $data);
    }

    // ─── JSON ─────────────────────────────────────────────────────────────────

    /**
     * JSON başarı yanıtı.
     */
    protected function jsonSuccess(mixed $data = null, string $message = 'Başarılı', int $code = 200): void
    {
        $this->sendJson([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    /**
     * JSON hata yanıtı.
     */
    protected function jsonError(string $message = 'Bir hata oluştu.', int $code = 400, mixed $errors = null): void
    {
        $this->sendJson([
            'success' => false,
            'message' => $message,
            'errors'  => $errors,
        ], $code);
    }

    /**
     * Raw JSON gönder.
     */
    protected function sendJson(array $payload, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    // ─── Redirect ─────────────────────────────────────────────────────────────

    /**
     * Yönlendirme.
     */
    protected function redirect(string $path, int $code = 302): void
    {
        $url = str_starts_with($path, 'http') ? $path : APP_URL . '/' . ltrim($path, '/');
        header('Location: ' . $url, true, $code);
        exit;
    }

    /**
     * Önceki sayfaya geri dön.
     */
    protected function back(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? (APP_URL . '/');
        header('Location: ' . $referer, true, 302);
        exit;
    }

    // ─── Flash mesajları ──────────────────────────────────────────────────────

    /**
     * Flash mesajı ayarla (sonraki yönlendirmede gösterilir).
     */
    protected function flash(string $type, string $message): void
    {
        flash_set($type, $message);
    }

    // ─── Güvenlik ─────────────────────────────────────────────────────────────

    /**
     * CSRF token doğrula (POST isteklerinde zorunlu).
     */
    protected function verifyCsrf(): void
    {
        if (!csrf_verify()) {
            if ($this->isAjax()) {
                $this->jsonError('Güvenlik doğrulaması başarısız. Lütfen sayfayı yenileyin.', 419);
            }
            $this->flash('error', 'Güvenlik doğrulaması başarısız. Lütfen tekrar deneyin.');
            $this->back();
        }
    }

    /**
     * AJAX isteği mi?
     */
    protected function isAjax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * İstek metodu.
     */
    protected function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    /**
     * POST verisi al (güvenli).
     */
    protected function input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    /**
     * Tüm POST verisi al.
     */
    protected function inputs(): array
    {
        return $_POST;
    }
}
