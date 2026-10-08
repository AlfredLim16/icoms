<?php
    class OrderRepository extends BaseRepository {

        public function create(array $d): int {
            $this->q(
                "INSERT INTO orders (order_no, user_id, total) VALUES (?, ?, ?)",
                [$d['order_no'], $d['user_id'], $d['total']]
            );
            return $this->lastId();
        }

        public function addItem(array $d): int {
            $this->q(
                "INSERT INTO order_items (order_id, menu_item_id, quantity, unit_price, subtotal)
                VALUES (?, ?, ?, ?, ?)",
                [$d['order_id'], $d['menu_item_id'], $d['quantity'], $d['unit_price'], $d['subtotal']]
            );
            return $this->lastId();
        }

        public function find(int $id): ?array {
            return $this->fetchOne(
                "SELECT o.*, u.full_name AS cashier_name
                FROM orders o JOIN users u ON u.id = o.user_id
                WHERE o.id = ? LIMIT 1", [$id]
            );
        }

        public function itemsOf(int $orderId): array {
            return $this->fetchAll(
                "SELECT oi.*, m.name AS menu_name
                FROM order_items oi
                JOIN menu_items m ON m.id = oi.menu_item_id
                WHERE oi.order_id = ?
                ORDER BY oi.id ASC", [$orderId]
            );
        }

        public function recent(int $limit = 20, string $date = null): array {
            $date = $date ?: date('Y-m-d');
            return $this->fetchAll(
                "SELECT o.*, u.full_name AS cashier_name
                FROM orders o JOIN users u ON u.id = o.user_id
                WHERE DATE(o.created_at) = ?
                ORDER BY o.id DESC
                LIMIT " . (int)$limit, [$date]
            );
        }

        public function nextOrderNo(): string {
            $prefix = 'ORD-' . date('Ymd') . '-';
            $row = $this->fetchOne(
                "SELECT order_no FROM orders
                WHERE order_no LIKE ?
                ORDER BY id DESC LIMIT 1",
                [$prefix . '%']
            );

            $next = 1;
            if ($row) {
                $last = (int)substr($row['order_no'], -4);
                $next = $last + 1;
            }
            return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
        }

        public function todayStats(): array {
            $row = $this->fetchOne(
                "SELECT COUNT(*) AS orders, COALESCE(SUM(total), 0) AS revenue
                FROM orders WHERE DATE(created_at) = CURDATE()"
            );
            return [
                'orders'  => (int)($row['orders'] ?? 0),
                'revenue' => (float)($row['revenue'] ?? 0),
            ];
        }

        public function summaryForRange(string $from, string $to): array {
            $row = $this->fetchOne(
                "SELECT COUNT(*) AS orders, COALESCE(SUM(total), 0) AS revenue
                FROM orders WHERE DATE(created_at) BETWEEN ? AND ?",
                [$from, $to]
            );
            $orders = (int)($row['orders'] ?? 0);
            $revenue = (float)($row['revenue'] ?? 0);
            return [
                'orders'  => $orders,
                'revenue' => $revenue,
                'avg'     => $orders > 0 ? round($revenue / $orders, 2) : 0,
            ];
        }

        public function revenueByDay(string $from, string $to): array {
            return $this->fetchAll(
                "SELECT DATE(created_at) AS d, COUNT(*) AS orders, COALESCE(SUM(total),0) AS revenue
                FROM orders
                WHERE DATE(created_at) BETWEEN ? AND ?
                GROUP BY DATE(created_at)
                ORDER BY d ASC",
                [$from, $to]
            );
        }

        public function topSellingItems(string $from, string $to, int $limit = 5): array {
            return $this->fetchAll(
                "SELECT m.name, SUM(oi.quantity) AS qty, SUM(oi.subtotal) AS revenue
                FROM order_items oi
                JOIN orders o     ON o.id = oi.order_id
                JOIN menu_items m ON m.id = oi.menu_item_id
                WHERE DATE(o.created_at) BETWEEN ? AND ?
                GROUP BY m.id, m.name
                ORDER BY qty DESC
                LIMIT " . (int)$limit,
                [$from, $to]
            );
        }

        public function revenueByCategory(string $from, string $to): array {
            return $this->fetchAll(
                "SELECT COALESCE(m.category, 'Uncategorized') AS category, SUM(oi.subtotal) AS revenue
                FROM order_items oi
                JOIN orders o     ON o.id = oi.order_id
                JOIN menu_items m ON m.id = oi.menu_item_id
                WHERE DATE(o.created_at) BETWEEN ? AND ?
                GROUP BY m.category
                ORDER BY revenue DESC",
                [$from, $to]
            );
        }

        public function recentDetailed(int $limit = 20): array {
            return $this->fetchAll(
                "SELECT o.*, u.full_name AS cashier_name,
                        (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) AS item_lines
                FROM orders o JOIN users u ON u.id = o.user_id
                ORDER BY o.id DESC
                LIMIT " . (int)$limit
            );
        }

        public function search(array $filters): array {
            $sql = "SELECT o.*, u.full_name AS cashier_name,
                        (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) AS item_lines,
                        (SELECT COALESCE(SUM(quantity),0) FROM order_items oi WHERE oi.order_id = o.id) AS total_items
                    FROM orders o
                    JOIN users u ON u.id = o.user_id
                    WHERE 1=1";
            $params = [];

            if (!empty($filters['from'])) {
                $sql .= " AND DATE(o.created_at) >= ?";
                $params[] = $filters['from'];
            }
            if (!empty($filters['to'])) {
                $sql .= " AND DATE(o.created_at) <= ?";
                $params[] = $filters['to'];
            }
            if (!empty($filters['order_no'])) {
                $sql .= " AND o.order_no LIKE ?";
                $params[] = '%' . $filters['order_no'] . '%';
            }
            if (!empty($filters['cashier_id'])) {
                $sql .= " AND o.user_id = ?";
                $params[] = (int)$filters['cashier_id'];
            }

            $limit = (int)($filters['limit'] ?? 100);
            if ($limit < 1 || $limit > 500) $limit = 100;

            $sql .= " ORDER BY o.id DESC LIMIT $limit";

            return $this->fetchAll($sql, $params);
        }

        public function searchSummary(array $filters): array {
            $sql = "SELECT COUNT(*) AS cnt, COALESCE(SUM(o.total),0) AS revenue
                    FROM orders o
                    WHERE 1=1";
            $params = [];

            if (!empty($filters['from'])) {
                $sql .= " AND DATE(o.created_at) >= ?";
                $params[] = $filters['from'];
            }
            if (!empty($filters['to'])) {
                $sql .= " AND DATE(o.created_at) <= ?";
                $params[] = $filters['to'];
            }
            if (!empty($filters['order_no'])) {
                $sql .= " AND o.order_no LIKE ?";
                $params[] = '%' . $filters['order_no'] . '%';
            }
            if (!empty($filters['cashier_id'])) {
                $sql .= " AND o.user_id = ?";
                $params[] = (int)$filters['cashier_id'];
            }

            $row = $this->fetchOne($sql, $params);
            return [
                'orders'  => (int)($row['cnt'] ?? 0),
                'revenue' => (float)($row['revenue'] ?? 0),
            ];
        }

        public function cashiers(): array {
            return $this->fetchAll(
                "SELECT DISTINCT u.id, u.full_name
                FROM users u
                JOIN orders o ON o.user_id = u.id
                ORDER BY u.full_name"
            );
        }
    }
?>