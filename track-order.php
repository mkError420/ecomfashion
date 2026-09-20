<?php
require_once __DIR__ . '/config/db.php';
$pageTitle = 'Track Order';

$order = null; $items = []; $error = '';
$id = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = (int)($_POST['order_id'] ?? 0);
    $phone = trim($_POST['phone'] ?? '');
    if ($id && $phone !== '') {
        $stmt = db()->prepare('SELECT * FROM orders WHERE id=? AND phone=? LIMIT 1');
        $stmt->execute([$id, $phone]);
        $order = $stmt->fetch();
        if (!$order) $error = 'No order found with that ID and phone number.';
    } else { $error = 'Enter both order ID and phone number.'; }
} elseif ($id) {
    $stmt = db()->prepare('SELECT * FROM orders WHERE id=? LIMIT 1');
    $stmt->execute([$id]); $order = $stmt->fetch();
    if (!$order) $error = 'Order not found.';
}

if ($order) {
    $it = db()->prepare('SELECT * FROM order_items WHERE order_id=?');
    $it->execute([$order['id']]); $items = $it->fetchAll();
}

$steps = ['pending','processing','shipped','delivered'];
$curStep = $order ? array_search($order['status'], $steps, true) : false;
$cancelled = $order && $order['status'] === 'cancelled';

include __DIR__ . '/includes/header.php';
?>
<div class="page-head"><div class="container"><h1>Track Your Order</h1><p>Enter your order ID and phone number</p></div></div>

<div class="container section" style="padding-top:30px">
  <?php if (!$order): ?>
    <div class="form-card">
      <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
      <form method="post">
        <?= csrf_field() ?>
        <div class="form-grid">
          <div class="field"><label>Order ID *</label><input type="number" name="order_id" value="<?= $id?:'' ?>" placeholder="e.g. 1" required></div>
          <div class="field"><label>Phone number *</label><input name="phone" placeholder="01XXXXXXXXX" required></div>
        </div>
        <button class="btn btn-primary btn-block" type="submit">Track Order</button>
      </form>
    </div>
  <?php else: ?>
    <div class="cart-layout" style="grid-template-columns:1fr 340px">
      <div>
        <h2 style="font-family:'Hind Siliguri',sans-serif">Order #<?= str_pad((string)$order['id'],5,'0',STR_PAD_LEFT) ?></h2>
        <p class="muted">Placed on <?= e(date('d M Y, h:i A', strtotime($order['created_at']))) ?></p>

        <?php if ($cancelled): ?>
          <div class="alert alert-error" style="margin-top:16px">This order has been cancelled.</div>
        <?php else: ?>
          <div style="display:flex;justify-content:space-between;margin:30px 0;position:relative">
            <div style="position:absolute;top:16px;left:5%;right:5%;height:3px;background:var(--line);z-index:0"></div>
            <div style="position:absolute;top:16px;left:5%;height:3px;background:var(--crimson);z-index:1;width:<?= $curStep===false?0:($curStep/(count($steps)-1))*90 ?>%"></div>
            <?php foreach ($steps as $i=>$s): $done = $curStep!==false && $i<=$curStep; ?>
              <div style="text-align:center;z-index:2;flex:1">
                <div style="width:34px;height:34px;border-radius:50%;margin:0 auto 8px;display:grid;place-items:center;background:<?= $done?'var(--crimson)':'#fff' ?>;color:<?= $done?'#fff':'var(--muted)' ?>;border:2px solid <?= $done?'var(--crimson)':'var(--line)' ?>;font-size:.85rem"><?= $done?'✓':($i+1) ?></div>
                <div style="font-size:.8rem;font-weight:600;text-transform:capitalize"><?= e($s) ?></div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <table class="cart-table" style="margin-top:10px">
          <thead><tr><th>Item</th><th>Qty</th><th>Total</th></tr></thead>
          <tbody>
          <?php foreach ($items as $it): ?>
            <tr><td data-label="Item"><?= e($it['product_name']) ?></td><td data-label="Qty"><?= (int)$it['quantity'] ?></td><td data-label="Total"><?= price((float)$it['price']*$it['quantity']) ?></td></tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <aside class="cart-summary">
        <h3 style="font-family:'Hind Siliguri',sans-serif;margin-bottom:14px">Summary</h3>
        <div class="summary-row"><span>Status</span><strong style="text-transform:capitalize"><?= e($order['status']) ?></strong></div>
        <div class="summary-row"><span>Payment</span><span style="text-transform:capitalize"><?= e(str_replace('_',' ',$order['payment_method'])) ?></span></div>
        <div class="summary-row"><span>Subtotal</span><span><?= price($order['subtotal']) ?></span></div>
        <div class="summary-row"><span>Shipping</span><span><?= (float)$order['shipping']==0?'Free':price($order['shipping']) ?></span></div>
        <div class="summary-row total"><span>Total</span><span><?= price($order['total']) ?></span></div>
        <hr style="border:0;border-top:1px solid var(--line);margin:14px 0">
        <div class="muted" style="font-size:.88rem">
          <strong style="color:var(--ink)"><?= e($order['name']) ?></strong><br>
          <?= e($order['address']) ?><br><?= e($order['city']) ?><br><?= e($order['phone']) ?>
        </div>
      </aside>
    </div>
  <?php endif; ?>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
