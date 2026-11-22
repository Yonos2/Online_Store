<?php

namespace App\Controllers;

use App\Models\ProductNotification;
use Database;

final class ProductNotificationController
{
    public function dismiss(): void
    {
        session_start();
        $this->requireCustomer();
        $this->requirePost();
        $this->requireValidCsrf();

        $notificationId = filter_input(
            INPUT_POST,
            'notification_id',
            FILTER_VALIDATE_INT
        );
        if (!$notificationId || $notificationId < 1) {
            http_response_code(400);
            exit('Invalid notification ID.');
        }

        (new ProductNotification((new Database())->connect()))
            ->dismiss((int) $_SESSION['user_id'], $notificationId);
        header('Location: ' . route_url('dashboard'));
        exit;
    }

    public function markRead(): void
    {
        session_start();
        $this->requireCustomer();
        $this->requirePost();
        $this->requireValidCsrf();

        $notificationIds = $_POST['notification_ids'] ?? [];
        if (!is_array($notificationIds)) {
            http_response_code(400);
            exit('Invalid notification list.');
        }

        $notifications = new ProductNotification((new Database())->connect());
        foreach ($notificationIds as $notificationId) {
            $validId = filter_var($notificationId, FILTER_VALIDATE_INT);
            if ($validId === false || $validId < 1) {
                http_response_code(400);
                exit('Invalid notification ID.');
            }

            $notifications->markRead((int) $_SESSION['user_id'], $validId);
        }

        header('Location: ' . route_url('dashboard'));
        exit;
    }

    private function requireCustomer(): void
    {
        if (
            !isset($_SESSION['user_id']) ||
            ($_SESSION['role'] ?? '') !== 'customer'
        ) {
            header('Location: ' . route_url('login'));
            exit;
        }
    }

    private function requirePost(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            exit('Method not allowed.');
        }
    }

    private function requireValidCsrf(): void
    {
        $token = $_POST['csrf_token'] ?? '';
        if (
            empty($_SESSION['csrf_token']) ||
            !is_string($token) ||
            !hash_equals($_SESSION['csrf_token'], $token)
        ) {
            http_response_code(403);
            exit('Invalid form submission. Please refresh and try again.');
        }
    }
}
