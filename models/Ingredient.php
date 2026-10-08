<?php
class Ingredient
{
    public ?int $id = null;
    public string $name = '';
    public string $unit = '';
    public float  $stock_qty = 0;
    public float  $reorder_point = 0;
    public ?string $expiration_date = null;
    public float  $cost_per_unit = 0;
    public ?string $updated_at = null;

    public static function fromArray(array $r): self
    {
        $i = new self();
        $i->id = (int)$r['id'];
        $i->name = $r['name'];
        $i->unit = $r['unit'];
        $i->stock_qty = (float)$r['stock_qty'];
        $i->reorder_point = (float)$r['reorder_point'];
        $i->expiration_date = $r['expiration_date'];
        $i->cost_per_unit = (float)$r['cost_per_unit'];
        $i->updated_at = $r['updated_at'] ?? null;
        return $i;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'unit' => $this->unit,
            'stock_qty' => $this->stock_qty,
            'reorder_point' => $this->reorder_point,
            'expiration_date' => $this->expiration_date,
            'cost_per_unit' => $this->cost_per_unit,
            'updated_at' => $this->updated_at,
        ];
    }

    public function needsReorder(): bool
    {
        return $this->stock_qty <= $this->reorder_point;
    }

    public function expiresWithin(int $days = 7): bool
    {
        if (!$this->expiration_date) return false;
        return strtotime($this->expiration_date) <= strtotime("+$days days");
    }
}
