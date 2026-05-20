<?php

namespace GO\Core;

/**
 * GO! Uygulama çekirdeği.
 * Bootstrap, autoload, mode yönetimi, request dispatching.
 */
class App
{
    private static ?self $instance = null;
    private Router $router;

    private function __construct() {}

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Uygulamayı başlat.
     */
    public function boot(): void
    {
        // 1. Autoloader
        $this->registerAutoloader();

        // 2. Config (Env zaten index.php'de yüklendi, config.php çekilir)
        if (!defined('APP_NAME') && file_exists(GO_ROOT . '/config.php')) {
            require_once GO_ROOT . '/config.php';
        }

        // 3. Timezone
        date_default_timezone_set(defined('APP_TIMEZONE') ? APP_TIMEZONE : 'Europe/Istanbul');

        // 4. Hata yönetimi
        $this->configureErrorHandling();

        // 5. Session
        $this->startSession();

        // 6. Helper'ları yükle
        $this->loadHelpers();

        // 7. Router — $router değişkeni web.php scope'una aktarılır
        $this->router = new Router();
        $router = $this->router;
        require_once GO_ROOT . '/routes/web.php';
    }

    /**
     * İsteği route'a yönlendir.
     */
    public function handle(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri    = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $uri    = '/' . trim($uri, '/');

        $this->router->dispatch($method, $uri);
    }

    /**
     * PSR-4 benzeri autoloader (namespace GO\* → /core|controllers|models|services|...).
     */
    private function registerAutoloader(): void
    {
        spl_autoload_register(function (string $class): void {
            // Namespace GO\ → proje kökü
            if (!str_starts_with($class, 'GO\\')) {
                return;
            }

            // GO\Core\App → core/App.php
            // GO\Controllers\Customer\ProjectController → controllers/Customer/ProjectController.php
            $relative = str_replace('GO\\', '', $class);
            $relative = str_replace('\\', '/', $relative);
            $file     = GO_ROOT . '/' . strtolower(substr($relative, 0, strpos($relative, '/')))
                      . substr($relative, strpos($relative, '/')) . '.php';

            // Küçük harf klasör adıyla eşleştir (core → Core, controllers → Controllers)
            // Daha sağlam yol: katman adını küçük harfe çevir
            $parts   = explode('/', $relative);
            $parts[0] = strtolower($parts[0]);
            $file     = GO_ROOT . '/' . implode('/', $parts) . '.php';

            if (file_exists($file)) {
                require_once $file;
            }
        });
    }

    /**
     * Hata yönetimi — APP_MODE'a göre.
     */
    private function configureErrorHandling(): void
    {
        $debug = defined('APP_DEBUG') && APP_DEBUG;
        $mode  = defined('APP_MODE') ? APP_MODE : 'production';

        if ($debug || $mode === 'local') {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
        } else {
            error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
            ini_set('display_errors', '0');
        }

        ini_set('log_errors', '1');
        ini_set('error_log', GO_ROOT . '/storage/logs/php-errors.log');

        set_exception_handler(function (\Throwable $e) use ($debug): void {
            if ($debug) {
                echo '<pre style="background:#1a1a2e;color:#ff6b6b;padding:20px;">';
                echo htmlspecialchars($e->getMessage()) . "\n\n";
                echo htmlspecialchars($e->getTraceAsString());
                echo '</pre>';
            } else {
                // Üretimde genel hata sayfası
                http_response_code(500);
                if (file_exists(GO_ROOT . '/views/errors/500.php')) {
                    require GO_ROOT . '/views/errors/500.php';
                } else {
                    echo '<h1>Bir hata oluştu.</h1><p>Lütfen daha sonra tekrar deneyin.</p>';
                }
            }
            exit;
        });
    }

    /**
     * Session başlat.
     */
    private function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_name('GO_SESSION');

            $secure   = defined('APP_MODE') && APP_MODE === 'production';
            $lifetime = defined('SESSION_LIFETIME') ? SESSION_LIFETIME * 60 : 7200;

            session_set_cookie_params([
                'lifetime' => $lifetime,
                'path'     => '/',
                'domain'   => '',
                'secure'   => $secure,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);

            session_start();
        }
    }

    /**
     * Helper dosyalarını yükle.
     */
    private function loadHelpers(): void
    {
        $helpers = [
            'escape',
            'csrf',
            'flash',
            'validation',
            'auth',
        ];

        foreach ($helpers as $helper) {
            $file = GO_ROOT . '/helpers/' . $helper . '.php';
            if (file_exists($file)) {
                require_once $file;
            }
        }
    }

    // ─── Statik yardımcı metodlar ─────────────────────────────────────────────

    public static function isProduction(): bool
    {
        return defined('APP_MODE') && APP_MODE === 'production';
    }

    public static function isLocal(): bool
    {
        return defined('APP_MODE') && APP_MODE === 'local';
    }

    public static function isDebug(): bool
    {
        return defined('APP_DEBUG') && APP_DEBUG;
    }

    public static function version(): string
    {
        return '1.0.0';
    }
}
