# Deploying StudyMate

This guide covers getting the system onto your own machine, and then onto a
hosted server with a domain.

- [1. Get the code](#1-get-the-code)
- [2. Set up the database](#2-set-up-the-database)
- [3. Configure the application](#3-configure-the-application)
- [4. Run it on your local PC](#4-run-it-on-your-local-pc)
- [5. Host it with a domain](#5-host-it-with-a-domain)
- [6. Moving an existing database](#6-moving-an-existing-database)
- [7. Production checklist](#7-production-checklist)
- [8. Troubleshooting](#8-troubleshooting)

---

## 1. Get the code

### From GitHub (recommended)

```bash
git clone https://github.com/webhossen/studymate.git
cd studymate
```

### Without Git

Download the repository as a zip from
**https://github.com/webhossen/studymate**, unzip it, and rename the folder to
`studymate`.

### Install the dependencies

The only Composer package is PHPMailer (used to send password reset emails).

```bash
composer install
```

If you do not have Composer, download it from
<https://getcomposer.org/download/> and run `composer install` with your
PHP binary. The application still works without this step — emails then fall
back to PHP's own `mail()`.

**Requirements**

| | |
|---|---|
| PHP | 8.0 or newer |
| Extensions | `pdo_mysql`, `mbstring`, `openssl`, `ctype`, `filter`, `iconv`, `json` |
| Database | MySQL 5.7+ or MariaDB 10.4+ |
| Web server | Apache with `mod_rewrite`, or Nginx |

---

## 2. Set up the database

### Locally (XAMPP / MAMP / Laragon)

1. Start **Apache** and **MySQL** from the XAMPP control panel.
2. Open <http://localhost/phpmyadmin>.
3. Click the **Database** tab, create a database named `studymate`, and choose
   `utf8mb4_unicode_ci` as the collation.
4. Create a dedicated MySQL user instead of using `root`:
   - **Users** tab → **Add user**
   - User name `studymate`, password of your choice
   - Host: **localhost**
   - Tick **Create database** and grant **All privileges** on `studymate`
5. Now create the tables. Either:
   - import `database/migrations/001_schema.sql` (and `002`, `003`, `004`) through
     phpMyAdmin's **Import** tab, **or**
   - run the migration script, which applies them in order and records what it
     has run:

     ```bash
     php database/migrate.php
     ```

     Output looks like `Applied 4 migrations.` — run it again and it reports
     `Applied 0 migrations.`

6. Optionally add the demo dataset:

   ```bash
   php database/setup.php
   ```

### On a hosting account

1. Open **phpMyAdmin** in your control panel (cPanel → **Databases** → phpMyAdmin).
2. Create a database, e.g. `yourname_studymate`.
3. Create a user with a strong password and grant it **all privileges** on
   that database. Note the full prefixed names — hosting providers usually
   prefix both the database and the user with your account name.
4. Import the schema through **Import**, or upload `database/migrations/*.sql`
   and run them in order (001 → 004).

   If your host does not let you run PHP from the command line, use
   phpMyAdmin's **Import** tab and upload each migration file in numeric
   order. `001_schema.sql` must go first.

### The schema

Four migrations build the database:

| File | Adds |
|---|---|
| `001_schema.sql` | users, profiles, classes, subjects, enrolments, tasks, notes, assignments, submissions, exams, grades, attendance, study sessions, announcements, notifications, audit logs, password resets |
| `002_academic_years_and_resources.sql` | academic years and teacher resources |
| `003_file_metadata.sql` | attachment metadata on assignments and submissions |
| `004_student_id_consistency.sql` | aligns every `student_id` foreign key with `student_profiles(id)` |

---

## 3. Configure the application

Copy the example configuration and edit it:

```bash
cp .env.example .env
```

| Variable | What it does |
|---|---|
| `APP_ENV` | `development` or `production` |
| `APP_DEBUG` | `true` shows errors and the on-screen reset link. **Set to `false` in production.** |
| `APP_URL` | The public URL of the site, with no trailing slash. This drives every generated link. |
| `APP_NAME` | Shown in the interface and in emails |
| `APP_TIMEZONE` | e.g. `UTC`, `Asia/Dhaka`, `Europe/Berlin` |
| `DB_HOST` / `DB_PORT` | Usually `127.0.0.1` / `3306` |
| `DB_NAME` / `DB_USER` / `DB_PASSWORD` | Your database credentials |
| `SESSION_LIFETIME` | Session lifetime in minutes |
| `UPLOAD_MAX_SIZE` | Maximum upload size in bytes (10 MB by default) |
| `MAIL_*` | Outgoing mail — see [Email](#email) in the README |

**`APP_URL` is the single most important value.** It must match how the site is
actually reached:

| How you host it | `APP_URL` |
|---|---|
| Domain root, document root = `public/` | `https://studymate.example.com` |
| Domain root, document root = project folder | `https://studymate.example.com` |
| Sub-folder of a shared host | `https://example.com/studymate` |

The application figures out the asset paths from this value on its own — with a
sub-folder it serves them from `/studymate/public/assets/…`, and at a domain
root it serves them from `/assets/…`. Both are verified.

---

## 4. Run it on your local PC

### XAMPP (Windows / Mac / Linux)

1. Copy the project into your web root:

   ```
   C:\xampp\htdocs\studymate
   /Applications/XAMPP/htdocs/studymate
   ```

2. Update `.env` — `APP_URL=http://localhost/studymate` and your database
   credentials.

3. Apply the schema and the demo data:

   ```bash
   php database/migrate.php
   php database/setup.php
   ```

4. Make sure `mod_rewrite` is enabled in `httpd.conf`
   (`LoadModule rewrite_module modules/mod_rewrite.so`) and that
   `AllowOverride All` is set for the `htdocs` directory. XAMPP ships with both
   already in place.

5. Open <http://localhost/studymate/>.

### Docker (any platform)

```yaml
# docker-compose.yml
services:
  app:
    image: php:8.2-apache
    ports: ["8080:80"]
    volumes:
      - .:/var/www/html
      - ./public:/var/www/html/public
    environment:
      APACHE_DOCUMENT_ROOT: /var/www/html/public
    command: >-
      bash -c "a2enmod rewrite &&
               sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf &&
               sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf &&
               apache2-foreground"
  db:
    image: mysql:8
    environment:
      MYSQL_DATABASE: studymate
      MYSQL_ROOT_PASSWORD: secret
    ports: ["3306:3306"]
    volumes:
      - dbdata:/var/lib/mysql
volumes:
  dbdata:
```

```bash
docker compose up -d
php database/migrate.php    # run inside the container
```

### A note on `public/`

The `public/` folder is the only part that should be web-accessible. It holds
the front controller and the static assets; the application code, `.env` and
`storage/` all live outside it.

If your local setup serves the whole project folder (the XAMPP default), the
`.htaccess` rules still route everything correctly — but for anything
internet-facing, point the document root at `public/` instead.

---

## 5. Host it with a domain

### Shared hosting (cPanel, Plesk, Hostinger, Namecheap…)

**1. Buy the domain and point it at the host.** In the domain registrar's
DNS settings, create an `A` record for the root (`@`) and for `www` pointing
at the host's IP address. Propagation takes a few minutes to a few hours.

**2. Create the database.** In cPanel → **MySQL Databases**, create a
database and a user, and add the user to the database with **ALL PRIVILEGES**.
Write down the prefixed names — they are usually `youruser_studymate`.

**3. Upload the code.** Either:

- **Git (recommended)** — most hosts have a **Git Version Manager** or a
  terminal. Clone straight from GitHub:

  ```bash
  git clone https://github.com/webhossen/studymate.git ~/public_html/studymate
  cd ~/public_html/studymate
  composer install --no-dev --optimize-autoloader
  ```

- **FTP** — upload the folder with FileZilla, then run
  `composer install` from the **Terminal** in cPanel (or upload the `vendor/`
  folder from a machine that has Composer).

**4. Create `.env`.** Copy `.env.example` to `.env` and fill it in with the
database credentials from step 2 and your domain:

```ini
APP_ENV=production
APP_DEBUG=false
APP_URL=https://studymate.example.com
```

Use the **Terminal** in cPanel, or the phpMyAdmin file editor.

**5. Set the document root.** In cPanel → **Domains** → your domain →
**Document Root**, point it at:

```
/home/youruser/public_html/studymate/public
```

This is the important step — it keeps `.env`, `app/` and `storage/` outside
the web root. If your host does not allow a document root outside
`public_html`, leave it as the project folder instead; the rewrite rules still
work and only the `public/` folder is exposed, but the document root is
preferable.

**6. Apply the schema.** From the **Terminal**:

```bash
cd ~/public_html/studymate
php database/migrate.php
```

**7. Make the storage writable.** The uploads and log directories need write
access:

```bash
chmod -R 775 storage
chown -R youruser:youruser storage
```

**8. Enable HTTPS.** In cPanel → **SSL/TLS Status** → **Run AutoSSL**. It
issues a free Let's Encrypt certificate. Then force HTTPS with a redirect in
`.htaccess`:

```apache
RewriteEngine On
RewriteCond %{HTTPS} !=on
RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

**9. Set the mail transport.** If the host provides SMTP, add the `MAIL_*`
values to `.env`. Otherwise set `MAIL_TRANSPORT=mail` to use the host's local
mailer.

### VPS or cloud server (Ubuntu / Debian)

**1. Provision the server.** A fresh Ubuntu 22.04 or 24.04 instance, pointed
at by the domain's `A` record.

**2. Install the stack:**

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y nginx apache2-utils mariadb-server php8.2-fpm php8.2-mysql \
    php8.2-mbstring php8.2-curl php8.2-xml unzip git
```

**3. Create the database and user:**

```bash
sudo mysql <<'SQL'
CREATE DATABASE studymate CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'studymate'@'localhost' IDENTIFIED BY 'a-strong-password';
GRANT ALL PRIVILEGES ON studymate.* TO 'studymate'@'localhost';
FLUSH PRIVILEGES;
SQL
```

**4. Install the application:**

```bash
sudo mkdir -p /var/www/studymate
sudo git clone https://github.com/webhossen/studymate.git /var/www/studymate
cd /var/www/studymate
composer install --no-dev --optimize-autoloader
sudo chown -R www-data:www-data storage
sudo chmod -R 775 storage
```

**5. Configure Nginx** — `/etc/nginx/sites-available/studymate`:

```nginx
server {
    listen 80;
    server_name studymate.example.com www.studymate.example.com;
    root /var/www/studymate/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    # Block direct access to the application code and secrets.
    location ~ ^/(app|config|database|resources|routes|storage|tools|vendor|\.env) {
        deny all;
    }

    client_max_body_size 12M;
}
```

```bash
sudo ln -s /etc/nginx/sites-available/studymate /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
```

**6. Create `.env`:**

```bash
sudo cp .env.example .env
sudo nano .env
```

Set `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://studymate.example.com`
and the database credentials. The database host stays `127.0.0.1` because
PHP and MySQL share the machine.

**7. Apply the schema:**

```bash
cd /var/www/studymate
php database/migrate.php
```

**8. Add HTTPS** with Certbot:

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d studymate.example.com -d www.studymate.example.com
```

Certbot rewrites the Nginx config to redirect to HTTPS and renews the
certificate automatically.

**9. Set the mail transport** in `.env`, and if you use a local MTA, install
one:

```bash
sudo apt install -y postfix
```

### Pointing a subdomain at the app

If the application lives at `https://example.com/studymate/` rather than on
its own domain, set `APP_URL=https://example.com/studymate` and make sure the
web server routes that prefix to the project. On Nginx that means a `location`
block with the matching prefix.

---

## 6. Moving an existing database

To move a local database to a hosted server, export it and import it.

**Export locally:**

```bash
mysqldump -u root -p --single-transaction --default-character-set=utf8mb4 \
    studymate > studymate.sql
```

**Import on the server** — through phpMyAdmin's **Import** tab, or:

```bash
mysql -u studymate -p studymate < studymate.sql
```

The schema is migrated by `database/migrate.php`, so on a fresh server you can
skip the export entirely and just run the migrations.

**Two things to fix after a move:**

1. `APP_URL` must match the new location, or every generated link will point
   back at the old address.
2. `APP_TIMEZONE` must match the server's timezone. The application
   automatically aligns the database session timezone with PHP's, so
   timestamps stay consistent either way.

---

## 7. Production checklist

Before going live:

- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false` — with debug on, PHP errors are printed to the page
- [ ] `APP_URL` uses `https://` and matches the real address
- [ ] The document root points at `public/`, so `.env`, `app/` and
      `storage/` are not reachable over HTTP
- [ ] The database user only has privileges on the StudyMate database
- [ ] A real, strong database password (not `root` with no password)
- [ ] `MAIL_TRANSPORT=smtp` with working SMTP credentials, and you have
      confirmed a reset email arrives
- [ ] HTTPS is enabled and HTTP redirects to HTTPS
- [ ] `storage/` is writable by the web server user
- [ ] `composer install --no-dev --optimize-autoloader` has been run
- [ ] The demo accounts are gone: run `php database/setup.php` **only** on a
      throwaway database, and delete those users on a real one:

      ```sql
      DELETE FROM users WHERE email LIKE '%@studymate.local';
      ```

---

## 8. Troubleshooting

**"The requested URL was not found" on every page**
`mod_rewrite` is not enabled, or `AllowOverride None` is set for the
directory. In XAMPP, enable `rewrite_module` in `httpd.conf` and set
`AllowOverride All` for `htdocs`.

**Stylesheets return 404 but the pages load**
`APP_URL` does not match the real address. Check what the HTML asks for and
what actually works — the asset URLs reveal which one is wrong.

**"Class 'PDOException' … Access denied for user"**
The `.env` database credentials do not match. On shared hosting the user and
database names are usually prefixed with your account name.

**"Base table or view not found"**
The schema has not been applied. Run `php database/migrate.php`.

**Password reset emails never arrive**
Set `MAIL_TRANSPORT=log` temporarily and check `storage/logs/mail.log` to
confirm the link is being generated, then fix the SMTP settings. While
`APP_DEBUG=true` the link is also shown on screen.

**Password reset links are rejected as expired**
The database server's timezone differs from the application's. The application
aligns the database session timezone automatically, so this normally means the
schema predates migration `004` — run `php database/migrate.php`.

**Uploads fail**
`storage/private_uploads` must be writable by the web server:

```bash
chmod -R 775 storage && chown -R www-data:www-data storage
```

Also raise the upload limits in `php.ini` (`upload_max_filesize`,
`post_max_size`) and in Nginx (`client_max_body_size`).

**The sidebar icons are missing or the layout is unstyled**
The stylesheet is not loading. Open the browser console, or right-click the
page → **View Source** and check the `<link>` paths.
