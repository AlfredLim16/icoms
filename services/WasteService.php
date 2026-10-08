<?php
class WasteService extends BaseService
{
    private WasteRepository $wasteRepo;
    private IngredientRepository $ingRepo;

    public function __construct()
    {
        $this->wasteRepo = new WasteRepository();
        $this->ingRepo = new IngredientRepository();
    }

    public function record(array $d, int $userId): array
    {
        $this->validate($d);

        $ingId = (int)$d['ingredient_id'];
        $qty = (float)$d['quantity'];

        $ing = $this->ingRepo->find($ingId);
        if (!$ing) throw new Exception('Ingredient not found.');

        $stock = (float)$ing['stock_qty'];
        if ($qty > $stock) {
            $needFmt = rtrim(rtrim(number_format($qty, 2, '.', ''), '0'), '.');
            $haveFmt = rtrim(rtrim(number_format($stock, 2, '.', ''), '0'), '.');
            throw new Exception(
                "Cannot waste more than what's in stock. Available: {$haveFmt} {$ing['unit']}, requested: {$needFmt} {$ing['unit']}."
            );
        }

        $db = Database::conn();
        $db->beginTransaction();

        try {
            $id = $this->wasteRepo->create([
                'ingredient_id' => $ingId,
                'quantity' => $qty,
                'reason' => $d['reason'],
                'notes' => trim($d['notes'] ?? ''),
                'user_id' => $userId,
            ]);

            $this->ingRepo->decrement($ingId, $qty);

            $db->commit();

            return WasteRecord::fromArray($this->wasteRepo->find($id))->toArray();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public function recent(int $limit = 50, ?string $date = null): array
    {
        return array_map(
            fn($r) => WasteRecord::fromArray($r)->toArray(),
            $this->wasteRepo->recent($limit, $date)
        );
    }

    public function todayByReason(): array
    {
        return $this->wasteRepo->todayByReason();
    }

    public function todayCount(): int
    {
        return $this->wasteRepo->todayCount();
    }

    private function validate(array $d): void
    {
        $allowedReasons = ['spill', 'spoilage', 'prep_error', 'other'];

        if (empty($d['ingredient_id']) || (int)$d['ingredient_id'] <= 0)
            throw new Exception('Please select an ingredient.');

        if (!is_numeric($d['quantity'] ?? null))
            throw new Exception('Quantity must be a number.');

        if ((float)$d['quantity'] <= 0)
            throw new Exception('Quantity must be greater than 0.');

        if (!in_array($d['reason'] ?? '', $allowedReasons, true))
            throw new Exception('Invalid waste reason.');

        if (!empty($d['notes']) && mb_strlen($d['notes']) > 500)
            throw new Exception('Notes are too long (max 500 chars).');
    }
}
