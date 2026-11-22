<?php

function recordProductNotification(
    PDO $pdo,
    string $action,
    string $productName
): void {
    $stmt = $pdo->prepare(
        'INSERT INTO public.product_notifications (product_name, action)
         VALUES (:product_name, :action)'
    );
    $stmt->execute([
        ':product_name' => $productName,
        ':action' => $action
    ]);
}
