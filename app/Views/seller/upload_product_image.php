<?php

if (!function_exists('e')) {
    function e($value): string
    {
        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Upload Product Image</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= route_url('assets/css/store-theme.css') ?>" rel="stylesheet">
    <link href="<?= route_url('assets/css/seller/seller-console.css') ?>" rel="stylesheet">
    <link href="<?= route_url('assets/css/seller/upload_product_image.css') ?>" rel="stylesheet">
    <script src="<?= route_url('assets/js/seller/upload_product_image.js') ?>" defer></script>
</head>
<body class="bg-light">
<?php $sellerActive = 'products'; require __DIR__ . '/partials/navigation.php'; ?>
<div class="container py-4">
    <a href="<?= route_url('seller/products') ?>" class="btn btn-secondary mb-3">Back to Products</a>
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h2>Upload Product Image</h2>
            <p><strong>Product:</strong> <?= e($product['name']) ?></p>
            <?php if ($success !== ''): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
            <?php if ($error !== ''): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
            <p class="text-muted">JPG, PNG, or WebP only. Maximum 5 MB per image, up to 6 images per product.</p>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                <label for="product_image" class="form-label">Choose Image</label>
                <input type="file" name="product_image" id="product_image" class="form-control mb-3"
                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" required>
                <div class="mt-3" data-image-preview hidden>
                    <p class="form-label mb-2">Selected image preview</p>
                    <img class="img-thumbnail upload-preview-image" data-preview-image alt="Preview of the selected product image">
                    <p class="form-text mb-0 mt-2" data-preview-file-info></p>
                </div>
                <button type="submit" class="btn btn-primary">Upload Image</button>
            </form>
        </div>
    </div>
    <h4>Current Product Images</h4>
    <div class="row g-3">
        <?php foreach ($images as $image): ?>
            <div class="col-6 col-md-4 col-lg-3">
                <div class="card h-100">
                    <img src="<?= e(route_url(ltrim($image['image_path'], '/'))) ?>" alt="Product image" class="card-img-top product-image-thumbnail">
                    <div class="card-body">
                        <?php if ($image['is_primary']): ?>
                            <span class="badge text-bg-success">Primary Image</span>
                        <?php else: ?>
                            <span class="badge text-bg-secondary">Product Image</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (!$images): ?><p>No images uploaded yet.</p><?php endif; ?>
    </div>
</div>
</body>
</html>
