<?php

namespace GO\Controllers\Admin;

use GO\Core\BaseController;
use GO\Services\AuditService;

class SettingsController extends BaseController
{
    public function __construct()
    {
        require_admin();
    }

    public function index(): void
    {
        $settings = $this->getAllSettings();
        $smtp     = $this->getSmtp();
        $sectors  = $this->getSectors();

        $this->view('admin/settings/index', [
            'title'    => 'Site Ayarları',
            'layout'   => 'layouts/admin',
            'settings' => $settings,
            'smtp'     => $smtp,
            'sectors'  => $sectors,
        ]);
    }

    public function saveSite(): void
    {
        $this->verifyCsrf();
        require_super_admin();

        $updates = [
            'site_name'        => trim($this->input('site_name', '')),
            'site_url'         => rtrim(trim($this->input('site_url', '')), '/'),
            'site_description' => trim($this->input('site_description', '')),
            'contact_email'    => trim($this->input('contact_email', '')),
            'contact_phone'    => trim($this->input('contact_phone', '')),
            'company_name'     => trim($this->input('company_name', '')),
            'invoice_prefix'   => trim($this->input('invoice_prefix', 'GO-')),
            'maintenance_mode' => $this->input('maintenance_mode', '0'),
        ];

        foreach ($updates as $key => $value) {
            if (!empty($value) || $value === '0') {
                $this->updateSetting($key, $value);
            }
        }

        (new AuditService())->log(
            'settings_update',
            'settings',
            0,
            null,
            array_keys($updates),
            'admin',
            (int)current_admin()['id']
        );

        // Cache temizle
        (new \GO\Services\CacheService())->flush();

        flash_success('Site ayarları güncellendi.');
        $this->redirect('admin/ayarlar');
    }

    public function saveSmtp(): void
    {
        $this->verifyCsrf();
        require_super_admin();

        $host       = trim($this->input('smtp_host', ''));
        $port       = (int)$this->input('smtp_port', 587);
        $enc        = $this->input('smtp_encryption', 'tls');
        $user       = trim($this->input('smtp_user', ''));
        $pass       = $this->input('smtp_pass', '');
        $fromEmail  = trim($this->input('smtp_from_email', ''));
        $fromName   = trim($this->input('smtp_from_name', 'GO!'));

        try {
            $pdo = \GO\Core\Database::getInstance();
            $existing = $pdo->query("SELECT id FROM smtp_settings LIMIT 1")->fetch();

            if ($existing) {
                $stmt = $pdo->prepare("
                    UPDATE smtp_settings
                    SET host=?, port=?, encryption=?, username=?, from_email=?, from_name=?, is_active=1, updated_at=NOW()
                    " . (!empty($pass) ? ", password=?" : "") . "
                    WHERE id=?
                ");
                $params = [$host, $port, $enc, $user, $fromEmail, $fromName];
                if (!empty($pass)) $params[] = $pass;
                $params[] = (int)$existing['id'];
                $stmt->execute($params);
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO smtp_settings (host, port, encryption, username, password, from_email, from_name, is_active)
                    VALUES (?, ?, ?, ?, ?, ?, ?, 1)
                ");
                $stmt->execute([$host, $port, $enc, $user, $pass, $fromEmail, $fromName]);
            }
        } catch (\Throwable $e) {
            flash_error('SMTP kayıt başarısız: ' . $e->getMessage());
            $this->redirect('admin/ayarlar');
        }

        flash_success('SMTP ayarları güncellendi.');
        $this->redirect('admin/ayarlar');
    }

    public function saveFeatureFlags(): void
    {
        $this->verifyCsrf();
        require_super_admin();

        $flags = [
            'ai_enabled', 'kanban_enabled', 'zip_export_enabled',
            'domain_module_enabled', 'hosting_module_enabled',
            'software_module_enabled', 'chat_enabled', 'api_enabled',
            'notifications_enabled', 'support_enabled',
        ];

        foreach ($flags as $flag) {
            $value = isset($_POST[$flag]) ? '1' : '0';
            $this->updateSetting($flag, $value);
        }

        (new \GO\Services\CacheService())->flush();
        (new \GO\Services\FeatureFlagService())->invalidate();

        flash_success('Özellik bayrakları güncellendi.');
        $this->redirect('admin/ayarlar');
    }

    public function testSmtp(): void
    {
        $this->verifyCsrf();
        require_super_admin();

        $to      = trim($this->input('test_email', ''));
        $mailer  = new \GO\Services\MailService();
        $success = $mailer->send($to, 'GO! SMTP Test', '<h2>Test başarılı!</h2><p>SMTP ayarlarınız çalışıyor.</p>');

        if ($success) {
            flash_success("Test e-postası '{$to}' adresine gönderildi.");
        } else {
            flash_error('Test e-postası gönderilemedi. SMTP ayarlarını kontrol edin.');
        }

        $this->redirect('admin/ayarlar');
    }

    private function updateSetting(string $key, string $value): void
    {
        try {
            $pdo  = \GO\Core\Database::getInstance();
            $stmt = $pdo->prepare("
                INSERT INTO settings (setting_key, setting_value)
                VALUES (?, ?)
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()
            ");
            $stmt->execute([$key, $value]);
        } catch (\Throwable) {}
    }

    private function getAllSettings(): array
    {
        try {
            $pdo   = \GO\Core\Database::getInstance();
            $rows  = $pdo->query("SELECT setting_key, setting_value FROM settings")->fetchAll(\PDO::FETCH_ASSOC);
            $result = [];
            foreach ($rows as $row) $result[$row['setting_key']] = $row['setting_value'];
            return $result;
        } catch (\Throwable) { return []; }
    }

    private function getSmtp(): array
    {
        try {
            return \GO\Core\Database::getInstance()->query("SELECT * FROM smtp_settings LIMIT 1")->fetch(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable) { return []; }
    }

    private function getSectors(): array
    {
        try {
            return \GO\Core\Database::getInstance()->query("SELECT * FROM sectors ORDER BY sort_order ASC")->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable) { return []; }
    }
}
