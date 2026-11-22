<?php

namespace App\Controllers;

use App\Core\View;
use App\Models\SellerCatalog;
use Database;

final class SellerCatalogController
{
    public function products(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $this->ensureCsrfToken();
        $catalog = new SellerCatalog((new Database())->connect());
        $products = $catalog->products();
        View::render('seller/products', compact('products'));
    }

    public function inventory(): void
    {
        $this->requireSeller();
        $catalog = new SellerCatalog((new Database())->connect());
        $products = $catalog->inventoryProducts();
        $totalUnits = array_sum(array_map(
            static fn (array $product): int => (int) $product['stock'],
            $products
        ));
        $outOfStockCount = count(array_filter(
            $products,
            static fn (array $product): bool => (int) $product['stock'] === 0
        ));
        $newOrderCount = $catalog->pendingOrderCount();
        $newOrders = $catalog->pendingOrders(5);
        View::render('seller/inventory', compact(
            'products',
            'totalUnits',
            'outOfStockCount',
            'newOrderCount',
            'newOrders'
        ));
    }

    public function newCheckouts(): void
    {
        $this->requireSeller();
        $newCheckouts = (new SellerCatalog((new Database())->connect()))
            ->pendingOrders();
        View::render('seller/new_checkouts', compact('newCheckouts'));
    }

    private function requireSeller(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (
            !isset($_SESSION['user_id']) ||
            ($_SESSION['role'] ?? '') !== 'seller'
        ) {
            header('Location: ' . route_url('seller/login'));
            exit;
        }
    }

    private function ensureCsrfToken(): void
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }
}
