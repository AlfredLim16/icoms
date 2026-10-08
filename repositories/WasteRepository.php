<?php
class WasteRepository extends BaseRepository
{

    public function create(array $d): int
    {
        $this->q(
            "INSERT INTO waste_records (ingredient_id, quantity, reason, notes, user_id)
                VALUES (?, ?, ?, ?, ?)",
            [
                $d['ingredient_id'],
                $d['quantity'],
                $d['reason'],
                $d['notes'] ?: null,
                $d['user_id'],
            ]
        );
        return $this->lastId();
    }

    public function find(int $id): ?array
    {
        return $this->fetchOne(
            "SELECT w.*, i.name AS ingredient_name, i.unit AS ingredient_unit,
                        u.full_name AS user_name
                FROM waste_records w
                JOIN ingredients i ON i.id = w.ingredient_id
                JOIN users u       ON u.id = w.user_id
                WHERE w.id = ? LIMIT 1",
            [$id]
        );
    }

    public function recent(int $limit = 50, ?string $date = null): array
    {
        $date = $date ?: date('Y-m-d');
        return $this->fetchAll(
            "SELECT w.*, i.name AS ingredient_name, i.unit AS ingredient_unit,
                        u.full_name AS user_name
                FROM waste_records w
                JOIN ingredients i ON i.id = w.ingredient_id
                JOIN users u       ON u.id = w.user_id
                WHERE DATE(w.created_at) = ?
                ORDER BY w.id DESC
                LIMIT " . (int)$limit,
            [$date]
        );
    }

    public function todayByReason(): array
    {
        return $this->fetchAll(
            "SELECT reason, COUNT(*) cnt, COALESCE(SUM(quantity),0) total_qty
                FROM waste_records
                WHERE DATE(created_at) = CURDATE()
                GROUP BY reason"
        );
    }

    public function todayCount(): int
    {
        return (int)$this->fetchOne(
            "SELECT COUNT(*) c FROM waste_records WHERE DATE(created_at) = CURDATE()"
        )['c'];
    }

    public function summaryForRange(string $from, string $to): array
    {
        $row = $this->fetchOne(
            "SELECT COUNT(*) AS entries
                FROM waste_records WHERE DATE(created_at) BETWEEN ? AND ?",
            [$from, $to]
        );
        return ['entries' => (int)($row['entries'] ?? 0)];
    }

    public function byReasonForRange(string $from, string $to): array
    {
        return $this->fetchAll(
            "SELECT reason, COUNT(*) AS cnt, COALESCE(SUM(quantity),0) AS total_qty
                FROM waste_records
                WHERE DATE(created_at) BETWEEN ? AND ?
                GROUP BY reason
                ORDER BY cnt DESC",
            [$from, $to]
        );
    }

    public function topWastedIngredients(string $from, string $to, int $limit = 5): array
    {
        return $this->fetchAll(
            "SELECT i.name, i.unit, SUM(w.quantity) AS qty
                FROM waste_records w
                JOIN ingredients i ON i.id = w.ingredient_id
                WHERE DATE(w.created_at) BETWEEN ? AND ?
                GROUP BY i.id, i.name, i.unit
                ORDER BY qty DESC
                LIMIT " . (int)$limit,
            [$from, $to]
        );
    }
}