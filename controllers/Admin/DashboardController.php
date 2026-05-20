<?php

namespace GO\Controllers\Admin;

use GO\Core\BaseController;
use GO\Models\UserModel;
use GO\Models\ProjectModel;
use GO\Models\InvoiceModel;
use GO\Models\SupportTicketModel;
use GO\Models\DomainRequestModel;
use GO\Models\HostingRequestModel;

class DashboardController extends BaseController
{
    public function __construct()
    {
        require_admin();
    }

    public function index(): void
    {
        $userModel    = new UserModel();
        $projectModel = new ProjectModel();
        $invoiceModel = new InvoiceModel();
        $ticketModel  = new SupportTicketModel();

        // Stats
        $stats = [
            'total_users'       => $userModel->countAll(),
            'new_users_7d'      => $userModel->recentRegistrations(7),
            'total_projects'    => $projectModel->countAll(),
            'projects_by_status'=> $projectModel->countByStatus(),
            'total_invoices'    => $invoiceModel->countAll(),
            'total_revenue'     => $invoiceModel->totalRevenue(),
            'open_tickets'      => $ticketModel->countPending(),
            'pending_domain'    => (new DomainRequestModel())->countPending(),
            'pending_hosting'   => (new HostingRequestModel())->countPending(),
        ];

        // Recent activity
        $recentProjects = $projectModel->adminList(1, 8, 'queued');
        $pendingInvoices = $invoiceModel->pending();

        $this->view('admin/dashboard/index', [
            'title'          => 'Dashboard',
            'layout'         => 'layouts/admin',
            'stats'          => $stats,
            'recentProjects' => $recentProjects,
            'pendingInvoices'=> array_slice($pendingInvoices, 0, 5),
        ]);
    }
}
