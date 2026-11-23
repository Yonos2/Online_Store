<?php

namespace App\Controllers;

use App\Core\View;

final class StoreController
{
    public function index(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $isCustomerLoggedIn = isset($_SESSION['user_id'])
            && ($_SESSION['role'] ?? '') === 'customer';

        if ($isCustomerLoggedIn) {
            header('Location: ' . route_url('dashboard'));
            exit;
        }

        View::render('customer/index', compact('isCustomerLoggedIn'));
    }
}
