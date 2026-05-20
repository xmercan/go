<?php

namespace GO\Controllers;

use GO\Core\BaseController;

class InstallController extends BaseController
{
    public function index(): void
    {
        // Kurulum tamamlandıysa ana sayfaya yönlendir
        if (file_exists(GO_ROOT . '/storage/installed.lock')) {
            $this->redirect('');
        }

        // Kurulum sihirbazı install/ dizininde bağımsız çalışıyor
        $this->redirect('install/');
    }
}
