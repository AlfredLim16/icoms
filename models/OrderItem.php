<?php
class OrderItem
{
    public ?int $id = null;
    public int $order_id = 0;
    public int $menu_item_id = 0;
    public string $menu_name = '';
    public int $quantity = 0;
    public float $unit_price = 0;
    public float $subtotal = 0;

    public static function fromArray(array $r): self
    {
        $i = new self();
        $i->id = (int)$r['id'];
        $i->order_id = (int)$r['order_id'];
        $i->menu_item_id = (int)$r['menu_item_id'];
        $i->menu_name = $r['menu_name'] ?? '';
        $i->quantity = (int)$r['quantity'];
        $i->unit_price = (float)$r['unit_price'];
        $i->subtotal = (float)$r['subtotal'];
        return $i;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'menu_item_id' => $this->menu_item_id,
            'menu_name' => $this->menu_name,
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'subtotal' => $this->subtotal,
        ];
    }
}
