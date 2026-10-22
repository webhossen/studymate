# StudyMate

Academic management and student productivity for schools and colleges — a
dependency-free PHP 8 application (no Composer packages required).

[![PHP](https://img.shields.io/badge/PHP-8.0%2B-777bb4?style=flat-square&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-10.4%2B-00758F?style=flat-square&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![License](https://img.shields.io/badge/license-MIT-8b5cf6?style=flat-square)](LICENSE)

<p align="center">
  <img src="docs/screenshots/01-landing.png" alt="StudyMate landing page" width="820">
</p>

## Screenshots

### Student workspace

| Dashboard | Focus timer | Assignments |
| :---: | :---: | :---: |
| <img src="docs/screenshots/03-student-dashboard.png" alt="Student dashboard" width="330"> | <img src="docs/screenshots/04-focus-timer.png" alt="Focus timer" width="330"> | <img src="docs/screenshots/05-student-assignments.png" alt="Student assignments" width="330"> |

### Teacher workspace

| Dashboard | Attendance | Resources |
| :---: | :---: | :---: |
| <img src="docs/screenshots/07-teacher-dashboard.png" alt="Teacher dashboard" width="330"> | <img src="docs/screenshots/08-teacher-attendance.png" alt="Teacher attendance" width="330"> | <img src="docs/screenshots/09-teacher-resources.png" alt="Teacher resources" width="330"> |

### Administration

| Users | Audit log |
| :---: | :---: |
| <img src="docs/screenshots/10-admin-users.png" alt="Admin users" width="420"> | <img src="docs/screenshots/11-admin-audit-log.png" alt="Admin audit log" width="420"> |

### Notifications &amp; confirmation

| Toast notifications | Confirmation dialog |
| :---: | :---: |
| <img src="docs/screenshots/12-toasts.png" alt="Toast notifications" width="420"> | <img src="docs/screenshots/13-logout-popup.png" alt="Logout confirmation" width="420"> |

### Mobile

| Dashboard | Navigation drawer |
| :---: | :---: |
| <img src="docs/screenshots/14-mobile-dashboard.png" alt="Mobile dashboard" width="240"> | <img src="docs/screenshots/15-mobile-nav.png" alt="Mobile navigation" width="240"> |

### Password reset

| Request a link | Choose a new password |
| :---: | :---: |
| <img src="docs/screenshots/17-forgot-sent.png" alt="Forgot password" width="420"> | <img src="docs/screenshots/18-reset-password.png" alt="Reset password" width="420"> |

<p align="center">
  <img src="docs/screenshots/02-login.png" alt="StudyMate sign in" width="520">
</p>

## Requirements

- PHP 8.0+ with PDO MySQL
- MySQL / MariaDB 10.4+
- Apache with `mod_rewrite` (or any web server that can route to `index.php`)
- [Composer](https://getcomposer.org) — only for PHPMailer, used to send password
  reset emails

## Setup

1. Install the dependencies:

   ```bash
   composer install
   ```

   The only package is `phpmailer/phpmailer`. Without it the application still
   runs, but password reset emails fall back to PHP's `mail()`.

2. Copy the configuration and edit it:

   ```bash
   cp .env.example .env
   ```

   `.env` holds the database credentials, `APP_URL` and the app name. It is
   **not** committed to the repository.

3. Create the database and apply the schema:

   ```bash
   php database/migrate.php
   ```

   Migrations live in `database/migrations/` and are tracked in the
   `migrations_applied` table, so `migrate.php` is safe to re-run.

4. Seed a demo dataset (optional):

   ```bash
   php database/setup.php
   ```

   This creates a department, a class with a subject, two students, a teacher,
   an administrator, an enrolment and a sample assignment.

5. Point the web server document root at the project so that
   `http://localhost/studymate/` reaches `index.php`, and make sure
   `mod_rewrite` is enabled (see `.htaccess`).

## Email

Password reset links are sent with **PHPMailer**. Configure the transport in
`.env`:

```ini
# smtp | mail | log
MAIL_TRANSPORT=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your.address@gmail.com
MAIL_PASSWORD=your-app-password
# tls | ssl | none
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@yourdomain.com
MAIL_FROM_NAME=StudyMate
```

- `smtp` — the normal choice for a hosted server
- `mail` — hand the message to the local MTA via PHP's `mail()`
- `log` — write the message to `storage/logs/mail.log` and send nothing
  (handy while developing)

For Gmail, use an **App Password** rather than your account password.

While `APP_DEBUG=true` the reset link is also shown on screen, so the flow can
be completed even before a mail server is configured.

## Demo accounts

After running `database/setup.php`:

| Role    | Email                      | Password      |
| ------- | -------------------------- | ------------- |
| Admin   | `admin@studymate.local`    | `DemoPass123!` |
| Teacher | `teacher@studymate.local`  | `DemoPass123!` |
| Student | `student@studymate.local`  | `DemoPass123!` |

## Layout

```
app/
  Core/           Front controller, router, view, database, auth, audit, mailer, upload
  Controllers/    Admin, Teacher, Student and Api controllers
  Helpers/        Global helper functions (e, url, asset, base_path, ...)
  Middleware/     Auth, guest and web (CSRF) middleware
config/           Application and database configuration
database/         Migrations and the demo seeder
public/           Document root, static assets
resources/views/   Templates, layouts and partials
routes/           web.php and api.php route tables
storage/          Uploads and logs (not committed)
tools/            Composer binary (not committed)
```

## Features

- **Students** — tasks, study planner, notes, focus timer with pomodoro
  sessions, study sessions, assignments and submissions, attendance, exams,
  grades, calendar and profile.
- **Teachers** — class rosters, attendance marking, assignments, submissions
  and grading, exams, live study-session monitoring, check-ins, resources and
  announcements.
- **Admins** — users and roles, departments, academic years, classes,
  subjects, enrolments, class-subject assignment, announcements, an audit log
  and settings.

## Design system

The UI is built on a small design system in `public/assets/css/app.css`:

- token layer (colour, surface, type, shape, motion) mapped onto Bootstrap's
  CSS variables
- dark and light themes, remembered in `localStorage`
- app shell with a grouped sidebar, off-canvas mobile drawer and a
  glass top bar
- toast notifications and confirmation dialogs for every action
- an animated logout button (`public/assets/css/logout-button.css`)

`test_desktop.php` is a self-contained regression check for the design system
and can be run from the CLI.

Screenshots live in [`docs/screenshots/`](docs/screenshots).

## Security

- CSRF tokens on every state-changing form
- output escaping through the `e()` helper
- role-based route guards and per-file authorisation for downloads
- password reset tokens are single-use, expire after an hour, and only a
  SHA-256 hash of the token is stored
- uploads validated by extension **and** sniffed MIME type, stored outside the
  document root and streamed through an authorised endpoint
- all administrative and teaching writes recorded in the audit log
