<?php

/**
 * GO! — Front Controller
 * Tüm HTTP istekleri bu dosyadan geçer.
 */

declare(strict_types=1);

define('GO_ROOT', __DIR__);
define('GO_START', microtime(true));

// Bootstrap
require_once GO_ROOT . '/core/App.php';

// Uygulamayı başlat ve isteği işle
$app = \GO\Core\App::getInstance();
$app->boot();
$app->handle();
