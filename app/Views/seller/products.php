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
    <title>Product Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= route_url('assets/css/store-theme.css') ?>" rel="stylesheet">
    <link href="<?= route_url('assets/css/seller/seller-console.css') ?>" rel="stylesheet">
    <link href="<?= route_url('assets/css/confirm-actions.css') ?>" rel="stylesheet">
    <script src="<?= route_url('assets/js/confirm-actions.js') ?>" defer></script>
    <script src="<?= route_url('assets/js/seller/products.js') ?>" defer></script>
</head>
<body class="bg-light">
<?php $sellerActive = 'products'; require __DIR__ . '/partials/navigation.php'; ?>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Product Management</h2>
        <a href="<?= route_url('seller/products/new') ?>" class="btn btn-primary">+ Add Product</a>
    </div>
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">Product saved successfully.</div>
    <?php endif; ?>
    <?php if (isset($_GET['updated'])): ?>
        <div class="alert alert-success">Product updated successfully.</div>
    <?php endif; ?>
    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success">Product deleted successfully.</div>
    <?php endif; ?>
    <?php if (($_GET['error'] ?? '') === 'not_found'): ?>
        <div class="alert alert-warning">Product not found or already deleted.</div>
    <?php endif; ?>
    <div class="card shadow-sm">
        <div class="card-body">
            <?php if ($products): ?>
                <div class="mb-3" data-product-filters hidden>
                    <label for="product-search" class="form-label">Search products</label>
                    <input type="search" id="product-search" class="form-control"
                        placeholder="Product name or category..." autocomplete="off"
                        aria-controls="products-table" data-product-search>
                    <p class="form-text mb-0 mt-2" data-product-count aria-live="polite"></p>
                </div>
            <?php endif; ?>
            <div class="table-responsive">
                <table id="products-table" class="table table-bordered table-hover align-middle">
                    <thead class="table-dark">
                    <tr><th>ID</th><th>Product Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                    <?php if (!$products): ?>
                        <tr><td colspan="7" class="text-center">No products found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($products as $product): ?>
                            <tr data-product-row data-search-text="<?= e($product['name'] . ' ' . ($product['category_name'] ?? 'Uncategorized')) ?>">
                                <td><?= e($product['id']) ?></td>
                                <td><?= e($product['name']) ?></td>
                                <td><?= e($product['category_name'] ?? 'Uncategorized') ?></td>
                                <td>₱<?= number_format((float) $product['price'], 2) ?></td>
                                <td><?= e($product['stock']) ?></td>
                                <td><?= e($product['status']) ?></td>
                                <td>
                                    <a href="<?= route_url('seller/products/images?id=' . (int) $product['id']) ?>" class="btn btn-sm btn-success">Upload Images</a>
                                    <a href="<?= route_url('seller/products/edit?id=' . (int) $product['id']) ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <form method="POST" action="<?= route_url('seller/products/delete') ?>" class="d-inline"
                                        data-confirm-message="Are you sure you want to delete &quot;<?= e($product['name']) ?>&quot;? This action cannot be undone."
                                        data-confirm-title="Delete this product?" data-confirm-action="Delete product" data-confirm-variant="danger">
                                        <input type="hidden" name="id" value="<?= (int) $product['id'] ?>">
                                        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr data-no-product-matches hidden><td colspan="7" class="text-center">No products match your search.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>
