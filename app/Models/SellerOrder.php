<?php

namespace App\Models;

use PDO;

final class SellerOrder
{
    public function __construct(private PDO $pdo)
    {
    }

    public function allForManagement(): array
    {
        return $this->pdo->query("
            SELECT o.id, o.order_number, o.recipient_name, o.total_amount,
                   o.payment_method, o.payment_status, o.order_status,
                   o.created_at, u.full_name AS customer_name,
                   u.email AS customer_email
            FROM public.orders o
            LEFT JOIN public.users u ON u.id = o.user_id
            ORDER BY o.created_at DESC
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function allWithDelivery(): array
    {
        return $this->pdo->query("
            SELECT id, order_number, recipient_name, phone, shipping_address,
                   total_amount, payment_method, payment_status, order_status,
                   created_at
            FROM orders
            ORDER BY created_at DESC, id DESC
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus(int $id, string $status): int
    {
        $stmt = $this->pdo->prepare("
            UPDATE public.orders
            SET order_status = :status, updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");
        $stmt->execute([':status' => $status, ':id' => $id]);

        return $stmt->rowCount();
    }

    public function exists(int $id): bool
    {
        $stmt = $this->pdo->prepare('SELECT id FROM orders WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return (bool) $stmt->fetchColumn();
    }

    public function details(int $id): array|false
    {
        $stmt = $this->pdo->prepare("
            SELECT o.*, u.full_name AS customer_name, u.email AS customer_email
            FROM public.orders o
            LEFT JOIN public.users u ON u.id = o.user_id
            WHERE o.id = :id
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function items(int $id): array
    {
        $stmt = $this->pdo->prepare("
            SELECT product_name, quantity, unit_price, subtotal
            FROM public.order_items
            WHERE order_id = :order_id
            ORDER BY id ASC
        ");
        $stmt->execute([':order_id' => $id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
