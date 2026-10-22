<?php
declare(strict_types=1);
if (php_sapi_name() !== 'cli') { http_response_code(403); exit('CLI only.'); }
require __DIR__ . '/../app/Core/App.php';
use App\Core\App;
$app = new \App\Core\App(__DIR__ . '/..');
$app->bootstrap();
$files = glob(__DIR__ . '/migrations/*.sql');
sort($files);
$appliedTable = 'CREATE TABLE IF NOT EXISTS migrations_applied (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, filename VARCHAR(255) NOT NULL UNIQUE, applied_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
$app->db->query($appliedTable);
$applied = $app->db->get('SELECT filename FROM migrations_applied');
$appliedFiles = array_map(fn($r) => $r['filename'], $applied);
$ran = 0;
foreach ($files as $file) {
    $name = basename($file);
    if (in_array($name, $appliedFiles)) continue;
    $sql = file_get_contents($file);
    if ($sql === false) throw new \RuntimeException("Cannot read: {$file}");
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    foreach ($statements as $stmt) {
        $stmt = trim($stmt);
        if ($stmt === '') continue;
        $app->db->query($stmt);
    }
    $app->db->insert('INSERT INTO migrations_applied (filename) VALUES (?)', [$name]);
    $ran++;
}
echo "Applied {$ran} migration(s).\n";
