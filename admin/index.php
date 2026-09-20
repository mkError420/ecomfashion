<?php
$activeKey = 'dashboard';
$adminTitle = 'Dashboard';
require_once __DIR__ . '/includes/header.php';
$pdo = db();

$totalRevenue = (float)$pdo->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE status<>'cancelled'")->fetchColumn();
$orderCount   = (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$pendingCount = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status='pending'")->fetchColumn();
$productCount = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$customerCount= (int)$pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn();
$lowStock     = $pdo->query("SELECT COUNT(*) FROM products WHERE stock<=5")->fetchColumn();

$recentOrders = $pdo->query('SELECT * FROM orders ORDER BY created_at DESC LIMIT 6')->fetchAll();

$topProducts = $pdo->query(
  'SELECT product_name, SUM(quantity) AS sold, SUM(price*quantity) AS revenue
   FROM order_items GROUP BY product_id, product_name ORDER BY sold DESC LIMIT 5')->fetchAll();
$maxSold = $topProducts ? (int)$topProducts[0]['sold'] : 1;

$lowItems = $pdo->query('SELECT * FROM products WHERE stock<=5 ORDER BY stock ASC LIMIT 5')->fetchAll();
?>

<div class="stats">
  <div class="stat"><div class="ic">💰</div><div class="label">Total Revenue</div><div class="num"><?= price($totalRevenue) ?></div></div>
  <div class="stat"><div class="ic">📦</div><div class="label">Orders</div><div class="num"><?= $orderCount ?></div><div class="muted" style="font-size:.8rem"><?= $pendingCount ?> pending</div></div>
  <div class="stat"><div class="ic">👗</div><div class="label">Products</div><div class="num"><?= $productCount ?></div><div class="muted" style="font-size:.8rem"><?= (int)$lowStock ?> low stock</div></div>
  <div class="stat"><div class="ic">👥</div><div class="label">Customers</div><div class="num"><?= $customerCount ?></div></div>
</div>

<div class="dash-grid">
  <div class="panel">
    <div class="panel-head"><h2>Recent Orders</h2><a class="btn btn-ghost btn-sm" href="<?= BASE_URL ?>/admin/orders.php">View all</a></div>
    <div style="overflow-x:auto">
    <table class="data">
      <thead><tr><th>Order</th><th>Customer</th><th>Date</th><th>Total</th><th>Status</th></tr></thead>
      <tbody>
      <?php if (empty($recentOrders)): ?><tr><td colspan="5" class="muted">No orders yet.</td></tr><?php endif; ?>
      <?php foreach ($recentOrders as $o): ?>
        <tr>
          <td data-label="Order"><a href="<?= BASE_URL ?>/admin/order-view.php?id=<?= (int)$o['id'] ?>" style="color:var(--crimson);font-weight:600">#<?= str_pad((string)$o['id'],5,'0',STR_PAD_LEFT) ?></a></td>
          <td data-label="Customer"><?= e($o['name']) ?></td>
          <td data-label="Date" class="nowrap muted"><?= e(date('d M Y', strtotime($o['created_at']))) ?></td>
          <td data-label="Total"><strong><?= price($o['total']) ?></strong></td>
          <td data-label="Status"><span class="pill pill-<?= e($o['status']) ?>"><?= e($o['status']) ?></span></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  </div>

  <div>
    <div class="panel">
      <div class="panel-head"><h2>Top Products</h2></div>
      <div class="panel-body">
        <?php if (empty($topProducts)): ?><p class="muted">No sales data yet.</p><?php else: foreach ($topProducts as $tp): ?>
          <div style="margin-bottom:14px">
            <div style="display:flex;justify-content:space-between;font-size:.9rem"><span><?= e($tp['product_name']) ?></span><strong><?= (int)$tp['sold'] ?> sold</strong></div>
            <div class="bar"><span style="width:<?= max(6,(int)$tp['sold']/$maxSold*100) ?>%"></span></div>
          </div>
        <?php endforeach; endif; ?>
      </div>
    </div>

    <div class="panel">
      <div class="panel-head"><h2>Low Stock Alert</h2></div>
      <ul class="mini-list" style="padding:0 22px 14px">
        <?php if (empty($lowItems)): ?><li class="muted">All products are well stocked.</li><?php else: foreach ($lowItems as $li): ?>
          <li><span><?= e($li['name']) ?></span><span class="pill <?= (int)$li['stock']===0?'pill-cancelled':'pill-pending' ?>"><?= (int)$li['stock'] ?> left</span></li>
        <?php endforeach; endif; ?>
      </ul>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
