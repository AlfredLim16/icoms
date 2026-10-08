<?php
abstract class BaseRepository
{
    protected PDO $db;
    public function __construct()
    {
        $this->db = Database::conn();
    }

    protected function q(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    protected function fetchAll(string $sql, array $p = []): array
    {
        return $this->q($sql, $p)->fetchAll();
    }
    protected function fetchOne(string $sql, array $p = []): ?array
    {
        return $this->q($sql, $p)->fetch() ?: null;
    }
    protected function lastId(): int
    {
        return (int)$this->db->lastInsertId();
    }
}
