<?php
declare(strict_types=1);
namespace App\Core;

class Audit {
    private Database $db;
    public function __construct(Database $db) { $this->db = $db; }
    public function log(string $action, ?int $userId = null, string $table = '', int $recordId = 0, array $old = [], array $new = []): void {
        $userId = $userId ?? ($_SESSION['auth']['user_id'] ?? null);
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $this->db->insert(
            'INSERT INTO audit_logs (user_id, action, table_name, record_id, old_values, new_values, ip_address, user_agent, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [$userId, $action, $table, $recordId, $old ? json_encode($old) : null, $new ? json_encode($new) : null, $ip, $ua, date('Y-m-d H:i:s')]
        );
    }
    public function list(int $limit = 100, int $offset = 0): array {
        return $this->db->get(
            'SELECT al.*, u.email FROM audit_logs al LEFT JOIN users u ON u.id = al.user_id ORDER BY al.id DESC LIMIT ? OFFSET ?',
            [$limit, $offset]
        );
    }
}
