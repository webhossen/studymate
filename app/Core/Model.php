<?php
declare(strict_types=1);
namespace App\Core;

abstract class Model {
    protected Database $db;
    protected string $table;
    protected string $primaryKey = 'id';

    public function __construct(Database $db) { $this->db = $db; }
    public function all(string $orderBy = null, string $direction = 'ASC'): array {
        $sql = "SELECT * FROM {$this->table}";
        if ($orderBy) $sql .= " ORDER BY {$orderBy} {$direction}";
        return $this->db->get($sql);
    }
    public function find(int $id): ?array {
        $row = $this->db->first("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?", [$id]);
        return $row ? $row : null;
    }
    public function create(array $data): int {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        return $this->db->insert($sql, array_values($data));
    }
    public function updateById(int $id, array $data): int {
        $set = implode(', ', array_map(fn ($col) => "$col = ?", array_keys($data)));
        $sql = "UPDATE {$this->table} SET {$set} WHERE {$this->primaryKey} = ?";
        return $this->db->update($sql, [...array_values($data), $id]);
    }
    public function deleteById(int $id): int {
        return $this->db->delete("DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?", [$id]);
    }
    public function query(string $sql, array $params = []): array {
        return $this->db->get($sql, $params);
    }
}
