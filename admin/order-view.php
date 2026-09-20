<?php
$activeKey = 'orders';
require_once __DIR__ . '/../config/db.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM orders WHERE id=? LIMIT 1');
$stmt->execute([$id]);
$order = $stmt->fetch();
if (!$order) { flash_set('error','Order not found.'); redirect('admin/orders.php'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if (isset($_POST['status'])) {
        $new = $_POST['status'];
        if (in_array($new, ['pending','processing','shipped','delivered','cancelled'], true)) {
            db()->prepare('UPDATE orders SET status=? WHERE id=?')->execute([$new, $id]);
            flash_set('success','Order status updated to '.$new.'.');
        }
    }
    redirect('admin/order-view.php?id='.$id);
}

$items = db()->prepare('SELECT * FROM order_items WHERE order_id=?');
$items->execute([$id]); $items = $items->fetchAll();
$adminTitle = 'Order #'.str_pad((string)$order['id'],5,'0',STR_PAD_LEFT);

require_once __DIR__ . '/includes/header.php';
$pmLabels = ['cash_on_delivery'=>'Cash on Delivery','bkash'=>'bKash','nagad'=>'Nagad','card'=>'Card'];
?>

<div class="panel">
  <div class="panel-head"><h2>Order #<?= str_pad((string)$order['id'],5,'0',STR_PAD_LEFT) ?></h2><a class="btn btn-ghost btn-sm" href="<?= BASE_URL ?>/admin/orders.php">← Back to orders</a></div>
</div>

<div class="dash-grid">
  <div>
    <div class="panel">
      <div class="panel-head"><h2>Items</h2></div>
      <div style="overflow-x:auto">
      <table class="data">
        <thead><tr><th>Product</th><th>Price</th><th>Qty</th><th>Total</th></tr></thead>
        <tbody>
        <?php foreach ($items as $it): ?>
          <tr>
            <td data-label="Product"><?= e($it['product_name']) ?></td>
            <td data-label="Price"><?= price($it['price']) ?></td>
            <td data-label="Qty"><?= (int)$it['quantity'] ?></td>
            <td data-label="Total"><strong><?= price((float)$it['price']*$it['quantity']) ?></strong></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr><td colspan="3" class="text-right muted">Subtotal</td><td><strong><?= price($order['subtotal']) ?></strong></td></tr>
          <tr><td colspan="3" class="text-right muted">Shipping</td><td><strong><?= (float)$order['shipping']==0?'Free':price($order['shipping']) ?></strong></td></tr>
          <tr><td colspan="3" class="text-right"><strong>Total</strong></td><td><strong style="color:var(--crimson);font-size:1.1rem"><?= price($order['total']) ?></strong></td></tr>
        </tfoot>
      </table>
      </div>
    </div>
    <?php if ($order['note']): ?>
      <div class="panel"><div class="panel-head"><h2>Order Note</h2></div><div class="panel-body"><?= nl2br(e($order['note'])) ?></div></div>
    <?php endif; ?>
  </div>

  <div>
    <div class="panel">
      <div class="panel-head"><h2>Update Status</h2></div>
      <div class="panel-body">
        <form method="post">
          <?= csrf_field() ?>
          <div class="field"><label>Current: <span class="pill pill-<?= e($order['status']) ?>"><?= e($order['status']) ?></span></label>
            <select name="status">
              <?php foreach (['pending','processing','shipped','delivered','cancelled'] as $s): ?>
                <option value="<?= $s ?>" <?= $order['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <button class="btn btn-primary btn-block" type="submit">Save Status</button>
        </form>
      </div>
    </div>

    <div class="panel">
      <div class="panel-head"><h2>Customer & Delivery</h2></div>
      <div class="panel-body">
        <p><strong><?= e($order['name']) ?></strong></p>
        <p class="muted"><?= e($order['email']) ?></p>
        <p class="muted"><?= e($order['phone']) ?></p>
        <hr style="border:0;border-top:1px solid var(--line);margin:14px 0">
        <p class="muted"><?= e($order['address']) ?><br><?= e($order['city']) ?></p>
        <hr style="border:0;border-top:1px solid var(--line);margin:14px 0">
        <p><strong>Payment:</strong> <?= e($pmLabels[$order['payment_method']] ?? $order['payment_method']) ?></p>
        <p><strong>Placed:</strong> <?= e(date('d M Y, h:i A', strtotime($order['created_at']))) ?></p>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
