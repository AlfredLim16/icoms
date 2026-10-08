<?php
class UserRepository extends BaseRepository
{

    public function all(): array
    {
        return $this->fetchAll(
            "SELECT id, full_name, username, role, is_active, created_at
                FROM users ORDER BY role DESC, full_name ASC"
        );
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne(
            "SELECT id, full_name, username, role, is_active, created_at
                FROM users WHERE id = ? LIMIT 1",
            [$id]
        );
    }

    public function findByUsername(string $username): ?array
    {
        return $this->fetchOne(
            "SELECT * FROM users WHERE username = ? LIMIT 1",
            [$username]
        );
    }

    public function create(array $d): int
    {
        $this->q(
            "INSERT INTO users (full_name, username, password_hash, role, is_active)
                VALUES (?, ?, ?, ?, ?)",
            [$d['full_name'], $d['username'], $d['password_hash'], $d['role'], $d['is_active']]
        );
        return $this->lastId();
    }

    public function update(int $id, array $d): void
    {
        $this->q(
            "UPDATE users SET full_name=?, username=?, role=?, is_active=? WHERE id=?",
            [$d['full_name'], $d['username'], $d['role'], $d['is_active'], $id]
        );
    }

    public function updatePassword(int $id, string $hash): void
    {
        $this->q("UPDATE users SET password_hash=? WHERE id=?", [$hash, $id]);
    }

    public function delete(int $id): void
    {
        $this->q("DELETE FROM users WHERE id=?", [$id]);
    }

    public function countOwnersExcept(int $id): int
    {
        return (int)$this->fetchOne(
            "SELECT COUNT(*) c FROM users WHERE role='owner' AND id <> ?",
            [$id]
        )['c'];
    }
}
