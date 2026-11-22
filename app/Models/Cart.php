<?php

namespace App\Models;

use PDO;

final class Cart
{
    public function __construct(private PDO $pdo)
    {
    }

    public function itemsForCustomer(int $userId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                ci.id AS cart_item_id,
                ci.quantity,
                p.id AS product_id,
                p.name,
                p.price,
                p.stock,
                pi.image_path,
                (ci.quantity * p.price) AS subtotal
            FROM cart_items ci
            INNER JOIN products p
                ON p.id = ci.product_id
            LEFT JOIN product_images pi
                ON pi.product_id = p.id
                AND pi.is_primary = TRUE
            WHERE ci.user_id = :user_id
            ORDER BY ci.created_at DESC
        ");
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addProduct(int $userId, int $productId, int $quantity): string
    {
        $this->pdo->beginTransaction();

        try {
            $stmt = $this->pdo->prepare("
                SELECT id, stock, status
                FROM products
                WHERE id = :product_id
                FOR UPDATE
            ");
            $stmt->execute(['product_id' => $productId]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$product || $product['status'] !== 'active') {
                $this->pdo->rollBack();
                return 'product_unavailable';
            }

            if ((int) $product['stock'] < 1) {
                $this->pdo->rollBack();
                return 'out_of_stock';
            }

            $stmt = $this->pdo->prepare("
                SELECT id, quantity
                FROM cart_items
                WHERE user_id = :user_id
                  AND product_id = :product_id
                LIMIT 1
            ");
            $stmt->execute([
                'user_id' => $userId,
                'product_id' => $productId
            ]);
            $cartItem = $stmt->fetch(PDO::FETCH_ASSOC);
            $newQuantity = $quantity + (int) ($cartItem['quantity'] ?? 0);

            if ($newQuantity > (int) $product['stock']) {
                $this->pdo->rollBack();
                return 'quantity_exceeds_stock';
            }

            if ($cartItem) {
                $stmt = $this->pdo->prepare("
                    UPDATE cart_items
                    SET quantity = :quantity
                    WHERE id = :id
                      AND user_id = :user_id
                ");
                $stmt->execute([
                    'quantity' => $newQuantity,
                    'id' => $cartItem['id'],
                    'user_id' => $userId
                ]);
            } else {
                $stmt = $this->pdo->prepare("
                    INSERT INTO cart_items (user_id, product_id, quantity)
                    VALUES (:user_id, :product_id, :quantity)
                ");
                $stmt->execute([
                    'user_id' => $userId,
                    'product_id' => $productId,
                    'quantity' => $quantity
                ]);
            }

            $this->pdo->commit();
            return 'added';
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }

    public function stockForItem(int $cartItemId, int $userId): int|false
    {
        $stmt = $this->pdo->prepare(
            'SELECT ci.id, p.stock
             FROM cart_items ci
             INNER JOIN products p ON p.id = ci.product_id
             WHERE ci.id = :cart_item_id
               AND ci.user_id = :user_id
             LIMIT 1'
        );
        $stmt->execute([
            'cart_item_id' => $cartItemId,
            'user_id' => $userId
        ]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        return $item ? (int) $item['stock'] : false;
    }

    public function removeItem(int $cartItemId, int $userId): void
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM cart_items
             WHERE id = :cart_item_id
               AND user_id = :user_id'
        );
        $stmt->execute([
            'cart_item_id' => $cartItemId,
            'user_id' => $userId
        ]);
    }

    public function updateQuantity(int $cartItemId, int $userId, int $quantity): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE cart_items
             SET quantity = :quantity
             WHERE id = :cart_item_id
               AND user_id = :user_id'
        );
        $stmt->execute([
            'quantity' => $quantity,
            'cart_item_id' => $cartItemId,
            'user_id' => $userId
        ]);
    }
}
