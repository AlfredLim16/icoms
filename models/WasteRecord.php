<?php
class WasteRecord
{
    public ?int $id = null;
    public int $ingredient_id = 0;
    public string $ingredient_name = '';
    public string $ingredient_unit = '';
    public float $quantity = 0;
    public string $reason = '';
    public ?string $notes = null;
    public int $user_id = 0;
    public string $user_name = '';
    public ?string $created_at = null;

    public static function fromArray(array $r): self
    {
        $w = new self();
        $w->id = (int)$r['id'];
        $w->ingredient_id = (int)$r['ingredient_id'];
        $w->ingredient_name = $r['ingredient_name'] ?? '';
        $w->ingredient_unit = $r['ingredient_unit'] ?? '';
        $w->quantity = (float)$r['quantity'];
        $w->reason = $r['reason'] ?? '';
        $w->notes = $r['notes'] ?? null;
        $w->user_id = (int)$r['user_id'];
        $w->user_name = $r['user_name'] ?? '';
        $w->created_at = $r['created_at'] ?? null;
        return $w;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'ingredient_id' => $this->ingredient_id,
            'ingredient_name' => $this->ingredient_name,
            'ingredient_unit' => $this->ingredient_unit,
            'quantity' => $this->quantity,
            'reason' => $this->reason,
            'notes' => $this->notes,
            'user_id' => $this->user_id,
            'user_name' => $this->user_name,
            'created_at' => $this->created_at,
        ];
    }
}
