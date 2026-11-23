<?php

namespace App\Controllers;

use App\Core\View;
use App\Models\Checkout;
use Database;
use PayMongo;
use PDOException;
use RuntimeException;
use Throwable;

final class CheckoutController
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
        $this->ensureSessionToken('csrf_token');
        $this->ensureSessionToken('checkout_token');
        $checkout = new Checkout((new Database())->connect());
        $userId = (int) $_SESSION['user_id'];
        $error = '';
        $successOrder = null;
        $selectedPaymentMethod = 'cod';

        if (isset($_GET['placed'])) {
            $successOrder = $_SESSION['checkout_order_number'] ?? null;
            unset($_SESSION['checkout_order_number']);
        }

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $selectedPaymentMethod = $_POST['payment_method'] ?? 'cod';
            try {
                if (!in_array($selectedPaymentMethod, ['cod', 'paymongo'], true)) {
                    throw new RuntimeException('Please select a valid payment method.');
                }
                if ($selectedPaymentMethod === 'paymongo' && !PayMongo::isConfigured()) {
                    throw new RuntimeException('Online payments are not configured yet. Please choose Cash on Delivery.');
                }
                $checkoutToken = $_POST['checkout_token'] ?? '';
                $sessionCheckoutToken = $_SESSION['checkout_token'] ?? '';
                if (
                    !is_string($checkoutToken) ||
                    !is_string($sessionCheckoutToken) ||
                    $sessionCheckoutToken === '' ||
                    !hash_equals($sessionCheckoutToken, $checkoutToken)
                ) {
                    throw new RuntimeException('This checkout request has already been used or expired. Please refresh the page.');
                }
                $csrfToken = $_POST['csrf_token'] ?? '';
                if (!is_string($csrfToken) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
                    throw new RuntimeException('Invalid request. Please refresh the page and try again.');
                }
                $recipient = trim($_POST['recipient_name'] ?? '');
                $phone = trim($_POST['phone'] ?? '');
                $address = trim($_POST['shipping_address'] ?? '');
                if ($recipient === '' || $phone === '' || $address === '') {
                    throw new RuntimeException('Please complete all checkout fields.');
                }
                if (strlen($recipient) > 255 || strlen($phone) > 50 || strlen($address) > 5000) {
                    throw new RuntimeException('One or more fields exceed the allowed length.');
                }
                $result = $checkout->placeOrder(
                    $userId,
                    $recipient,
                    $phone,
                    $address,
                    $selectedPaymentMethod
                );
                unset($_SESSION['checkout_token']);
                $_SESSION['last_order_number'] = $result['order_number'];
                if ($result['redirect_url'] !== null) {
                    header('Location: ' . $result['redirect_url']);
                    exit;
                }
                header('Location: ' . route_url('orders/success'));
                exit;
            } catch (RuntimeException $e) {
                $error = $e->getMessage();
            } catch (PDOException $e) {
                error_log('Checkout database error: ' . $e->getMessage());
                $error = 'Checkout failed. Please try again.';
            } catch (Throwable $e) {
                error_log('Checkout error: ' . $e->getMessage());
                $error = 'Checkout failed. Please try again.';
            }
        }

        $items = [];
        $total = 0.0;
        if ($successOrder === null) {
            try {
                [$items, $total] = $checkout->summary($userId);
            } catch (Throwable $e) {
                error_log('Checkout summary error: ' . $e->getMessage());
                $error = 'Unable to load your cart. Please try again.';
            }
        }

        View::render('customer/checkout', compact(
            'error',
            'successOrder',
            'selectedPaymentMethod',
            'items',
            'total'
        ));
    }

    private function ensureSessionToken(string $key): void
    {
        if (empty($_SESSION[$key])) {
            $_SESSION[$key] = bin2hex(random_bytes(32));
        }
    }
}
