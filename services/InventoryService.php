<?php
class InventoryService extends BaseService
{
    private IngredientRepository $repo;

    public function __construct()
    {
        $this->repo = new IngredientRepository();
    }

    public function list(): array
    {
        return array_map(fn($r) => Ingredient::fromArray($r), $this->repo->all());
    }

    public function alerts(): array
    {
        return array_map(fn($r) => Ingredient::fromArray($r), $this->repo->alerts());
    }

    public function save(?int $id, array $d): Ingredient
    {
        $this->validate($d);

        $payload = [
            'name' => trim($d['name']),
            'unit' => trim($d['unit']),
            'stock_qty' => (float)$d['stock_qty'],
            'reorder_point' => (float)$d['reorder_point'],
            'expiration_date' => !empty($d['expiration_date']) ? $d['expiration_date'] : null,
            'cost_per_unit' => (float)($d['cost_per_unit'] ?? 0),
        ];

        if ($id) {
            if (!$this->repo->find($id)) throw new Exception('Ingredient not found.');
            $this->repo->update($id, $payload);
        } else {
            $id = $this->repo->create($payload);
        }

        return Ingredient::fromArray($this->repo->find($id));
    }

    public function delete(int $id): void
    {
        if (!$this->repo->find($id)) throw new Exception('Ingredient not found.');

        if ($this->repo->isUsedInRecipes($id))
            throw new Exception('Cannot delete: ingredient is used in one or more recipes.');

        $this->repo->delete($id);
    }

    private function validate(array $d): void
    {
        if (empty(trim($d['name'] ?? ''))) throw new Exception('Name is required.');
        if (empty(trim($d['unit'] ?? ''))) throw new Exception('Unit is required.');
        if (!is_numeric($d['stock_qty'] ?? null)) throw new Exception('Stock must be numeric.');
        if (!is_numeric($d['reorder_point'] ?? null)) throw new Exception('Reorder point must be numeric.');
        if ((float)$d['stock_qty'] < 0) throw new Exception('Stock cannot be negative.');
        if ((float)$d['reorder_point'] < 0) throw new Exception('Reorder point cannot be negative.');

        if (!empty($d['expiration_date'])) {
            $ts = strtotime($d['expiration_date']);
            if (!$ts) throw new Exception('Invalid expiration date.');
        }
    }
}
