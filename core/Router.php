<?php

namespace GO\Core;

/**
 * GO! Router
 * URL → Controller@method yönlendirme.
 * Composer gerektirmez. Named routes + parametreli URL desteği.
 */
class Router
{
    private array $routes = [];

    // ─── Route kayıt metodları ────────────────────────────────────────────────

    public function get(string $uri, string $handler, string $name = ''): void
    {
        $this->addRoute('GET', $uri, $handler, $name);
    }

    public function post(string $uri, string $handler, string $name = ''): void
    {
        $this->addRoute('POST', $uri, $handler, $name);
    }

    public function put(string $uri, string $handler, string $name = ''): void
    {
        $this->addRoute('PUT', $uri, $handler, $name);
    }

    public function patch(string $uri, string $handler, string $name = ''): void
    {
        $this->addRoute('PATCH', $uri, $handler, $name);
    }

    public function delete(string $uri, string $handler, string $name = ''): void
    {
        $this->addRoute('DELETE', $uri, $handler, $name);
    }

    // ─── Dispatch ─────────────────────────────────────────────────────────────

    public function dispatch(string $method, string $uri): void
    {
        // Method override (_method hidden field ile)
        if ($method === 'POST' && isset($_POST['_method'])) {
            $override = strtoupper($_POST['_method']);
            if (in_array($override, ['PUT', 'PATCH', 'DELETE'], true)) {
                $method = $override;
            }
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $params = $this->matchUri($route['uri'], $uri);
            if ($params === false) {
                continue;
            }

            $this->callHandler($route['handler'], $params);
            return;
        }

        // 404
        $this->handle404();
    }

    // ─── Handler çağrısı ──────────────────────────────────────────────────────

    private function callHandler(string $handler, array $params): void
    {
        // "ControllerClass@method" veya closure
        if (!str_contains($handler, '@')) {
            // Direkt callable (string)
            http_response_code(500);
            echo 'Geçersiz handler formatı: ' . htmlspecialchars($handler);
            return;
        }

        [$class, $method] = explode('@', $handler, 2);

        // Namespace ekle (GO\Controllers\)
        $fullClass = str_starts_with($class, 'GO\\') ? $class : 'GO\\Controllers\\' . $class;

        if (!class_exists($fullClass)) {
            http_response_code(500);
            if (\GO\Core\App::isDebug()) {
                echo "Controller bulunamadı: {$fullClass}";
            }
            return;
        }

        $controller = new $fullClass();

        if (!method_exists($controller, $method)) {
            http_response_code(500);
            if (\GO\Core\App::isDebug()) {
                echo "Method bulunamadı: {$fullClass}::{$method}";
            }
            return;
        }

        call_user_func_array([$controller, $method], $params);
    }

    // ─── URI eşleştirme ───────────────────────────────────────────────────────

    /**
     * /customer/projeler/{uuid} gibi parametreli URI'leri eşleştirir.
     * Eşleşirse parametre array'i, eşleşmezse false döner.
     */
    private function matchUri(string $routeUri, string $requestUri): array|false
    {
        // Tam eşleşme
        if ($routeUri === $requestUri) {
            return [];
        }

        // Parametreli route: {param}
        $pattern = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', '([^/]+)', $routeUri);
        $pattern = '#^' . $pattern . '$#';

        if (preg_match($pattern, $requestUri, $matches)) {
            array_shift($matches); // tam eşleşmeyi kaldır
            return array_values($matches);
        }

        return false;
    }

    // ─── 404 ──────────────────────────────────────────────────────────────────

    private function handle404(): void
    {
        http_response_code(404);
        $errorView = GO_ROOT . '/views/errors/404.php';
        if (file_exists($errorView)) {
            require $errorView;
        } else {
            echo '<h1>404 — Sayfa Bulunamadı</h1>';
        }
    }

    // ─── Yardımcılar ──────────────────────────────────────────────────────────

    private function addRoute(string $method, string $uri, string $handler, string $name): void
    {
        $this->routes[] = [
            'method'  => strtoupper($method),
            'uri'     => '/' . trim($uri, '/'),
            'handler' => $handler,
            'name'    => $name,
        ];
    }

    /**
     * Named route URL üretici (ileride kullanım için).
     */
    public function url(string $name, array $params = []): string
    {
        foreach ($this->routes as $route) {
            if ($route['name'] === $name) {
                $uri = $route['uri'];
                foreach ($params as $key => $value) {
                    $uri = str_replace('{' . $key . '}', (string)$value, $uri);
                }
                return APP_URL . $uri;
            }
        }
        return APP_URL . '/';
    }
}
