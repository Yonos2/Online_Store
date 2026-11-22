<?php

namespace App\Models;

use PDO;

final class StoreStats
{
    public function __construct(private PDO $pdo)
    {
    }

    public function productCount(): int
    {
        return (int) $this->pdo->query(
            'SELECT COUNT(*) FROM products'
        )->fetchColumn();
    }

    public function totalStock(): int
    {
        return (int) $this->pdo->query(
            'SELECT COALESCE(SUM(stock), 0) FROM products'
        )->fetchColumn();
    }
}
