<?php

namespace App\Core;

use App\Controllers\AuthController;
use App\Controllers\CartController;
use App\Controllers\CheckoutController;
use App\Controllers\CustomerDashboardController;
use App\Controllers\OrderController;
use App\Controllers\PaymentController;
use App\Controllers\ProductNotificationController;
use App\Controllers\SellerCatalogController;
use App\Controllers\SellerDashboardController;
use App\Controllers\SellerOrderController;
use App\Controllers\SellerProductController;
use App\Controllers\StoreController;

final class Router
{
    private const ROUTES = [
        '/' => [StoreController::class, 'index'],
        '/login' => [AuthController::class, 'customerLogin'],
        '/register' => [AuthController::class, 'register'],
        '/logout' => [AuthController::class, 'customerLogout'],
        '/dashboard' => [CustomerDashboardController::class, 'index'],
        '/cart' => [CartController::class, 'show'],
        '/cart/add' => [CartController::class, 'add'],
        '/cart/action' => [CartController::class, 'updateOrRemove'],
        '/checkout' => [CheckoutController::class, 'index'],
        '/orders' => [OrderController::class, 'index'],
        '/orders/details' => [OrderController::class, 'details'],
        '/orders/success' => [OrderController::class, 'success'],
        '/orders/updates' => [OrderController::class, 'pollUpdates'],
        '/notifications/dismiss' => [ProductNotificationController::class, 'dismiss'],
        '/notifications/read' => [ProductNotificationController::class, 'markRead'],
        '/payments/return' => [PaymentController::class, 'returnFromProvider'],
        '/payments/webhook' => [PaymentController::class, 'webhook'],
        '/seller/login' => [AuthController::class, 'sellerLogin'],
        '/seller/logout' => [AuthController::class, 'sellerLogout'],
        '/seller/dashboard' => [SellerDashboardController::class, 'index'],
        '/seller/products' => [SellerCatalogController::class, 'products'],
        '/seller/inventory' => [SellerCatalogController::class, 'inventory'],
        '/seller/checkouts' => [SellerCatalogController::class, 'newCheckouts'],
        '/seller/products/new' => [SellerProductController::class, 'add'],
        '/seller/products/edit' => [SellerProductController::class, 'edit'],
        '/seller/products/delete' => [SellerProductController::class, 'delete'],
        '/seller/products/images' => [SellerProductController::class, 'uploadImage'],
        '/seller/orders' => [SellerOrderController::class, 'orders'],
        '/seller/orders/list' => [SellerOrderController::class, 'sellerOrders'],
        '/seller/orders/details' => [SellerOrderController::class, 'details']
    ];

    private const LEGACY_ROUTES = [
        'index.php' => '/',
        'login.php' => '/login',
        'register.php' => '/register',
        'logout.php' => '/logout',
        'customer_dashboard.php' => '/dashboard',
        'cart.php' => '/cart',
        'add_to_cart.php' => '/cart/add',
        'cart_action.php' => '/cart/action',
        'checkout.php' => '/checkout',
        'my_orders.php' => '/orders',
        'order_details.php' => '/orders/details',
        'order_success.php' => '/orders/success',
        'order_updates_poll.php' => '/orders/updates',
        'dismiss_product_notification.php' => '/notifications/dismiss',
        'mark_product_notifications_read.php' => '/notifications/read',
        'payment_return.php' => '/payments/return',
        'paymongo_webhook.php' => '/payments/webhook',
        'seller/login.php' => '/seller/login',
        'seller/logout.php' => '/seller/logout',
        'seller/dashboard.php' => '/seller/dashboard',
        'seller/products.php' => '/seller/products',
        'seller/inventory.php' => '/seller/inventory',
        'seller/new_checkouts.php' => '/seller/checkouts',
        'seller/add_product.php' => '/seller/products/new',
        'seller/edit_product.php' => '/seller/products/edit',
        'seller/delete_product.php' => '/seller/products/delete',
        'seller/upload_product_image.php' => '/seller/products/images',
        'seller/orders.php' => '/seller/orders',
        'seller/seller_orders.php' => '/seller/orders/list',
        'seller/order_details.php' => '/seller/orders/details'
    ];

    public function dispatch(): void
    {
        $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        if (!is_string($requestPath)) {
            http_response_code(400);
            exit('Invalid request path.');
        }

        $basePath = app_base_path();
        if ($basePath !== '' && str_starts_with($requestPath, $basePath . '/')) {
            $requestPath = substr($requestPath, strlen($basePath));
        } elseif ($basePath !== '' && $requestPath === $basePath) {
            $requestPath = '/';
        }

        $requestPath = '/' . trim(rawurldecode($requestPath), '/');
        if ($requestPath === '/') {
            $legacyPath = '';
        } else {
            $legacyPath = ltrim($requestPath, '/');
        }

        if (str_starts_with($legacyPath, 'public/')) {
            $legacyPath = substr($legacyPath, strlen('public/'));
        }

        $canonicalPath = self::LEGACY_ROUTES[$legacyPath] ?? $requestPath;
        if (!isset(self::ROUTES[$canonicalPath])) {
            http_response_code(404);
            exit('Page not found.');
        }

        [$controllerClass, $action] = self::ROUTES[$canonicalPath];
        (new $controllerClass())->{$action}();
    }
}
