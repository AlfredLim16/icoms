<?php
    class ReportService extends BaseService {
        private OrderRepository $orderRepo;
        private WasteRepository $wasteRepo;

        public function __construct() {
            $this->orderRepo = new OrderRepository();
            $this->wasteRepo = new WasteRepository();
        }

        public function summary(string $from, string $to): array {
            [$from, $to] = $this->sanitizeRange($from, $to);

            return [
                'range'     => ['from' => $from, 'to' => $to],
                'sales'     => $this->orderRepo->summaryForRange($from, $to),
                'waste'     => $this->wasteRepo->summaryForRange($from, $to),
                'daily'     => $this->fillMissingDays($this->orderRepo->revenueByDay($from, $to), $from, $to),
                'top'       => $this->orderRepo->topSellingItems($from, $to, 5),
                'categories'=> $this->orderRepo->revenueByCategory($from, $to),
                'waste_by_reason' => $this->wasteRepo->byReasonForRange($from, $to),
                'top_wasted'      => $this->wasteRepo->topWastedIngredients($from, $to, 5),
            ];
        }

        public function recentOrders(int $limit = 20): array {
            return $this->orderRepo->recentDetailed($limit);
        }

        // Helpers
        /** Ensure dates are valid and range is not absurd (max 90 days). */
        private function sanitizeRange(?string $from, ?string $to): array {
            $to   = $this->validDate($to)   ?: date('Y-m-d');
            $from = $this->validDate($from) ?: date('Y-m-d', strtotime($to . ' -6 days'));

            if ($from > $to) {
                [$from, $to] = [$to, $from];
            }

            // cap range to 90 days
            $diff = (strtotime($to) - strtotime($from)) / 86400;
            if ($diff > 90) {
                $from = date('Y-m-d', strtotime($to . ' -90 days'));
            }
            return [$from, $to];
        }

        private function validDate(?string $d): ?string {
            if (!$d) return null;
            $ts = strtotime($d);
            return $ts ? date('Y-m-d', $ts) : null;
        }

        /** Fill gaps so line charts have continuous x-axis. */
        private function fillMissingDays(array $rows, string $from, string $to): array {
            $byDate = [];
            foreach ($rows as $r) $byDate[$r['d']] = $r;

            $out = [];
            $cur = strtotime($from);
            $end = strtotime($to);
            while ($cur <= $end) {
                $d = date('Y-m-d', $cur);
                $out[] = [
                    'd'       => $d,
                    'orders'  => isset($byDate[$d]) ? (int)$byDate[$d]['orders']  : 0,
                    'revenue' => isset($byDate[$d]) ? (float)$byDate[$d]['revenue'] : 0,
                ];
                $cur = strtotime('+1 day', $cur);
            }
            return $out;
        }
    }