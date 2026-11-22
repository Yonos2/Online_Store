<?php

namespace App\Models;

use PDO;
use PayMongo;
use RuntimeException;

final class Checkout
{
    public function __construct(private PDO $pdo)
    {
    }

    public function summary(int $userId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT ci.id AS cart_item_id, ci.quantity, p.name, p.price,
                   p.stock, p.status, (ci.quantity * p.price) AS subtotal
            FROM cart_items ci
            INNER JOIN products p ON p.id = ci.product_id
            WHERE ci.user_id = :user_id
            ORDER BY ci.created_at DESC
        ");
        $stmt->execute(['user_id' => $userId]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $total = array_sum(array_map(
            static fn (array $item): float => (float) $item['subtotal'],
            $items
        ));
        return [$items, $total];
    }

    public function placeOrder(
        int $userId,
        string $recipient,
        string $phone,
        string $address,
        string $paymentMethod
    ): array {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("
                SELECT ci.id AS cart_item_id, ci.quantity,
                       p.id AS product_id, p.name AS product_name,
                       p.price, p.stock, p.status
                FROM cart_items ci
                INNER JOIN products p ON p.id = ci.product_id
                WHERE ci.user_id = :user_id
                ORDER BY ci.id
                FOR UPDATE OF ci, p
            ");
            $stmt->execute(['user_id' => $userId]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!$items) {
                throw new RuntimeException('Your cart is empty. Add products before checking out.');
            }

            $total = 0.0;
            foreach ($items as &$item) {
                $quantity = filter_var($item['quantity'], FILTER_VALIDATE_INT);
                $stock = filter_var($item['stock'], FILTER_VALIDATE_INT);
                if ($item['status'] !== 'active') {
                    throw new RuntimeException('A product in your cart is no longer available.');
                }
                if ($quantity === false || $quantity < 1) {
                    throw new RuntimeException('Invalid quantity for ' . $item['product_name'] . '.');
                }
                if ($stock === false || $stock < 0 || $quantity > $stock) {
                    throw new RuntimeException('Insufficient stock for ' . $item['product_name'] . '.');
                }
                if (!is_numeric($item['price']) || !is_finite((float) $item['price']) || (float) $item['price'] < 0) {
                    throw new RuntimeException('Invalid product price. Please contact the seller.');
                }
                $item['quantity'] = $quantity;
                $item['stock'] = $stock;
                $item['price'] = (float) $item['price'];
                $total += round($item['price'] * $quantity, 2);
            }
            unset($item);

            $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));
            $stmt = $this->pdo->prepare("
                INSERT INTO orders (
                    user_id, order_number, recipient_name, phone, shipping_address,
                    total_amount, payment_method, payment_status, order_status,
                    created_at, updated_at
                ) VALUES (
                    :user_id, :order_number, :recipient_name, :phone, :shipping_address,
                    :total_amount, :payment_method, 'pending', 'pending',
                    CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
                ) RETURNING id
            ");
            $stmt->execute([
                'user_id' => $userId,
                'order_number' => $orderNumber,
                'recipient_name' => $recipient,
                'phone' => $phone,
                'shipping_address' => $address,
                'total_amount' => number_format($total, 2, '.', ''),
                'payment_method' => $paymentMethod
            ]);
            $orderId = (int) $stmt->fetchColumn();
            $paymentRedirectUrl = null;
            if ($paymentMethod === 'paymongo') {
                $session = (new PayMongo())->createCheckoutSession(
                    $orderNumber,
                    $orderId,
                    $userId,
                    $items,
                    (int) round($total * 100)
                );
                $checkoutSessionId = $session['data']['id'] ?? null;
                $checkoutUrl = $session['data']['attributes']['checkout_url'] ?? null;
                if (
                    !is_string($checkoutSessionId) ||
                    !is_string($checkoutUrl) ||
                    !str_starts_with($checkoutSessionId, 'cs_') ||
                    parse_url($checkoutUrl, PHP_URL_SCHEME) !== 'https' ||
                    parse_url($checkoutUrl, PHP_URL_HOST) !== 'checkout.paymongo.com'
                ) {
                    throw new RuntimeException('The payment service returned an invalid checkout link.');
                }
                $save = $this->pdo->prepare(
                    'UPDATE public.orders SET paymongo_checkout_session_id = :session_id WHERE id = :order_id'
                );
                $save->execute([':session_id' => $checkoutSessionId, ':order_id' => $orderId]);
                $paymentRedirectUrl = $checkoutUrl;
            }

            $insert = $this->pdo->prepare("
                INSERT INTO order_items
                    (order_id, product_id, product_name, quantity, unit_price, subtotal)
                VALUES
                    (:order_id, :product_id, :product_name, :quantity, :unit_price, :subtotal)
            ");
            $updateStock = $this->pdo->prepare("
                UPDATE products SET stock = stock - :deduct_quantity
                WHERE id = :product_id AND stock >= :required_quantity AND status = 'active'
            ");
            foreach ($items as $item) {
                $quantity = (int) $item['quantity'];
                $price = (float) $item['price'];
                $subtotal = round($price * $quantity, 2);
                $updateStock->execute([
                    'deduct_quantity' => $quantity,
                    'product_id' => $item['product_id'],
                    'required_quantity' => $quantity
                ]);
                if ($updateStock->rowCount() !== 1) {
                    throw new RuntimeException('Stock changed while checking out. Please try again.');
                }
                $insert->execute([
                    'order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'quantity' => $quantity,
                    'unit_price' => number_format($price, 2, '.', ''),
                    'subtotal' => number_format($subtotal, 2, '.', '')
                ]);
            }
            $stmt = $this->pdo->prepare('DELETE FROM cart_items WHERE user_id = :user_id');
            $stmt->execute(['user_id' => $userId]);
            $this->pdo->commit();
            return ['order_number' => $orderNumber, 'redirect_url' => $paymentRedirectUrl];
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }
}
