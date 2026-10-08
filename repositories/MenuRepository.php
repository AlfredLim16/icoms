<?php
class MenuRepository extends BaseRepository
{

    public function all(): array
    {
        return $this->fetchAll(
            "SELECT * FROM menu_items ORDER BY category ASC, name ASC"
        );
    }

    public function find(int $id): ?array
    {
        return $this->fetchOne("SELECT * FROM menu_items WHERE id = ? LIMIT 1", [$id]);
    }

    public function create(array $d): int
    {
        $this->q(
            "INSERT INTO menu_items (name, category, price, is_available) VALUES (?, ?, ?, ?)",
            [$d['name'], $d['category'], $d['price'], $d['is_available']]
        );
        return $this->lastId();
    }

    public function update(int $id, array $d): void
    {
        $this->q(
            "UPDATE menu_items SET name=?, category=?, price=?, is_available=? WHERE id=?",
            [$d['name'], $d['category'], $d['price'], $d['is_available'], $id]
        );
    }

    public function delete(int $id): void
    {
        $this->q("DELETE FROM menu_items WHERE id=?", [$id]);
    }

    public function categories(): array
    {
        return $this->fetchAll(
            "SELECT DISTINCT category FROM menu_items WHERE category <> '' ORDER BY category"
        );
    }
}
