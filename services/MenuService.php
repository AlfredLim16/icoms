<?php
class MenuService extends BaseService
{
    private MenuRepository $menu;
    private RecipeRepository $recipe;
    private IngredientRepository $ingRepo;

    public function __construct()
    {
        $this->menu = new MenuRepository();
        $this->recipe = new RecipeRepository();
        $this->ingRepo = new IngredientRepository();
    }

    public function list(): array
    {
        $rows = $this->menu->all();
        $out  = [];
        foreach ($rows as $r) {
            $out[] = $this->enrich(MenuItem::fromArray($r))->toArray();
        }
        return $out;
    }

    public function categories(): array
    {
        return array_column($this->menu->categories(), 'category');
    }

    public function save(?int $id, array $d, array $recipeRows): array
    {
        $this->validateHeader($d);

        $payload = [
            'name' => trim($d['name']),
            'category' => trim($d['category'] ?? '') ?: 'Uncategorized',
            'price' => (float)$d['price'],
            'is_available' => ((int)($d['is_available'] ?? 0)) ? 1 : 0,
        ];

        $cleanRecipe = [];
        foreach ($recipeRows as $r) {
            $ingId = (int)($r['ingredient_id'] ?? 0);
            $qty   = (float)($r['quantity'] ?? 0);
            if ($ingId <= 0 || $qty <= 0) continue;

            if (!isset($cleanRecipe[$ingId])) $cleanRecipe[$ingId] = 0;
            $cleanRecipe[$ingId] += $qty;
        }

        foreach (array_keys($cleanRecipe) as $ingId) {
            if (!$this->ingRepo->find($ingId))
                throw new Exception("Ingredient #$ingId does not exist.");
        }

        if ($id) {
            if (!$this->menu->find($id)) throw new Exception('Menu item not found.');
            $this->menu->update($id, $payload);
        } else {
            $id = $this->menu->create($payload);
        }

        $rows = [];
        foreach ($cleanRecipe as $ingId => $qty) {
            $rows[] = ['ingredient_id' => $ingId, 'quantity' => $qty];
        }
        $this->recipe->replaceFor($id, $rows);

        return $this->enrich(MenuItem::fromArray($this->menu->find($id)))->toArray();
    }

    public function delete(int $id): void
    {
        if (!$this->menu->find($id)) throw new Exception('Menu item not found.');
        $this->menu->delete($id);
    }

    private function enrich(MenuItem $m): MenuItem
    {
        $m->recipe = $this->recipe->byMenuItem($m->id);
        $m->computeCostAndServings();
        return $m;
    }

    private function validateHeader(array $d): void
    {
        if (empty(trim($d['name'] ?? '')))
            throw new Exception('Menu name is required.');

        if (!is_numeric($d['price'] ?? null))
            throw new Exception('Price must be a number.');

        if ((float)$d['price'] <= 0)
            throw new Exception('Price must be greater than 0.');

        if (mb_strlen(trim($d['name'])) > 100)
            throw new Exception('Menu name is too long (max 100 chars).');
    }
}
