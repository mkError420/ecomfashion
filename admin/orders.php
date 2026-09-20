<?php
$activeKey = 'orders';
$adminTitle = 'Orders';
require_once __DIR__ . '/includes/header.php';

$status = $_GET['status'] ?? '';
$where = ''; $params = [];
if (in_array($status, ['pending','processing','shipped','delivered','cancelled'], true)) {
    $where = 'WHERE status=?'; $params[] = $status;
}
$stmt = db()->prepare("SELECT * FROM orders $where ORDER BY created_at DESC");
$stmt->execute($params);
$orders = $stmt->fetchAll();

$counts = ['all'=>0];
foreach (db()->query('SELECT status, COUNT(*) c FROM orders GROUP BY status') as $r) { $counts[$r['status']] = (int)$r['c']; $counts['all'] += (int)$r['c']; }
?>

<div class="panel">
  <div class="panel-head">
    <h2>Orders</h2>
    <div style="display:flex;gap:6px;flex-wrap:wrap">
      <?php
      $tabs = ['all'=>'All','pending'=>'Pending','processing'=>'Processing','shipped'=>'Shipped','delivered'=>'Delivered','cancelled'=>'Cancelled'];
      foreach ($tabs as $k=>$label):
        $active = ($k==='all' && $status==='') || $status===$k;
        $href = $k==='all' ? 'orders.php' : 'orders.php?status='.$k;
      ?>
        <a class="btn btn-sm <?= $active?'btn-primary':'btn-ghost' ?>" href="<?= BASE_URL ?>/admin/<?= $href ?>"><?= $label ?> (<?= $counts[$k] ?? 0 ?>)</a>
      <?php endforeach; ?>
    </div>
  </div>
  <div style="overflow-x:auto">
  <table class="data">
    <thead><tr><th>Order</th><th>Customer</th><th>Date</th><th>Payment</th><th>Total</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php if (empty($orders)): ?><tr><td colspan="7" class="muted">No orders found.</td></tr><?php endif; ?>
    <?php foreach ($orders as $o): ?>
      <tr>
        <td data-label="Order"><strong>#<?= str_pad((string)$o['id'],5,'0',STR_PAD_LEFT) ?></strong></td>
        <td data-label="Customer"><?= e($o['name']) ?><br><span class="muted" style="font-size:.8rem"><?= e($o['phone']) ?></span></td>
        <td data-label="Date" class="nowrap muted"><?= e(date('d M Y', strtotime($o['created_at']))) ?></td>
        <td data-label="Payment" style="text-transform:capitalize"><?= e(str_replace('_',' ',$o['payment_method'])) ?></td>
        <td data-label="Total"><strong><?= price($o['total']) ?></strong></td>
        <td data-label="Status"><span class="pill pill-<?= e($o['status']) ?>"><?= e($o['status']) ?></span></td>
        <td data-label=""><a class="btn btn-ghost btn-sm" href="<?= BASE_URL ?>/admin/order-view.php?id=<?= (int)$o['id'] ?>">View</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
