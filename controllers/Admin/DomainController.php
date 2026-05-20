<?php

namespace GO\Controllers\Admin;

use GO\Core\BaseController;
use GO\Models\DomainRequestModel;

class DomainController extends BaseController
{
    public function __construct()
    {
        require_admin();
    }

    public function index(): void
    {
        $requests = (new DomainRequestModel())->kanbanList();

        $this->view('admin/domain/index', [
            'title'    => 'Domain Talepleri',
            'layout'   => 'layouts/admin',
            'requests' => $requests,
        ]);
    }
}
