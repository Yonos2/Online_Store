<?php

namespace App\Controllers;

use App\Core\View;
use App\Models\Product;
use Database;
use PDOException;
use Throwable;

final class SellerProductController
{
    public function add(): void
    {
        $this->authorize();
        $productModel = new Product((new Database())->connect());
        $categories = $productModel->categories();
        $this->ensureCsrf();
        $error = '';

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $data = $this->productInput(false);
            $error = $this->validate($data, false);
            if ($error === '' && !$this->validCsrf()) {
                $error = 'Invalid form submission. Please refresh and try again.';
            }
            if ($error === '') {
                try {
                    $productModel->create($this->dbData($data, false));
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    header('Location: ' . route_url('seller/products?success=1'));
                    exit;
                } catch (PDOException $e) {
                    error_log('Add product error: ' . $e->getMessage());
                    $error = 'Unable to save the product. Please check the form and database schema.';
                }
            }
        } else {
            $data = ['name' => '', 'description' => '', 'category_id' => '', 'price' => '', 'stock' => '0'];
        }

        View::render('seller/product_form', [
            'product' => $data,
            'categories' => $categories,
            'error' => $error,
            'editing' => false
        ]);
    }

    public function edit(): void
    {
        $this->authorize();
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$id || $id < 1) {
            http_response_code(400);
            exit('Invalid product ID.');
        }
        $productModel = new Product((new Database())->connect());
        $product = $productModel->find($id);
        if (!$product) {
            http_response_code(404);
            exit('Product not found.');
        }
        $categories = $productModel->categories();
        $this->ensureCsrf();
        $error = '';

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $data = $this->productInput(true);
            $error = $this->validate($data, true);
            if ($error === '' && !$this->validCsrf()) {
                $error = 'Invalid form submission. Please refresh and try again.';
            }
            if ($error === '') {
                try {
                    if (!$productModel->update($id, $this->dbData($data, true))) {
                        http_response_code(404);
                        exit('Product not found.');
                    }
                    header('Location: ' . route_url('seller/products?updated=1'));
                    exit;
                } catch (PDOException $e) {
                    error_log('Edit product error: ' . $e->getMessage());
                    $error = 'Unable to update the product. Check your database schema.';
                }
            }
            $product = array_merge($product, $data);
        }

        View::render('seller/product_form', [
            'product' => $product,
            'categories' => $categories,
            'error' => $error,
            'editing' => true,
            'id' => $id
        ]);
    }

    public function delete(): void
    {
        $this->authorize();
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            exit('Method not allowed.');
        }
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if (!$id || $id < 1) {
            http_response_code(400);
            exit('Invalid product ID.');
        }
        if (!$this->validCsrf()) {
            http_response_code(403);
            exit('Invalid form submission. Please refresh and try again.');
        }

        try {
            if (!(new Product((new Database())->connect()))->delete($id)) {
                header('Location: ' . route_url('seller/products?error=not_found'));
                exit;
            }
            header('Location: ' . route_url('seller/products?deleted=1'));
            exit;
        } catch (PDOException $e) {
            error_log('Delete product error: ' . $e->getMessage());
            http_response_code(500);
            exit('Unable to delete the product. It may be referenced by existing orders.');
        }
    }

    public function uploadImage(): void
    {
        $this->authorize();
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$id || $id < 1) {
            http_response_code(400);
            exit('Invalid product ID.');
        }
        $model = new Product((new Database())->connect());
        $product = $model->find($id);
        if (!$product) {
            http_response_code(404);
            exit('Product not found.');
        }

        $this->ensureCsrf();
        $error = '';
        $success = isset($_GET['uploaded'])
            ? 'Product image uploaded successfully.'
            : '';
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            if (!$this->validCsrf()) {
                $error = 'Invalid request. Refresh the page and try again.';
            } elseif (!isset($_FILES['product_image']) || !is_array($_FILES['product_image'])) {
                $error = 'Please select an image.';
            } else {
                $file = $_FILES['product_image'];
                $error = $this->validateImage($file);
                if ($error === '') {
                    try {
                        $count = $model->imageCount($id);
                        $directory = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR
                            . 'uploads' . DIRECTORY_SEPARATOR . 'products' . DIRECTORY_SEPARATOR;
                        if ($count >= 6) {
                            $error = 'A product can have up to 6 images.';
                        } elseif (!is_dir($directory) || !is_writable($directory)) {
                            $error = 'The upload folder is missing or not writable.';
                        } else {
                            $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
                            $extension = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime];
                            $filename = bin2hex(random_bytes(16)) . '.' . $extension;
                            $destination = $directory . $filename;
                            if (!move_uploaded_file($file['tmp_name'], $destination)) {
                                $error = 'Unable to save the image. Check folder permissions.';
                            } else {
                                try {
                                    $model->addImage(
                                        $id,
                                        'uploads/products/' . $filename,
                                        $count === 0,
                                        $product['name']
                                    );
                                    header('Location: ' . route_url(
                                        'seller/products/images?id=' . $id . '&uploaded=1'
                                    ));
                                    exit;
                                } catch (Throwable $e) {
                                    if (is_file($destination)) {
                                        unlink($destination);
                                    }
                                    throw $e;
                                }
                            }
                        }
                    } catch (Throwable $e) {
                        error_log('Product image upload error: ' . $e->getMessage());
                        $error = 'A database or file error occurred. Please check the table structure and PHP error log.';
                    }
                }
            }
        }

        $images = $model->images($id);
        View::render('seller/upload_product_image', compact(
            'product',
            'images',
            'error',
            'success',
            'id'
        ));
    }

    private function validateImage(array $file): string
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return match ($file['error']) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'The image exceeds the upload size limit.',
                UPLOAD_ERR_NO_FILE => 'Please select an image.',
                default => 'The image upload failed. Please try again.'
            };
        }
        if (!is_uploaded_file($file['tmp_name'])) {
            return 'Invalid uploaded file.';
        }
        if ($file['size'] <= 0 || $file['size'] > 5 * 1024 * 1024) {
            return 'The image must be no larger than 5 MB.';
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        $image = @getimagesize($file['tmp_name']);
        if (
            !in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true) ||
            $image === false ||
            ($image['mime'] ?? '') !== $mime
        ) {
            return 'Only valid JPG, PNG, or WebP images are allowed.';
        }
        if ($image[0] > 8000 || $image[1] > 8000) {
            return 'The image dimensions are too large.';
        }
        return '';
    }

    private function productInput(bool $editing): array
    {
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'category_id' => $_POST['category_id'] ?? '',
            'price' => $_POST['price'] ?? '',
            'stock' => $_POST['stock'] ?? ''
        ];
        if ($editing) {
            $data['status'] = $_POST['status'] ?? '';
        }
        return $data;
    }

    private function validate(array $data, bool $editing): string
    {
        if ($data['name'] === '') {
            return 'Product name is required.';
        }
        if (!is_numeric($data['price']) || (float) $data['price'] < 0) {
            return 'Enter a valid, non-negative price.';
        }
        if (filter_var($data['stock'], FILTER_VALIDATE_INT) === false || (int) $data['stock'] < 0) {
            return 'Stock must be a non-negative whole number.';
        }
        if ($editing && !in_array($data['status'], ['active', 'inactive'], true)) {
            return 'Select a valid product status.';
        }
        if ($data['category_id'] !== '' && filter_var($data['category_id'], FILTER_VALIDATE_INT) === false) {
            return 'Select a valid category.';
        }
        return '';
    }

    private function dbData(array $data, bool $editing): array
    {
        $result = [
            'category_id' => $data['category_id'] === '' ? null : (int) $data['category_id'],
            'name' => $data['name'],
            'description' => $data['description'],
            'price' => $data['price'],
            'stock' => (int) $data['stock']
        ];
        if ($editing) {
            $result['status'] = $data['status'];
        }
        return $result;
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

    private function validCsrf(): bool
    {
        $token = $_POST['csrf_token'] ?? '';
        return is_string($token)
            && is_string($_SESSION['csrf_token'] ?? null)
            && $_SESSION['csrf_token'] !== ''
            && hash_equals($_SESSION['csrf_token'], $token);
    }
}
