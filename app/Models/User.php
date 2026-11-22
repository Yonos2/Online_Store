<?php

namespace App\Models;

use PDO;

final class User
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findByEmail(string $email): array|false
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, full_name, email, password_hash, role
             FROM users
             WHERE email = :email
             LIMIT 1'
        );
        $stmt->execute(['email' => $email]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function emailExists(string $email): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT id FROM users WHERE email = :email LIMIT 1'
        );
        $stmt->execute(['email' => $email]);

        return (bool) $stmt->fetch();
    }

    public function createCustomer(
        string $fullName,
        string $email,
        ?string $phone,
        string $passwordHash
    ): void {
        $stmt = $this->pdo->prepare("
            INSERT INTO users
                (full_name, email, phone, password_hash, role)
            VALUES
                (:full_name, :email, :phone, :password_hash, 'customer')
        ");
        $stmt->execute([
            'full_name' => $fullName,
            'email' => $email,
            'phone' => $phone,
            'password_hash' => $passwordHash
        ]);
    }
}
