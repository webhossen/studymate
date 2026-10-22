<?php
declare(strict_types=1);
/**
 * Seeds a complete, coherent demo dataset:
 * three users, a department, a class with a subject, the teacher link,
 * the student enrolment and a sample assignment.
 *
 * Run from the CLI:  php database/setup.php
 */
require __DIR__ . '/../app/Core/App.php';
use App\Core\App;

$app = new App(__DIR__ . '/..');
$app->bootstrap();

$hash = password_hash('DemoPass123!', PASSWORD_DEFAULT);

$app->db->transaction(function () use ($app, $hash) {
    /* ---------- Users ---------- */
    $adminId = $app->db->insert('INSERT INTO users (email, password, role, status) VALUES (?, ?, ?, ?)',
        ['admin@studymate.local', $hash, 'admin', 'active']);
    $app->db->insert('INSERT INTO student_profiles (user_id, first_name, last_name, student_id) VALUES (?, ?, ?, ?)',
        [$adminId, 'System', 'Administrator', 'ADM001']);

    $teacherUserId = $app->db->insert('INSERT INTO users (email, password, role, status) VALUES (?, ?, ?, ?)',
        ['teacher1@studymate.local', $hash, 'teacher', 'active']);
    $teacherId = $app->db->insert('INSERT INTO teacher_profiles (user_id, employee_id, first_name, last_name) VALUES (?, ?, ?, ?)',
        [$teacherUserId, 'EMP001', 'Teacher', 'One']);

    $studentUserId = $app->db->insert('INSERT INTO users (email, password, role, status) VALUES (?, ?, ?, ?)',
        ['student@studymate.local', $hash, 'student', 'active']);
    $studentId = $app->db->insert('INSERT INTO student_profiles (user_id, first_name, last_name, student_id) VALUES (?, ?, ?, ?)',
        [$studentUserId, 'Student', 'One', 'STU001']);

    $student2UserId = $app->db->insert('INSERT INTO users (email, password, role, status) VALUES (?, ?, ?, ?)',
        ['student2@studymate.local', $hash, 'student', 'active']);
    $student2Id = $app->db->insert('INSERT INTO student_profiles (user_id, first_name, last_name, student_id) VALUES (?, ?, ?, ?)',
        [$student2UserId, 'Student', 'Two', 'STU002']);

    /* ---------- Academy ---------- */
    $departmentId = $app->db->insert('INSERT INTO departments (name, code) VALUES (?, ?)',
        ['Computer Science', 'CS']);
    $classId = $app->db->insert('INSERT INTO classes (name, section, department_id) VALUES (?, ?, ?)',
        ['Database Fundamentals', 'A', $departmentId]);
    $subjectId = $app->db->insert('INSERT INTO subjects (name, code, department_id) VALUES (?, ?, ?)',
        ['Database Systems', 'DBS101', $departmentId]);
    $classSubjectId = $app->db->insert('INSERT INTO class_subjects (class_id, subject_id, teacher_id) VALUES (?, ?, ?)',
        [$classId, $subjectId, $teacherId]);

    /* The enrolment must point at the student's own profile. */
    $app->db->insert('INSERT INTO enrollments (student_id, class_id, status) VALUES (?, ?, ?)',
        [$studentId, $classId, 'active']);
    $app->db->insert('INSERT INTO enrollments (student_id, class_id, status) VALUES (?, ?, ?)',
        [$student2Id, $classId, 'active']);

    $app->db->insert(
        'INSERT INTO assignments (class_subject_id, title, description, due_date, max_marks, created_by)
         VALUES (?, ?, ?, ?, ?, ?)',
        [$classSubjectId, 'Practice set 1', 'Complete exercises 1 to 5 and hand in your answers.',
            date('Y-m-d H:i:s', strtotime('+7 days')), 50, $teacherId]
    );

    $app->db->insert(
        'INSERT INTO announcements (title, content, audience, author_id, is_published, published_at)
         VALUES (?, ?, ?, ?, 1, NOW())',
        ['Welcome to StudyMate', 'Your classes, tasks and study sessions are all in one place.',
            'all', $adminId]
    );

    echo "Admin:   admin@studymate.local / DemoPass123!\n";
    echo "Teacher: teacher@studymate.local / DemoPass123!\n";
    echo "Student: student@studymate.local / DemoPass123!\n";
    echo "Student: student2@studymate.local / DemoPass123!\n";
    echo "Class:   Database Fundamentals A — subject Database Systems (DBS101)\n";
});
