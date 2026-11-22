<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $editing ? 'Edit Product' : 'Add Product' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= route_url('assets/css/store-theme.css') ?>" rel="stylesheet">
    <link href="<?= route_url('assets/css/seller/seller-console.css') ?>" rel="stylesheet">
    <link href="<?= route_url('assets/css/confirm-actions.css') ?>" rel="stylesheet">
    <link href="<?= route_url('assets/css/seller/add_product.css') ?>" rel="stylesheet">
    <script src="<?= route_url('assets/js/confirm-actions.js') ?>" defer></script>
</head>
<body class="bg-light">
<?php $sellerActive = 'products'; require __DIR__ . '/partials/navigation.php'; ?>
<div class="container py-5 product-form-container">
    <h2 class="mb-4"><?= $editing ? 'Edit Product' : 'Add New Product' ?></h2>
    <?php if ($error !== ''): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <div class="card shadow-sm">
        <div class="card-body p-4">
            <form method="POST" <?= $editing ? 'data-confirm-message="Save the changes to this product?" data-confirm-title="Update this product?" data-confirm-action="Save changes"' : 'action=""' ?>>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                <div class="mb-3">
                    <label class="form-label" for="name">Product Name</label>
                    <input class="form-control" type="text" id="name" name="name" maxlength="200" required value="<?= htmlspecialchars($product['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="category_id">Category</label>
                    <select class="form-select" id="category_id" name="category_id">
                        <option value="">Select category (optional)</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= (int) $category['id'] ?>" <?= (string) ($product['category_id'] ?? '') === (string) $category['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="description">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="4"><?= htmlspecialchars($product['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="price">Price (₱)</label>
                        <input class="form-control" type="number" id="price" name="price" min="0" step="0.01" required value="<?= htmlspecialchars((string) ($product['price'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="stock"><?= $editing ? 'Stock' : 'Stock Quantity' ?></label>
                        <input class="form-control" type="number" id="stock" name="stock" min="0" step="1" required value="<?= htmlspecialchars((string) ($product['stock'] ?? '0'), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                </div>
                <?php if ($editing): ?>
                    <div class="mb-4">
                        <label class="form-label" for="status">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="active" <?= ($product['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= ($product['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                <?php endif; ?>
                <button class="btn btn-primary" type="submit"><?= $editing ? 'Save Changes' : 'Save Product' ?></button>
                <a href="<?= route_url('seller/products') ?>" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>
