<?php

namespace App\Models;

use PDO;

final class PaymentOrder
{
    public function __construct(private PDO $pdo)
    {
    }

    public function forCustomerByNumber(string $orderNumber, int $userId): array|false
    {
        $stmt = $this->pdo->prepare("
            SELECT id, order_number, payment_method, payment_status,
                   paymongo_checkout_session_id
            FROM public.orders
            WHERE order_number = :order_number
              AND user_id = :user_id
            LIMIT 1
        ");
        $stmt->execute([
            ':order_number' => $orderNumber,
            ':user_id' => $userId
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function byCheckoutSession(string $sessionId): array|false
    {
        $stmt = $this->pdo->prepare("
            SELECT id, paymongo_checkout_session_id
            FROM public.orders
            WHERE paymongo_checkout_session_id = :session_id
              AND payment_method = 'paymongo'
            LIMIT 1
        ");
        $stmt->execute([':session_id' => $sessionId]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function markPaid(int $orderId, ?string $paymentId): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE public.orders
            SET payment_status = 'paid',
                paymongo_payment_id = COALESCE(:payment_id, paymongo_payment_id),
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :order_id
              AND payment_status <> 'paid'
        ");
        $stmt->execute([
            ':payment_id' => $paymentId,
            ':order_id' => $orderId
        ]);
    }
}
