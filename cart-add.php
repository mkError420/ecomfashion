<?php
require_once __DIR__ . '/config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('cart.php');
}
verify_csrf();

$productId = (int)($_POST['product_id'] ?? 0);
$qty = max(1, (int)($_POST['qty'] ?? 1));
$action = $_POST['action'] ?? 'add';

$product = get_product($productId);
if (!$product) {
    flash_set('error', 'Product not found.');
    redirect('shop.php');
}
if ((int)$product['stock'] < 1) {
    flash_set('error', 'Sorry, this product is out of stock.');
    redirect('product.php?slug=' . urlencode($product['slug']));
}

$qty = min($qty, (int)$product['stock']);
cart_add($productId, $qty);
flash_set('success', '“' . $product['name'] . '” added to your cart.');

redirect($action === 'buy' ? 'checkout.php' : 'cart.php');
