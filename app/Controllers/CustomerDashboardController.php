<?php

namespace App\Controllers;

use App\Core\View;
use App\Models\Order;
use App\Models\Product;
use Database;

final class CustomerDashboardController
{
    public function index(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'customer') {
            header('Location: ' . route_url('login'));
            exit;
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        $userId = (int) $_SESSION['user_id'];
        $pdo = (new Database())->connect();
        $products = (new Product($pdo))->activeCatalog();
        $orders = new Order($pdo);
        $customerOrders = $orders->forCustomer($userId);
        $productNotifications = $orders->productNotifications($userId);
        $unreadProductNotifications = array_values(array_filter(
            $productNotifications,
            static fn (array $notification): bool => !$notification['is_read']
        ));
        $viewedStatuses = $_SESSION['viewed_order_statuses'] ?? [];
        if (!is_array($viewedStatuses)) {
            $viewedStatuses = [];
        }
        $unreadOrderUpdates = array_values(array_filter(
            $customerOrders,
            static function (array $order) use ($viewedStatuses): bool {
                if ($order['order_status'] === 'pending') {
                    return false;
                }
                $marker = $order['order_status'] . '|' . $order['updated_at'];
                return ($viewedStatuses[(int) $order['id']] ?? null) !== $marker;
            }
        ));
        usort(
            $unreadOrderUpdates,
            static fn (array $a, array $b): int =>
                strtotime($b['updated_at']) <=> strtotime($a['updated_at'])
        );
        View::render('customer/dashboard', compact(
            'products',
            'orders',
            'customerOrders',
            'productNotifications',
            'unreadProductNotifications',
            'unreadOrderUpdates'
        ));
    }
}
