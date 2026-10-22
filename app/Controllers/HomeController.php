<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Core\Controller;

class HomeController extends Controller {
    public function index(): void {
        if ($this->auth->check()) $this->redirect($this->auth->roleRedirect());
        echo $this->view->renderWithLayout('home.landing', 'layouts/auth', ['title' => app_name()]);
    }

    public function landing(): void {
        if ($this->auth->check()) $this->redirect($this->auth->roleRedirect());
        echo $this->view->renderWithLayout('home.landing', 'layouts/auth', ['title' => app_name()]);
    }
}
