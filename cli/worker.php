<?php

/**
 * GO! Queue Worker
 *
 * cPanel cron ayarı:
 *   * /5 * * * * php /home/KULLANICI/public_html/cli/worker.php >> /dev/null 2>&1
 *
 * Kullanım:
 *   php cli/worker.php
 *   php cli/worker.php --queue=email
 *   php cli/worker.php --queue=export --limit=5
 *
 * Sadece CLI'dan çalışır; web erişimi .htaccess ile engellenmiştir.
 */

declare(strict_types=1);

// Güvenlik: web'den çağrıldıysa engelle
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('CLI only.');
}

define('GO_ROOT', dirname(__DIR__));
define('GO_START', microtime(true));

// Bootstrap
require_once GO_ROOT . '/core/Env.php';
\GO\Core\Env::load(GO_ROOT . '/.env');
require_once GO_ROOT . '/config.php';

// Job sınıfı haritası: queue adı → handler sınıfı
$jobMap = [
    'email'  => \GO\Jobs\SendEmailJob::class,
    'export' => \GO\Jobs\ProcessExportJob::class,
    // 'backup' => \GO\Jobs\BackupJob::class, // V2
];

// CLI argümanları parse
$options = getopt('', ['queue:', 'limit:']);
$targetQueue = $options['queue'] ?? null;
$limit       = (int)($options['limit'] ?? 10);

// Stale job'ları serbest bırak (30 dk önceki processing)
$queueService = new \GO\Services\QueueService();
$queueService->releaseStale(30);

$processed = 0;
$queues    = $targetQueue ? [$targetQueue] : array_keys($jobMap);

foreach ($queues as $queue) {
    if (!isset($jobMap[$queue])) {
        echo "[WARN] Bilinmeyen queue: {$queue}\n";
        continue;
    }

    while ($processed < $limit) {
        $job = $queueService->pop($queue);

        if ($job === null) {
            break; // Bu queue boş
        }

        $handlerClass = $jobMap[$queue];
        $handler      = new $handlerClass();

        echo '[' . date('H:i:s') . "] İşleniyor: queue={$queue} id={$job['id']}\n";

        try {
            $handler->handle($job['payload']);
            $queueService->complete($job['id']);
            echo '[' . date('H:i:s') . "] Tamamlandı: id={$job['id']}\n";
        } catch (\Throwable $e) {
            $queueService->fail($job['id'], $e->getMessage());
            echo '[' . date('H:i:s') . "] HATA: id={$job['id']} error={$e->getMessage()}\n";
        }

        $processed++;
    }
}

$elapsed = round((microtime(true) - GO_START) * 1000, 2);
echo "[Worker] {$processed} job işlendi. Süre: {$elapsed}ms\n";
