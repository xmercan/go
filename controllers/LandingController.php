<?php

namespace GO\Controllers;

use GO\Core\BaseController;

class LandingController extends BaseController
{
    public function home(): void
    {
        $sectors = [];
        try {
            $pdo     = \GO\Core\Database::getInstance();
            $stmt    = $pdo->prepare("SELECT * FROM sectors WHERE is_active = 1 ORDER BY sort_order ASC LIMIT 8");
            $stmt->execute();
            $sectors = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable) {
            // DB bağlantısı yoksa boş devam
        }

        $this->view('landing/home', compact('sectors'));
    }

    public function kvkk(): void
    {
        $this->view('landing/kvkk', ['title' => 'KVKK Aydınlatma Metni']);
    }

    public function terms(): void
    {
        $this->view('landing/terms', ['title' => 'Kullanım Koşulları']);
    }

    public function privacy(): void
    {
        $this->view('landing/privacy', ['title' => 'Gizlilik Politikası']);
    }

    public function contact(): void
    {
        $this->view('landing/contact', ['title' => 'İletişim']);
    }

    public function blog(): void
    {
        $this->view('landing/blog', ['title' => 'Blog']);
    }

    public function sectorDetail(string $slug): void
    {
        $sectors = [];
        try {
            $pdo    = \GO\Core\Database::getInstance();
            $stmt   = $pdo->prepare("SELECT s.*, st.title, st.features, st.sample_copy, st.cta_label
                FROM sectors s
                LEFT JOIN sector_templates st ON st.sector_id = s.id AND st.is_active = 1
                WHERE s.slug = ? AND s.is_active = 1 LIMIT 1");
            $stmt->execute([$slug]);
            $sector = $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Throwable) {
            $sector = null;
        }

        if (!$sector) {
            http_response_code(404);
            $this->view('errors/404', ['title' => 'Sektör Bulunamadı']);
            return;
        }

        $this->view('landing/sector', compact('sector'));
    }
}
