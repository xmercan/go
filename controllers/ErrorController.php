<?php

namespace GO\Controllers;

use GO\Core\BaseController;

class ErrorController extends BaseController
{
    public function notFound(): void
    {
        http_response_code(404);
        $this->view('errors/404', [
            'title'  => 'Sayfa Bulunamadı',
            'layout' => false,
        ]);
    }

    public function serverError(): void
    {
        http_response_code(500);
        $this->view('errors/500', [
            'title'  => 'Sunucu Hatası',
            'layout' => false,
        ]);
    }
}
