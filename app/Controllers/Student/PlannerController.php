<?php
declare(strict_types=1);
namespace App\Controllers\Student;
use App\Core\Controller;

class PlannerController extends Controller {
    public function index(): void {
        $this->requireRole('student');
        echo $this->view->renderWithLayout('student/planner', 'layouts/app', [
            'title' => 'Planner', 'content' => $this->view->render('student/planner', []),
        ]);
    }
}
