<?php

namespace App\Controllers;

use App\Core\View;
use App\Models\Cart;
use Database;
use PDOException;
use Throwable;

final class CartController
{
    public function show(): void
    {
        session_start();

        $database = new Database();
        $cart = new Cart($database->connect());

        if (!$this->isCustomer()) {
            header('Location: ' . route_url('login'));
            exit;
        }

        $cartItems = $cart->itemsForCustomer((int) $_SESSION['user_id']);
        $total = array_sum(array_map(
            static fn (array $item): float => (float) $item['subtotal'],
            $cartItems
        ));

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        View::render('customer/cart', [
            'cartItems' => $cartItems,
            'total' => $total
        ]);
    }

    public function add(): void
    {
        session_start();

        if (!$this->isCustomer()) {
            header('Location: ' . route_url('login'));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . route_url());
            exit;
        }

        $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
        $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
        if (!$productId || !$quantity || $quantity < 1) {
            header('Location: ' . route_url('?error=invalid_input'));
            exit;
        }

        try {
            $cart = new Cart((new Database())->connect());
            $result = $cart->addProduct(
                (int) $_SESSION['user_id'],
                $productId,
                $quantity
            );
            if ($result === 'added') {
                header('Location: ' . route_url('cart?added=1'));
                exit;
            }

            header('Location: ' . route_url('?error=' . rawurlencode($result)));
            exit;
        } catch (PDOException $e) {
            error_log('Add to cart error: ' . $e->getMessage());

            http_response_code(500);
            echo 'Something went wrong while adding the product to your cart. Please try again.';
            exit;
        }
    }

    public function updateOrRemove(): void
    {
        session_start();

        if (!$this->isCustomer()) {
            header('Location: ' . route_url('login'));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . route_url('cart'));
            exit;
        }

        $sessionToken = $_SESSION['csrf_token'] ?? '';
        $formToken = $_POST['csrf_token'] ?? '';
        if (
            !is_string($sessionToken) ||
            !is_string($formToken) ||
            $sessionToken === '' ||
            !hash_equals($sessionToken, $formToken)
        ) {
            header('Location: ' . route_url('cart?error=invalid_request'));
            exit;
        }

        $action = $_POST['action'] ?? '';
        $cartItemId = filter_var(
            $_POST['cart_item_id'] ?? null,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );
        if (!$cartItemId || !in_array($action, ['update', 'remove'], true)) {
            header('Location: ' . route_url('cart?error=invalid_request'));
            exit;
        }

        try {
            $cart = new Cart((new Database())->connect());
            $userId = (int) $_SESSION['user_id'];
            $stock = $cart->stockForItem($cartItemId, $userId);
            if ($stock === false) {
                header('Location: ' . route_url('cart?error=item_not_found'));
                exit;
            }

            if ($action === 'remove') {
                $cart->removeItem($cartItemId, $userId);
                header('Location: ' . route_url('cart?removed=1'));
                exit;
            }

            $quantity = filter_var(
                $_POST['quantity'] ?? null,
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1]]
            );
            if ($quantity === false || $quantity === null) {
                header('Location: ' . route_url('cart?error=invalid_quantity'));
                exit;
            }

            if ($stock < 1 || $quantity > $stock) {
                header('Location: ' . route_url('cart?error=insufficient_stock'));
                exit;
            }

            $cart->updateQuantity($cartItemId, $userId, $quantity);
            header('Location: ' . route_url('cart?updated=1'));
            exit;
        } catch (PDOException $e) {
            error_log('Cart action database error: ' . $e->getMessage());
            header('Location: ' . route_url('cart?error=server'));
            exit;
        } catch (Throwable $e) {
            error_log('Cart action error: ' . $e->getMessage());
            header('Location: ' . route_url('cart?error=server'));
            exit;
        }
    }

    private function isCustomer(): bool
    {
        return isset($_SESSION['user_id'])
            && ($_SESSION['role'] ?? '') === 'customer';
    }
}
