<?php
class IngredientRepository extends BaseRepository
{

    public function all(): array
    {
        return $this->fetchAll("SELECT * FROM ingredients ORDER BY name ASC");
    }

    public function find(int $id): ?array
    {
        return $this->fetchOne("SELECT * FROM ingredients WHERE id = ? LIMIT 1", [$id]);
    }

    public function create(array $d): int
    {
        $this->q(
            "INSERT INTO ingredients (name, unit, stock_qty, reorder_point, expiration_date, cost_per_unit)
                VALUES (?, ?, ?, ?, ?, ?)",
            [
                $d['name'],
                $d['unit'],
                $d['stock_qty'],
                $d['reorder_point'],
                $d['expiration_date'] ?: null,
                $d['cost_per_unit']
            ]
        );
        return $this->lastId();
    }

    public function update(int $id, array $d): void
    {
        $this->q(
            "UPDATE ingredients SET name=?, unit=?, stock_qty=?, reorder_point=?, expiration_date=?, cost_per_unit=?
                WHERE id=?",
            [
                $d['name'],
                $d['unit'],
                $d['stock_qty'],
                $d['reorder_point'],
                $d['expiration_date'] ?: null,
                $d['cost_per_unit'],
                $id
            ]
        );
    }

    public function delete(int $id): void
    {
        $this->q("DELETE FROM ingredients WHERE id=?", [$id]);
    }

    public function decrement(int $id, float $qty): void
    {
        $this->q(
            "UPDATE ingredients SET stock_qty = GREATEST(stock_qty - ?, 0) WHERE id = ?",
            [$qty, $id]
        );
    }

    public function increment(int $id, float $qty): void
    {
        $this->q(
            "UPDATE ingredients SET stock_qty = stock_qty + ? WHERE id = ?",
            [$qty, $id]
        );
    }

    public function alerts(int $days = 7): array
    {
        return $this->fetchAll(
            "SELECT id, name, unit, stock_qty, reorder_point, expiration_date
                FROM ingredients
                WHERE stock_qty <= reorder_point
                    OR (expiration_date IS NOT NULL AND expiration_date <= DATE_ADD(CURDATE(), INTERVAL ? DAY))
                ORDER BY expiration_date IS NULL, expiration_date ASC",
            [$days]
        );
    }

    public function counts(): array
    {
        $row = $this->fetchOne(
            "SELECT
                COUNT(*) AS total,
                SUM(stock_qty <= reorder_point) AS low_stock,
                SUM(expiration_date IS NOT NULL AND expiration_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)) AS expiring
                FROM ingredients"
        );
        return [
            'total'     => (int)($row['total'] ?? 0),
            'low_stock' => (int)($row['low_stock'] ?? 0),
            'expiring'  => (int)($row['expiring'] ?? 0),
        ];
    }

    public function isUsedInRecipes(int $id): bool
    {
        $row = $this->fetchOne("SELECT COUNT(*) c FROM recipes WHERE ingredient_id = ?", [$id]);
        return (int)$row['c'] > 0;
    }
}
