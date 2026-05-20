<?php

namespace GO\Controllers\Admin;

use GO\Core\BaseController;
use GO\Models\HostingRequestModel;

class HostingController extends BaseController
{
    public function __construct()
    {
        require_admin();
    }

    public function index(): void
    {
        $requests = (new HostingRequestModel())->kanbanList();

        $this->view('admin/hosting/index', [
            'title'    => 'Hosting Talepleri',
            'layout'   => 'layouts/admin',
            'requests' => $requests,
        ]);
    }
}
