<?php
declare(strict_types=1);
namespace App\Core;
use PDO;
use PDOException;
use PDOStatement;

class Database {
    private ?PDO $pdo = null;
    public array $config;

    public function __construct(array $config) { $this->config = $config; }

    public function connection(): PDO {
        if ($this->pdo === null) {
            $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $this->config['host'], $this->config['port'], $this->config['database'], $this->config['charset']);
            try {
                $this->pdo = new PDO($dsn, $this->config['username'], $this->config['password'], $this->config['options']);
            } catch (PDOException $e) {
                error_log('DB connection failed: ' . $e->getMessage());
                throw new \RuntimeException('Database connection failed', 500, $e);
            }
        }
        return $this->pdo;
    }

    public function query(string $sql, array $params = []): PDOStatement {
        $stmt = $this->connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    public function get(string $sql, array $params = []): array {
        return $this->query($sql, $params)->fetchAll();
    }
    public function first(string $sql, array $params = []): ?array {
        $row = $this->query($sql, $params)->fetch();
        return $row === false ? null : $row;
    }
    public function insert(string $sql, array $params = []): int {
        $this->query($sql, $params);
        return (int) $this->connection()->lastInsertId();
    }
    public function update(string $sql, array $params = []): int {
        return (int) $this->query($sql, $params)->rowCount();
    }
    public function delete(string $sql, array $params = []): int {
        return (int) $this->query($sql, $params)->rowCount();
    }
    public function transaction(callable $callback) {
        $pdo = $this->connection();
        $pdo->beginTransaction();
        try { $result = $callback($pdo); $pdo->commit(); return $result; }
        catch (\Throwable $e) { $pdo->rollBack(); throw $e; }
    }
}
