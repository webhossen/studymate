<?php
declare(strict_types=1);
namespace App\Controllers\Admin;
use App\Core\Controller;

class AuditLogsController extends Controller {
    public function index(): void {
        $this->requireRole('admin');
        $logs = $this->app->audit->list(100, 0);
        echo $this->view->renderWithLayout('admin/audit_logs', 'layouts/app', [
            'title' => 'Audit Logs', 'content' => $this->view->render('admin/audit_logs', ['logs' => $logs]),
        ]);
    }
}
