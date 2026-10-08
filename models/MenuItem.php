<?php
class MenuItem
{
    public ?int $id = null;
    public string $name = '';
    public string $category = '';
    public float  $price = 0;
    public int    $is_available = 1;
    public array  $recipe = [];
    public float  $cost_per_serving = 0;
    public ?int   $max_servings = null;
    public ?string $created_at = null;

    public static function fromArray(array $r): self
    {
        $m = new self();
        $m->id = (int)$r['id'];
        $m->name = $r['name'];
        $m->category = $r['category'] ?? 'Uncategorized';
        $m->price = (float)$r['price'];
        $m->is_available = (int)$r['is_available'];
        $m->created_at = $r['created_at'] ?? null;
        return $m;
    }

    public function computeCostAndServings(): void
    {
        $this->cost_per_serving = 0;
        $this->max_servings = null;

        foreach ($this->recipe as $r) {
            $qty = (float)$r['quantity'];
            $this->cost_per_serving += (float)$r['cost_per_unit'] * $qty;

            if ($qty > 0) {
                $canMake = (int)floor((float)$r['stock_qty'] / $qty);
                $this->max_servings = $this->max_servings === null
                    ? $canMake
                    : min($this->max_servings, $canMake);
            }
        }
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'category' => $this->category,
            'price' => $this->price,
            'is_available' => $this->is_available,
            'recipe' => $this->recipe,
            'cost_per_serving' => round($this->cost_per_serving, 2),
            'max_servings' => $this->max_servings,
            'created_at' => $this->created_at,
        ];
    }
}
