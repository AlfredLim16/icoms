<?php
class OrderService extends BaseService
{
    private OrderRepository $orderRepo;
    private MenuRepository $menuRepo;
    private RecipeRepository $recipeRepo;
    private IngredientRepository $ingRepo;

    public function __construct()
    {
        $this->orderRepo = new OrderRepository();
        $this->menuRepo = new MenuRepository();
        $this->recipeRepo = new RecipeRepository();
        $this->ingRepo = new IngredientRepository();
    }

    public function checkout(array $items, int $userId): array
    {
        if (empty($items))
            throw new Exception('Cart is empty.');

        $db = Database::conn();
        $db->beginTransaction();

        try {
            $ingredientDemand = [];
            $orderItems = [];
            $total = 0.0;
            $seen = [];

            foreach ($items as $item) {
                $menuId = (int)($item['menu_item_id'] ?? 0);
                $qty = (int)($item['quantity'] ?? 0);
                if ($menuId <= 0 || $qty <= 0) continue;

                if (isset($seen[$menuId])) {
                    $seen[$menuId] += $qty;
                } else {
                    $seen[$menuId] = $qty;
                }
            }

            foreach ($seen as $menuId => $qty) {
                $menu = $this->menuRepo->find($menuId);
                if (!$menu)
                    throw new Exception("Menu item #$menuId was not found.");
                if ((int)$menu['is_available'] === 0)
                    throw new Exception("Menu item '{$menu['name']}' is not available.");

                $unitPrice = (float)$menu['price'];
                $subtotal  = $unitPrice * $qty;
                $total    += $subtotal;

                $orderItems[] = [
                    'menu_item_id' => $menuId,
                    'menu_name' => $menu['name'],
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ];

                foreach ($this->recipeRepo->byMenuItem($menuId) as $r) {
                    $ingId = (int)$r['ingredient_id'];
                    $need = (float)$r['quantity'] * $qty;
                    if (!isset($ingredientDemand[$ingId])) $ingredientDemand[$ingId] = 0.0;
                    $ingredientDemand[$ingId] += $need;
                }
            }

            if (empty($orderItems))
                throw new Exception('No valid items to order.');

            foreach ($ingredientDemand as $ingId => $need) {
                $ing = $this->ingRepo->find($ingId);
                if (!$ing)
                    throw new Exception("Ingredient #$ingId no longer exists.");

                if ((float)$ing['stock_qty'] < $need) {
                    $needFmt = rtrim(rtrim(number_format($need, 2, '.', ''), '0'), '.');
                    $haveFmt = rtrim(rtrim(number_format((float)$ing['stock_qty'], 2, '.', ''), '0'), '.');
                    throw new Exception(
                        "Insufficient stock: {$ing['name']} — need {$needFmt} {$ing['unit']}, have {$haveFmt} {$ing['unit']}."
                    );
                }
            }

            $orderNo = $this->orderRepo->nextOrderNo();
            $orderId = $this->orderRepo->create([
                'order_no' => $orderNo,
                'user_id' => $userId,
                'total' => round($total, 2),
            ]);

            foreach ($orderItems as $oi) {
                $this->orderRepo->addItem([
                    'order_id' => $orderId,
                    'menu_item_id' => $oi['menu_item_id'],
                    'quantity' => $oi['quantity'],
                    'unit_price' => $oi['unit_price'],
                    'subtotal' => $oi['subtotal'],
                ]);
            }

            foreach ($ingredientDemand as $ingId => $need) {
                $this->ingRepo->decrement($ingId, $need);
            }

            $db->commit();

            $cashier = $this->orderRepo->find($orderId);

            return [
                'order_id' => $orderId,
                'order_no' => $orderNo,
                'cashier_name' => $cashier['cashier_name'] ?? '',
                'total' => round($total, 2),
                'created_at' => $cashier['created_at'],
                'items' => $orderItems,
                'deducted' => array_map(function ($ingId, $need) {
                    return ['ingredient_id' => $ingId, 'quantity' => $need];
                }, array_keys($ingredientDemand), $ingredientDemand),
            ];
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public function recent(int $limit = 20, ?string $date = null): array
    {
        return array_map(
            fn($r) => Order::fromArray($r)->toArray(),
            $this->orderRepo->recent($limit, $date)
        );
    }

    public function findWithItems(int $orderId): array
    {
        $order = $this->orderRepo->find($orderId);
        if (!$order) throw new Exception('Order not found.');

        $o = Order::fromArray($order);
        $o->items = array_map(
            fn($r) => OrderItem::fromArray($r)->toArray(),
            $this->orderRepo->itemsOf($orderId)
        );
        return $o->toArray();
    }

    public function todayStats(): array
    {
        return $this->orderRepo->todayStats();
    }

    public function search(array $filters): array
    {
        return array_map(
            fn($r) => Order::fromArray($r)->toArray() + [
                'cashier_name' => $r['cashier_name'] ?? '',
                'item_lines' => (int)($r['item_lines'] ?? 0),
                'total_items' => (int)($r['total_items'] ?? 0),
            ],
            $this->orderRepo->search($filters)
        );
    }

    public function searchSummary(array $filters): array
    {
        return $this->orderRepo->searchSummary($filters);
    }

    public function getCashiers(): array
    {
        return $this->orderRepo->cashiers();
    }
}
