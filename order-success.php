<?php
require_once __DIR__ . '/config/db.php';
$pageTitle = 'Order Confirmed';

$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM orders WHERE id=? LIMIT 1');
$stmt->execute([$id]);
$order = $stmt->fetch();
if (!$order) { redirect('index.php'); }

$items = db()->prepare('SELECT * FROM order_items WHERE order_id=?');
$items->execute([$id]); $items = $items->fetchAll();

$pmLabels = ['cash_on_delivery'=>'Cash on Delivery','bkash'=>'bKash','nagad'=>'Nagad','card'=>'Card'];

include __DIR__ . '/includes/header.php';
?>
<div class="container section text-center" style="max-width:720px">
  <div style="width:80px;height:80px;border-radius:50%;background:#191713;color:#f5f2ec;display:grid;place-items:center;font-size:2.4rem;margin:0 auto 20px">✓</div>
  <h1 class="section-title">Thank you for your order!</h1>
  <p class="muted">Your order has been placed successfully. We'll contact you shortly to confirm delivery.</p>

  <div class="form-card text-left" style="max-width:none;margin-top:26px">
    <div class="summary-row"><span>Order number</span><strong>#<?= str_pad((string)$order['id'],5,'0',STR_PAD_LEFT) ?></strong></div>
    <div class="summary-row"><span>Date</span><span><?= e(date('d M Y, h:i A', strtotime($order['created_at']))) ?></span></div>
    <div class="summary-row"><span>Payment</span><span><?= e($pmLabels[$order['payment_method']] ?? $order['payment_method']) ?></span></div>
    <div class="summary-row"><span>Deliver to</span><span><?= e($order['address']) ?>, <?= e($order['city']) ?></span></div>
    <hr style="border:0;border-top:1px solid var(--line);margin:14px 0">
    <?php foreach ($items as $it): ?>
      <div class="summary-row"><span><?= e($it['product_name']) ?> × <?= (int)$it['quantity'] ?></span><span><?= price((float)$it['price']*$it['quantity']) ?></span></div>
    <?php endforeach; ?>
    <div class="summary-row"><span>Shipping</span><span><?= (float)$order['shipping']==0?'Free':price($order['shipping']) ?></span></div>
    <div class="summary-row total"><span>Total</span><span><?= price($order['total']) ?></span></div>
  </div>

  <div style="margin-top:24px;display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
    <a class="btn btn-primary" href="<?= BASE_URL ?>/track-order.php?id=<?= (int)$order['id'] ?>">Track Order</a>
    <a class="btn btn-outline" href="<?= BASE_URL ?>/shop.php">Continue Shopping</a>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
