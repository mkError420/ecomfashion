<?php
require_once __DIR__ . '/config/db.php';
$pageTitle = 'Shopping Cart';

// Handle update / remove
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if (isset($_POST['update_cart'])) {
        foreach ((array)($_POST['qty'] ?? []) as $pid => $qty) {
            cart_update((int)$pid, (int)$qty);
        }
        flash_set('success', 'Cart updated.');
    } elseif (isset($_POST['remove'])) {
        cart_remove((int)$_POST['remove']);
        flash_set('success', 'Item removed from cart.');
    } elseif (isset($_POST['clear'])) {
        cart_clear();
        flash_set('success', 'Your cart is now empty.');
    }
    redirect('cart.php');
}

$details = cart_details();
$lines = $details['lines'];
$subtotal = $details['subtotal'];
$freeOver = (float)setting('free_shipping_over', '5000');
$ship = ($subtotal >= $freeOver || $subtotal == 0) ? 0.0 : shipping_cost();
$total = $subtotal + $ship;

include __DIR__ . '/includes/header.php';
?>

<div class="page-head">
  <div class="container"><h1>Shopping Cart</h1><p><?= cart_count() ?> item(s) in your cart</p></div>
</div>

<div class="container section" style="padding-top:30px">
<?php if (empty($lines)): ?>
  <div class="empty">
    <svg viewBox="0 0 24 24" fill="none" stroke-width="1.5"><path d="M6 6h15l-1.5 9h-12z"/><circle cx="9" cy="20" r="1.4"/><circle cx="18" cy="20" r="1.4"/><path d="M6 6L5 3H2"/></svg>
    <h3>Your cart is empty</h3>
    <p>Looks like you haven't added anything yet.</p>
    <a class="btn btn-primary" style="margin-top:16px" href="<?= BASE_URL ?>/shop.php">Start Shopping</a>
  </div>
<?php else: ?>
  <div class="cart-layout">
    <form method="post">
      <?= csrf_field() ?>
      <table class="cart-table">
        <thead>
          <tr><th>Product</th><th>Price</th><th>Quantity</th><th>Total</th><th></th></tr>
        </thead>
        <tbody>
        <?php foreach ($lines as $line): $p = $line['product']; ?>
          <tr>
            <td data-label="Product">
              <div class="cart-item">
                <img src="<?= e(product_image_url($p['image'])) ?>" alt="<?= e($p['name']) ?>">
                <div>
                  <a href="<?= BASE_URL ?>/product.php?slug=<?= e($p['slug']) ?>" style="font-weight:600"><?= e($p['name']) ?></a>
                  <div class="muted" style="font-size:.82rem"><?= e($p['category_name'] ?? '') ?></div>
                </div>
              </div>
            </td>
            <td data-label="Price"><?= price($line['unit']) ?></td>
            <td data-label="Quantity">
              <input type="number" name="qty[<?= (int)$p['id'] ?>]" value="<?= (int)$line['qty'] ?>" min="1" max="<?= (int)$p['stock'] ?>" style="width:70px;padding:8px;border:1px solid var(--line);border-radius:8px">
            </td>
            <td data-label="Total"><strong><?= price($line['total']) ?></strong></td>
            <td data-label="">
              <button type="submit" name="remove" value="<?= (int)$p['id'] ?>" class="icon-btn" aria-label="Remove" title="Remove" style="width:36px;height:36px">
                <svg viewBox="0 0 24 24" style="width:16px;height:16px"><path d="M4 7h16M9 7V5h6v2M6 7l1 13h10l1-13"/></svg>
              </button>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <div style="display:flex;gap:10px;margin-top:20px;flex-wrap:wrap">
        <button type="submit" name="update_cart" value="1" class="btn btn-ghost">Update Cart</button>
        <button type="submit" name="clear" value="1" class="btn btn-ghost">Clear Cart</button>
        <a href="<?= BASE_URL ?>/shop.php" class="btn btn-outline">Continue Shopping</a>
      </div>
    </form>

    <aside class="cart-summary">
      <h3 style="font-family:'Hind Siliguri',sans-serif;margin-bottom:16px">Order Summary</h3>
      <div class="summary-row"><span>Subtotal</span><span><?= price($subtotal) ?></span></div>
      <div class="summary-row"><span>Shipping</span><span><?= $ship==0 ? 'Free' : price($ship) ?></span></div>
      <?php if ($ship>0 && $freeOver>0): ?>
        <div class="muted" style="font-size:.8rem">Add <?= price($freeOver-$subtotal) ?> more for free shipping.</div>
      <?php endif; ?>
      <div class="summary-row total"><span>Total</span><span><?= price($total) ?></span></div>
      <a href="<?= BASE_URL ?>/checkout.php" class="btn btn-primary btn-block" style="margin-top:16px">Proceed to Checkout</a>
      <p class="muted text-center" style="font-size:.8rem;margin-top:12px">Cash on Delivery • bKash • Nagad</p>
    </aside>
  </div>
<?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
