<?php

namespace App\Controllers;

use App\Core\View;
use App\Models\Order;
use Database;
use JsonException;
use PDOException;

final class OrderController
{
    public function index(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        if (!$this->isCustomer()) {
            header('Location: ' . route_url('login'));
            exit;
        }

        $this->ensureCsrfToken();
        $orders = (new Order((new Database())->connect()))
            ->forCustomer((int) $_SESSION['user_id']);
        View::render('customer/my_orders', compact('orders'));
    }

    public function details(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        if (!$this->isCustomer()) {
            header('Location: ' . route_url('login'));
            exit;
        }

        $this->ensureCsrfToken();
        $orderId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$orderId || $orderId < 1) {
            http_response_code(400);
            exit('Invalid order ID.');
        }

        $orders = new Order((new Database())->connect());
        $order = $orders->customerOrder($orderId, (int) $_SESSION['user_id']);
        if (!$order) {
            http_response_code(404);
            exit('Order not found.');
        }

        $items = $orders->items((int) $order['id']);
        if ($order['order_status'] !== 'pending') {
            if (
                !isset($_SESSION['viewed_order_statuses']) ||
                !is_array($_SESSION['viewed_order_statuses'])
            ) {
                $_SESSION['viewed_order_statuses'] = [];
            }
            $_SESSION['viewed_order_statuses'][(int) $order['id']] =
                $order['order_status'] . '|' . $order['updated_at'];
        }

        View::render('customer/order_details', compact('order', 'items'));
    }

    public function pollUpdates(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        if (!$this->isCustomer()) {
            header('Location: ' . route_url('login'));
            exit;
        }

        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

        try {
            $orders = (new Order((new Database())->connect()))
                ->customerUpdates((int) $_SESSION['user_id']);
            $viewedStatuses = $_SESSION['viewed_order_statuses'] ?? [];
            if (!is_array($viewedStatuses)) {
                $viewedStatuses = [];
            }

            $updates = [];
            foreach ($orders as $order) {
                $marker = $order['order_status'] . '|' . $order['updated_at'];
                if (($viewedStatuses[(int) $order['id']] ?? null) === $marker) {
                    continue;
                }

                $updates[] = [
                    'id' => (int) $order['id'],
                    'order_number' => $order['order_number'],
                    'order_status' => $order['order_status'],
                    'updated_at' => $order['updated_at']
                ];
            }

            echo json_encode([
                'unread_count' => count($updates),
                'updates' => $updates
            ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        } catch (PDOException | JsonException $ex) {
            error_log('Order update polling error: ' . $ex->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Unable to load order updates.']);
        }
    }

    public function success(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        if (!$this->isCustomer()) {
            header('Location: ' . route_url('login'));
            exit;
        }
        $this->ensureCsrfToken();
        $orderNumber = $_SESSION['last_order_number'] ?? null;
        if (!$orderNumber) {
            header('Location: ' . route_url());
            exit;
        }
        $orders = new Order((new Database())->connect());
        $order = $orders->byNumberForCustomer(
            (string) $orderNumber,
            (int) $_SESSION['user_id']
        );
        if (!$order) {
            http_response_code(404);
            exit('Order not found.');
        }
        $items = $orders->items((int) $order['id']);
        $paymentMethod = match ($order['payment_method']) {
            'cod' => 'Cash on Delivery',
            'manual' => 'Manual Payment',
            'paymongo' => 'PayMongo online payment',
            default => ucfirst($order['payment_method'])
        };
        $retryPaymentUrl = $_SESSION['paymongo_retry_url'] ?? null;
        unset($_SESSION['paymongo_retry_url']);
        $paymentMessage = match ($_GET['payment'] ?? '') {
            'paid' => 'Payment confirmed. Thank you!',
            'cancelled' => 'Payment was not completed. Your order is saved and you can continue payment below.',
            'pending' => 'Payment is still pending. Complete payment using the link below.',
            'unverified' => 'We could not verify the payment yet. Please check again shortly or contact the store.',
            default => ''
        };
        $createdAt = date('M d, Y h:i A', strtotime($order['created_at']));
        View::render('customer/order_success', compact(
            'order',
            'items',
            'paymentMethod',
            'retryPaymentUrl',
            'paymentMessage',
            'createdAt'
        ));
    }

    private function isCustomer(): bool
    {
        return isset($_SESSION['user_id'])
            && ($_SESSION['role'] ?? '') === 'customer';
    }

    private function ensureCsrfToken(): void
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }
}
