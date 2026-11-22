<?php

namespace App\Controllers;

use App\Core\View;
use App\Models\StoreStats;
use Database;

final class SellerDashboardController
{
    public function index(): void
    {
        session_start();
        if (
            !isset($_SESSION['user_id']) ||
            ($_SESSION['role'] ?? '') !== 'seller'
        ) {
            header('Location: ' . route_url('seller/login'));
            exit;
        }

        $stats = new StoreStats((new Database())->connect());
        $productCount = $stats->productCount();
        $totalStock = $stats->totalStock();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        View::render('seller/dashboard', compact('productCount', 'totalStock'));
    }
}
