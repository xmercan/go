<?php

namespace GO\Jobs;

/**
 * Proje ZIP export job'u.
 * Aşama 6'da ProjectExportService implement edilince burada çağrılacak.
 */
class ProcessExportJob extends BaseJob
{
    public function handle(array $payload): void
    {
        /*
         * Beklenen payload:
         * [
         *   'project_id'  => 42,
         *   'user_id'     => 7,
         *   'version'     => 'v1',
         *   'export_uuid' => 'xxx-yyy-zzz',
         * ]
         *
         * Aşama 6 implementasyonu:
         * $service = new \GO\Services\ProjectExportService();
         * $service->buildZip($payload['project_id'], $payload['export_uuid']);
         */

        $logPath = defined('LOG_PATH') ? LOG_PATH : GO_ROOT . '/storage/logs';
        $msg = sprintf(
            '[%s] ProcessExportJob project_id=%s uuid=%s' . PHP_EOL,
            date('Y-m-d H:i:s'),
            $payload['project_id'] ?? 'unknown',
            $payload['export_uuid'] ?? 'unknown'
        );
        @file_put_contents($logPath . '/queue-export.log', $msg, FILE_APPEND | LOCK_EX);
    }

    public function queueName(): string
    {
        return 'export';
    }
}
