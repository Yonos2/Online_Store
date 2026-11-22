<?php

namespace App\Controllers;

use App\Core\View;
use App\Models\SellerOrder;
use Database;
use PDOException;

final class SellerOrderController
{
    private const STATUSES = [
        'pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'
    ];

    public function orders(): void
    {
        $this->authorize();
        $this->ensureCsrf();
        $model = new SellerOrder((new Database())->connect());
        $message = '';
        $error = '';
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!$this->validCsrf($token)) {
                $error = 'Invalid request. Please refresh the page and try again.';
            } else {
                $id = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
                $status = $_POST['order_status'] ?? '';
                if (!$id || !in_array($status, self::STATUSES, true)) {
                    $error = 'Invalid order or status.';
                } else {
                    try {
                        $model->updateStatus($id, $status);
                        header('Location: ' . route_url('seller/orders?updated=1'));
                        exit;
                    } catch (PDOException $e) {
                        error_log('Seller order status update error: ' . $e->getMessage());
                        $error = 'Unable to update the order status. Check your database constraints.';
                    }
                }
            }
        }
        if (isset($_GET['updated'])) {
            $message = 'Order status updated successfully.';
        }
        $orders = $model->allForManagement();
        $newOrders = array_values(array_filter(
            $orders,
            static fn (array $order): bool => $order['order_status'] === 'pending'
        ));
        View::render('seller/orders', compact(
            'orders',
            'newOrders',
            'message',
            'error'
        ) + ['allowedStatuses' => self::STATUSES]);
    }

    public function sellerOrders(): void
    {
        $this->authorize();
        $this->ensureCsrf();
        $model = new SellerOrder((new Database())->connect());
        $message = '';
        $error = '';
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            $id = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
            $status = $_POST['order_status'] ?? '';
            if (!$this->validCsrf($token)) {
                $error = 'Invalid security token. Please refresh the page.';
            } elseif (!$id || $id < 1 || !is_string($status) || !in_array($status, self::STATUSES, true)) {
                $error = 'Invalid order or status.';
            } else {
                try {
                    if ($model->updateStatus($id, $status) > 0) {
                        $message = 'Order status updated successfully.';
                    } elseif ($model->exists($id)) {
                        $message = 'The order already has that status.';
                    } else {
                        $error = 'Order not found.';
                    }
                } catch (PDOException $e) {
                    error_log('Seller order status update error: ' . $e->getMessage());
                    $error = 'Unable to update the order status.';
                }
            }
        }
        $orders = $model->allWithDelivery();
        View::render('seller/seller_orders', compact(
            'orders',
            'message',
            'error'
        ) + ['allowedStatuses' => self::STATUSES]);
    }

    public function details(): void
    {
        $this->authorize();
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$id || $id < 1) {
            http_response_code(400);
            exit('Invalid order ID.');
        }
        $model = new SellerOrder((new Database())->connect());
        $order = $model->details($id);
        if (!$order) {
            http_response_code(404);
            exit('Order not found.');
        }
        $items = $model->items($id);
        View::render('seller/order_details', compact('order', 'items'));
    }

    private function authorize(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'seller') {
            header('Location: ' . route_url('seller/login'));
            exit;
        }
    }

    private function ensureCsrf(): void
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    private function validCsrf(mixed $token): bool
    {
        return is_string($token)
            && is_string($_SESSION['csrf_token'] ?? null)
            && $_SESSION['csrf_token'] !== ''
            && hash_equals($_SESSION['csrf_token'], $token);
    }
}
