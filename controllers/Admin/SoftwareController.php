<?php

namespace GO\Controllers\Admin;

use GO\Core\BaseController;
use GO\Models\SoftwareRequestModel;

class SoftwareController extends BaseController
{
    public function __construct()
    {
        require_admin();
    }

    public function index(): void
    {
        $requests = (new SoftwareRequestModel())->kanbanList();

        $this->view('admin/software/index', [
            'title'    => 'Yazılım Talepleri',
            'layout'   => 'layouts/admin',
            'requests' => $requests,
        ]);
    }
}
