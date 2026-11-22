<?php

namespace App\Models;

use PDO;

final class SellerCatalog
{
    public function __construct(private PDO $pdo)
    {
    }

    public function products(): array
    {
        return $this->pdo->query(
            "SELECT p.id, p.name, p.price, p.stock, p.status,
                    c.name AS category_name
             FROM public.products p
             LEFT JOIN public.categories c ON c.id = p.category_id
             ORDER BY p.id DESC"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function inventoryProducts(): array
    {
        return $this->pdo->query(
            'SELECT p.id, p.name, p.stock, p.status,
                    c.name AS category_name
             FROM public.products p
             LEFT JOIN public.categories c ON c.id = p.category_id
             ORDER BY p.stock ASC, p.name ASC'
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function pendingOrderCount(): int
    {
        return (int) $this->pdo->query(
            "SELECT COUNT(*) FROM public.orders WHERE order_status = 'pending'"
        )->fetchColumn();
    }

    public function pendingOrders(int $limit = 0): array
    {
        $sql = "
            SELECT
                o.id,
                o.order_number,
                o.recipient_name,
                o.total_amount,
                o.payment_method,
                o.created_at,
                COALESCE(u.full_name, o.recipient_name) AS customer_name,
                u.email AS customer_email
            FROM public.orders o
            LEFT JOIN public.users u ON u.id = o.user_id
            WHERE o.order_status = 'pending'
            ORDER BY o.created_at DESC, o.id DESC
        ";
        if ($limit > 0) {
            $sql .= ' LIMIT ' . $limit;
        }

        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}
