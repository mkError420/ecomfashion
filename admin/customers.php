<?php
$activeKey = 'customers';
$adminTitle = 'Customers';
require_once __DIR__ . '/includes/header.php';

$q = trim($_GET['q'] ?? '');
$where=''; $params=[];
if ($q!=='') { $where='WHERE name LIKE ? OR email LIKE ? OR phone LIKE ?'; $params=["%$q%","%$q%","%$q%"]; }
$stmt = db()->prepare("SELECT c.*, (SELECT COUNT(*) FROM orders o WHERE o.customer_id=c.id) AS orders,
  (SELECT COALESCE(SUM(total),0) FROM orders o WHERE o.customer_id=c.id AND o.status<>'cancelled') AS spent
  FROM customers c $where ORDER BY c.created_at DESC");
$stmt->execute($params);
$customers = $stmt->fetchAll();
?>

<div class="panel">
  <div class="panel-head">
    <h2>Customers (<?= count($customers) ?>)</h2>
    <form method="get"><input type="search" name="q" value="<?= e($q) ?>" placeholder="Search name/email/phone…" style="padding:8px 12px;border:1px solid var(--line);border-radius:8px"></form>
  </div>
  <div style="overflow-x:auto">
  <table class="data">
    <thead><tr><th>Name</th><th>Contact</th><th>City</th><th>Orders</th><th>Total Spent</th><th>Joined</th></tr></thead>
    <tbody>
    <?php if (empty($customers)): ?><tr><td colspan="6" class="muted">No customers found.</td></tr><?php endif; ?>
    <?php foreach ($customers as $c): ?>
      <tr>
        <td data-label="Name"><div style="display:flex;align-items:center;gap:10px"><span class="avatar" style="width:34px;height:34px;font-size:.85rem"><?= e(strtoupper(substr($c['name'],0,1))) ?></span><strong><?= e($c['name']) ?></strong></div></td>
        <td data-label="Contact"><?= e($c['email']) ?><br><span class="muted" style="font-size:.8rem"><?= e($c['phone']) ?></span></td>
        <td data-label="City"><?= e($c['city'] ?? '—') ?></td>
        <td data-label="Orders"><?= (int)$c['orders'] ?></td>
        <td data-label="Total Spent"><strong><?= price($c['spent']) ?></strong></td>
        <td data-label="Joined" class="muted nowrap"><?= e(date('d M Y', strtotime($c['created_at']))) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
