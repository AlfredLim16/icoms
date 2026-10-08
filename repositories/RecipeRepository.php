<?php
class RecipeRepository extends BaseRepository
{

    public function byMenuItem(int $menuItemId): array
    {
        return $this->fetchAll(
            "SELECT r.id, r.ingredient_id, r.quantity,
                        i.name, i.unit, i.stock_qty, i.cost_per_unit
                FROM recipes r
                JOIN ingredients i ON i.id = r.ingredient_id
                WHERE r.menu_item_id = ?
                ORDER BY i.name ASC",
            [$menuItemId]
        );
    }

    public function replaceFor(int $menuItemId, array $rows): void
    {
        $this->q("DELETE FROM recipes WHERE menu_item_id = ?", [$menuItemId]);

        foreach ($rows as $r) {
            $ingId = (int)($r['ingredient_id'] ?? 0);
            $qty   = (float)($r['quantity'] ?? 0);
            if ($ingId <= 0 || $qty <= 0) continue;

            $this->q(
                "INSERT INTO recipes (menu_item_id, ingredient_id, quantity) VALUES (?, ?, ?)",
                [$menuItemId, $ingId, $qty]
            );
        }
    }

    public function countByIngredient(int $ingredientId): int
    {
        return (int)$this->fetchOne(
            "SELECT COUNT(*) c FROM recipes WHERE ingredient_id = ?",
            [$ingredientId]
        )['c'];
    }
}
