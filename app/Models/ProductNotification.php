<?php

namespace App\Models;

use PDO;

final class ProductNotification
{
    public function __construct(private PDO $pdo)
    {
    }

    public function dismiss(int $customerId, int $notificationId): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO public.customer_product_notification_dismissals
                (customer_id, notification_id)
            SELECT :customer_id, n.id
            FROM public.product_notifications n
            WHERE n.id = :notification_id
            ON CONFLICT (customer_id, notification_id) DO NOTHING
        ");
        $stmt->execute([
            ':customer_id' => $customerId,
            ':notification_id' => $notificationId
        ]);
    }

    public function markRead(int $customerId, int $notificationId): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO public.customer_product_notification_reads
                (customer_id, notification_id)
            SELECT :customer_id, id
            FROM public.product_notifications
            WHERE id = :notification_id
            ON CONFLICT (customer_id, notification_id) DO NOTHING
        ");
        $stmt->execute([
            ':customer_id' => $customerId,
            ':notification_id' => $notificationId
        ]);
    }
}
