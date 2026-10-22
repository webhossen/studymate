<?php
declare(strict_types=1);
namespace App\Controllers\Admin;
use App\Core\Controller;

class SettingsController extends Controller {
    public function index(): void {
        $this->requireRole('admin');
        echo $this->view->renderWithLayout('admin/settings', 'layouts/app', [
            'title' => 'Settings', 'content' => $this->view->render('admin/settings', []),
        ]);
    }
}
