<?php

namespace GO\Services;

/**
 * Mail Service — PHPMailer gerektirmeden cPanel SMTP ile gönderim.
 * V1: socket üzerinden raw SMTP veya mail() fallback.
 */
class MailService
{
    private array $smtp = [];

    public function __construct()
    {
        $this->smtp = $this->loadSmtp();
    }

    /**
     * E-postayı kuyruğa ekle (tercih edilen yöntem).
     */
    public function queue(string $to, string $template, array $vars = []): void
    {
        $subject = $this->resolveSubject($template, $vars);
        $body    = $this->resolveBody($template, $vars);

        try {
            $pdo  = \GO\Core\Database::getInstance();
            $stmt = $pdo->prepare("
                INSERT INTO email_logs (to_email, subject, body_hash, template_key, status, created_at)
                VALUES (?, ?, ?, ?, 'queued', NOW())
            ");
            $stmt->execute([$to, $subject, md5($body), $template]);

            // Aynı zamanda kuyruğa iş ekle
            QueueService::push('send_email', [
                'to'       => $to,
                'subject'  => $subject,
                'body'     => $body,
                'template' => $template,
            ]);
        } catch (\Throwable) {
            // Kuyruk başarısız olsa da logla
        }
    }

    /**
     * Anında gönder.
     */
    public function send(string $to, string $subject, string $htmlBody): bool
    {
        if (empty($this->smtp) || empty($this->smtp['host'])) {
            return $this->sendWithMailFunction($to, $subject, $htmlBody);
        }
        return $this->sendWithSmtp($to, $subject, $htmlBody);
    }

    // ─── SMTP Gönderim (socket) ───────────────────────────────────────────────

    private function sendWithSmtp(string $to, string $subject, string $htmlBody): bool
    {
        $host       = $this->smtp['host'] ?? '';
        $port       = (int)($this->smtp['port'] ?? 587);
        $encryption = $this->smtp['encryption'] ?? 'tls';
        $username   = $this->smtp['username'] ?? '';
        $password   = $this->smtp['password'] ?? '';
        $fromEmail  = $this->smtp['from_email'] ?? $username;
        $fromName   = $this->smtp['from_name']  ?? 'GO!';

        $socketHost = $encryption === 'ssl' ? 'ssl://' . $host : $host;

        try {
            $socket = fsockopen($socketHost, $port, $errno, $errstr, 10);
            if (!$socket) return false;

            $this->smtpRead($socket); // 220

            $this->smtpWrite($socket, "EHLO " . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
            $this->smtpRead($socket);

            if ($encryption === 'tls') {
                $this->smtpWrite($socket, "STARTTLS");
                $this->smtpRead($socket);
                stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                $this->smtpWrite($socket, "EHLO " . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
                $this->smtpRead($socket);
            }

            $this->smtpWrite($socket, "AUTH LOGIN");
            $this->smtpRead($socket);
            $this->smtpWrite($socket, base64_encode($username));
            $this->smtpRead($socket);
            $this->smtpWrite($socket, base64_encode($password));
            $response = $this->smtpRead($socket);

            if (!str_starts_with(trim($response), '235')) {
                fclose($socket);
                return false;
            }

            $this->smtpWrite($socket, "MAIL FROM:<{$fromEmail}>");
            $this->smtpRead($socket);

            $this->smtpWrite($socket, "RCPT TO:<{$to}>");
            $this->smtpRead($socket);

            $this->smtpWrite($socket, "DATA");
            $this->smtpRead($socket);

            $message  = "From: {$fromName} <{$fromEmail}>\r\n";
            $message .= "To: <{$to}>\r\n";
            $message .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
            $message .= "MIME-Version: 1.0\r\n";
            $message .= "Content-Type: text/html; charset=UTF-8\r\n";
            $message .= "Content-Transfer-Encoding: base64\r\n";
            $message .= "\r\n";
            $message .= chunk_split(base64_encode($htmlBody));
            $message .= "\r\n.\r\n";

            $this->smtpWrite($socket, $message, false);
            $this->smtpRead($socket);

            $this->smtpWrite($socket, "QUIT");
            fclose($socket);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    private function smtpWrite($socket, string $data, bool $addNewline = true): void
    {
        fwrite($socket, $data . ($addNewline ? "\r\n" : ''));
    }

    private function smtpRead($socket): string
    {
        $data = '';
        while ($str = fgets($socket, 515)) {
            $data .= $str;
            if (substr($str, 3, 1) === ' ') break;
        }
        return $data;
    }

    private function sendWithMailFunction(string $to, string $subject, string $htmlBody): bool
    {
        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: GO! <noreply@go.net.tr>\r\n";

        return @mail($to, $subject, $htmlBody, $headers);
    }

    // ─── Template Resolver ────────────────────────────────────────────────────

    private function resolveSubject(string $template, array $vars): string
    {
        $subjects = [
            'user_welcome'          => 'GO!\'a Hoş Geldiniz 🎉',
            'user_password_reset'   => 'Şifre Sıfırlama Talebi',
            'ticket_created'        => 'Destek Talebiniz Alındı',
            'ticket_replied'        => 'Destek Talebinizde Yeni Yanıt',
            'invoice_created'       => 'Yeni Faturanız Hazır',
            'invoice_paid'          => 'Ödemeniz Onaylandı ✅',
            'project_status_update' => 'Proje Durum Güncelleme',
        ];
        return $subjects[$template] ?? 'GO! Bildirim';
    }

    private function resolveBody(string $template, array $vars): string
    {
        $name  = htmlspecialchars($vars['name'] ?? 'Değerli Müşteri', ENT_QUOTES, 'UTF-8');
        $link  = htmlspecialchars($vars['link'] ?? (defined('APP_URL') ? APP_URL : ''), ENT_QUOTES, 'UTF-8');
        $extra = htmlspecialchars($vars['subject'] ?? $vars['message'] ?? '', ENT_QUOTES, 'UTF-8');

        $baseStyle = 'font-family:system-ui,sans-serif;background:#0A1628;color:#F0F6FF;padding:2rem;border-radius:12px;max-width:600px;margin:0 auto';
        $btnStyle  = 'display:inline-block;background:#00D4FF;color:#0A1628;padding:.75rem 2rem;border-radius:8px;text-decoration:none;font-weight:700;margin-top:1rem';

        $bodies = [
            'user_welcome' => "<div style=\"{$baseStyle}\"><h2 style=\"color:#00D4FF\">GO!\'a Hoş Geldiniz!</h2><p>Merhaba {$name},</p><p>Hesabınız başarıyla oluşturuldu. GO! Chat ile projenizi başlatmak için panele gidin.</p><a href=\"{$link}/panel\" style=\"{$btnStyle}\">Panele Git</a></div>",
            'user_password_reset' => "<div style=\"{$baseStyle}\"><h2 style=\"color:#00D4FF\">Şifre Sıfırlama</h2><p>Merhaba {$name},</p><p>Şifrenizi sıfırlamak için aşağıdaki bağlantıya tıklayın. Bağlantı 1 saat geçerlidir.</p><a href=\"{$link}\" style=\"{$btnStyle}\">Şifremi Sıfırla</a><p style=\"color:rgba(255,255,255,.4);font-size:.8rem;margin-top:1rem\">Bu isteği siz yapmadıysanız, e-postayı yok sayın.</p></div>",
            'ticket_created' => "<div style=\"{$baseStyle}\"><h2 style=\"color:#00D4FF\">Destek Talebiniz Alındı</h2><p>Merhaba {$name},</p><p>\"<strong>{$extra}</strong>\" konulu destek talebiniz alındı. Ekibimiz en kısa sürede yanıtlayacak.</p><a href=\"{$link}/panel/destek\" style=\"{$btnStyle}\">Destek Taleplerim</a></div>",
            'invoice_paid' => "<div style=\"{$baseStyle}\"><h2 style=\"color:#22C55E\">Ödemeniz Onaylandı ✅</h2><p>Merhaba {$name},</p><p>Ödemeniz ekibimiz tarafından onaylandı. İyi günler dileriz.</p><a href=\"{$link}/panel/faturalar\" style=\"{$btnStyle}\">Faturalarım</a></div>",
        ];

        return $bodies[$template] ?? "<div style=\"{$baseStyle}\"><h2 style=\"color:#00D4FF\">GO! Bildirim</h2><p>Merhaba {$name}, sisteminizde yeni bir bildirim var.</p></div>";
    }

    private function loadSmtp(): array
    {
        try {
            $pdo  = \GO\Core\Database::getInstance();
            $stmt = $pdo->prepare("SELECT * FROM smtp_settings WHERE is_active = 1 LIMIT 1");
            $stmt->execute();
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable) {
            return [];
        }
    }
}
