<?php

namespace App\Models;

use PDO;

final class Product
{
    public function __construct(private PDO $pdo)
    {
    }

    public function categories(): array
    {
        return $this->pdo->query(
            'SELECT id, name FROM public.categories ORDER BY name'
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function activeCatalog(): array
    {
        return $this->pdo->query("
            SELECT p.id, p.name, p.description, p.price, p.stock, pi.image_path
            FROM products p
            LEFT JOIN product_images pi
                ON pi.product_id = p.id AND pi.is_primary = TRUE
            WHERE p.status = 'active'
            ORDER BY p.created_at DESC, p.id DESC
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $id): array|false
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, category_id, name, description, price, stock, status
             FROM public.products
             WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(array $data): void
    {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO public.products
                    (category_id, name, description, price, stock, status)
                VALUES (:category_id, :name, :description, :price, :stock, 'active')
            ");
            $stmt->execute($data);
            \recordProductNotification($this->pdo, 'added', $data['name']);
            $this->pdo->commit();
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    public function update(int $id, array $data): bool
    {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("
                UPDATE public.products
                SET category_id = :category_id,
                    name = :name,
                    description = :description,
                    price = :price,
                    stock = :stock,
                    status = :status,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id
            ");
            $stmt->execute($data + ['id' => $id]);
            if ($stmt->rowCount() === 0) {
                $check = $this->pdo->prepare(
                    'SELECT id FROM public.products WHERE id = :id'
                );
                $check->execute(['id' => $id]);
                if (!$check->fetchColumn()) {
                    $this->pdo->rollBack();
                    return false;
                }
            }
            \recordProductNotification($this->pdo, 'updated', $data['name']);
            $this->pdo->commit();
            return true;
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    public function delete(int $id): bool
    {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare(
                'SELECT name FROM public.products WHERE id = :id FOR UPDATE'
            );
            $stmt->execute(['id' => $id]);
            $name = $stmt->fetchColumn();
            if ($name === false) {
                $this->pdo->rollBack();
                return false;
            }

            $stmt = $this->pdo->prepare(
                'DELETE FROM public.products WHERE id = :id'
            );
            $stmt->execute(['id' => $id]);
            if ($stmt->rowCount() === 0) {
                $this->pdo->rollBack();
                return false;
            }
            \recordProductNotification($this->pdo, 'deleted', $name);
            $this->pdo->commit();
            return true;
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    public function images(int $productId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, image_path, is_primary, created_at
             FROM public.product_images
             WHERE product_id = :product_id
             ORDER BY is_primary DESC, id ASC'
        );
        $stmt->execute([':product_id' => $productId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addImage(int $productId, string $path, bool $isPrimary, string $name): void
    {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO public.product_images
                    (product_id, image_path, is_primary)
                 VALUES (:product_id, :image_path, :is_primary)'
            );
            $stmt->execute([
                ':product_id' => $productId,
                ':image_path' => $path,
                ':is_primary' => $isPrimary
            ]);
            \recordProductNotification($this->pdo, 'updated', $name);
            $this->pdo->commit();
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    public function imageCount(int $productId): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM public.product_images WHERE product_id = :product_id'
        );
        $stmt->execute([':product_id' => $productId]);

        return (int) $stmt->fetchColumn();
    }
}
