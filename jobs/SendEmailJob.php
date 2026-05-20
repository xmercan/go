<?php

namespace GO\Jobs;

/**
 * E-posta gönderme job'u.
 * Aşama 9'da MailService implement edilince burada çağrılacak.
 */
class SendEmailJob extends BaseJob
{
    public function handle(array $payload): void
    {
        /*
         * Beklenen payload:
         * [
         *   'to'           => 'kullanici@example.com',
         *   'subject'      => 'Konu',
         *   'template_key' => 'user_welcome',
         *   'data'         => ['name' => '...', ...],
         * ]
         *
         * Aşama 9 implementasyonu:
         * $mailer = new \GO\Services\MailService();
         * $mailer->sendTemplate($payload['to'], $payload['template_key'], $payload['data']);
         */

        // V1 placeholder — Aşama 9'da tamamlanacak
        $logPath = defined('LOG_PATH') ? LOG_PATH : GO_ROOT . '/storage/logs';
        $msg = sprintf(
            '[%s] SendEmailJob to=%s template=%s' . PHP_EOL,
            date('Y-m-d H:i:s'),
            $payload['to'] ?? 'unknown',
            $payload['template_key'] ?? 'unknown'
        );
        @file_put_contents($logPath . '/queue-mail.log', $msg, FILE_APPEND | LOCK_EX);
    }

    public function queueName(): string
    {
        return 'email';
    }
}
