<?php

namespace App\Models;

use PDO;

final class Order
{
    public function __construct(private PDO $pdo)
    {
    }

    public function forCustomer(int $userId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                id,
                order_number,
                total_amount,
                payment_method,
                payment_status,
                order_status,
                created_at,
                updated_at
            FROM orders
            WHERE user_id = :user_id
            ORDER BY created_at DESC, id DESC
        ");
        $stmt->execute([':user_id' => $userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function customerOrder(int $orderId, int $userId): array|false
    {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM orders
            WHERE id = :id AND user_id = :user_id
            LIMIT 1
        ");
        $stmt->execute([':id' => $orderId, ':user_id' => $userId]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function byNumberForCustomer(string $orderNumber, int $userId): array|false
    {
        $stmt = $this->pdo->prepare("
            SELECT id, order_number, recipient_name, phone, shipping_address,
                   total_amount, payment_method, payment_status, order_status, created_at
            FROM orders
            WHERE order_number = :order_number AND user_id = :user_id
            LIMIT 1
        ");
        $stmt->execute([
            ':order_number' => $orderNumber,
            ':user_id' => $userId
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function items(int $orderId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT product_name, quantity, unit_price, subtotal
            FROM order_items
            WHERE order_id = :order_id
            ORDER BY id ASC
        ");
        $stmt->execute([':order_id' => $orderId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function customerUpdates(int $userId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT id, order_number, order_status, updated_at
            FROM public.orders
            WHERE user_id = :user_id
              AND order_status <> 'pending'
            ORDER BY updated_at DESC, id DESC
        ");
        $stmt->execute([':user_id' => $userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function productNotifications(int $userId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT n.id, n.product_name, n.action, n.created_at,
                   CASE WHEN r.notification_id IS NOT NULL THEN 1 ELSE 0 END AS is_read
            FROM public.product_notifications n
            LEFT JOIN public.customer_product_notification_reads r
                ON r.notification_id = n.id AND r.customer_id = :customer_id
            WHERE NOT EXISTS (
                SELECT 1
                FROM public.customer_product_notification_dismissals d
                WHERE d.notification_id = n.id AND d.customer_id = :dismissed_customer_id
            )
            ORDER BY n.created_at DESC, n.id DESC
            LIMIT 20
        ");
        $stmt->execute([
            ':customer_id' => $userId,
            ':dismissed_customer_id' => $userId
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
