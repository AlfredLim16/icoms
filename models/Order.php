<?php
class Order
{
    public ?int $id = null;
    public string $order_no = '';
    public int $user_id = 0;
    public string $cashier_name = '';
    public float $total = 0;
    public ?string $created_at = null;
    public array $items = [];

    public static function fromArray(array $r): self
    {
        $o = new self();
        $o->id = (int)$r['id'];
        $o->order_no = $r['order_no'];
        $o->user_id = (int)$r['user_id'];
        $o->cashier_name = $r['cashier_name'] ?? '';
        $o->total = (float)$r['total'];
        $o->created_at = $r['created_at'] ?? null;
        return $o;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'order_no' => $this->order_no,
            'user_id' => $this->user_id,
            'cashier_name' => $this->cashier_name,
            'total' => $this->total,
            'created_at' => $this->created_at,
            'items' => $this->items,
        ];
    }
}
