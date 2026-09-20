<?php
require_once __DIR__ . '/config/db.php';
require_login();
$pageTitle = 'My Account';
$customer = current_customer();

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if (isset($_POST['update_profile'])) {
        $stmt = db()->prepare('UPDATE customers SET name=?, phone=?, address=?, city=? WHERE id=?');
        $stmt->execute([trim($_POST['name']), trim($_POST['phone']) ?: null, trim($_POST['address']) ?: null, trim($_POST['city']) ?: null, $customer['id']]);
        flash_set('success', 'Profile updated.');
        redirect('account.php');
    }
    if (isset($_POST['change_password'])) {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        if (!password_verify($current, $customer['password_hash'])) {
            flash_set('error', 'Current password is incorrect.');
        } elseif (strlen($new) < 6) {
            flash_set('error', 'New password must be at least 6 characters.');
        } else {
            db()->prepare('UPDATE customers SET password_hash=? WHERE id=?')->execute([password_hash($new, PASSWORD_DEFAULT), $customer['id']]);
            flash_set('success', 'Password changed.');
        }
        redirect('account.php');
    }
}

$orders = db()->prepare('SELECT * FROM orders WHERE customer_id=? ORDER BY created_at DESC');
$orders->execute([$customer['id']]); $orders = $orders->fetchAll();

$statusColors = ['pending'=>'#4a463e','processing'=>'#33312c','shipped'=>'#2c2a26','delivered'=>'#191713','cancelled'=>'#8f887b'];

include __DIR__ . '/includes/header.php';
?>
<div class="page-head"><div class="container"><h1>My Account</h1><p>Welcome, <?= e($customer['name']) ?></p></div></div>

<div class="container section" style="padding-top:30px">
  <div class="shop-layout" style="grid-template-columns:1fr 1fr">
    <div class="form-card" style="max-width:none">
      <h3 style="font-family:'Hind Siliguri',sans-serif;margin-bottom:16px">Profile Details</h3>
      <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="update_profile" value="1">
        <div class="field"><label>Full name</label><input name="name" value="<?= e($customer['name']) ?>" required></div>
        <div class="field"><label>Email</label><input value="<?= e($customer['email']) ?>" disabled></div>
        <div class="form-grid">
          <div class="field"><label>Phone</label><input name="phone" value="<?= e($customer['phone']) ?>"></div>
          <div class="field"><label>City</label><input name="city" value="<?= e($customer['city']) ?>"></div>
        </div>
        <div class="field"><label>Address</label><input name="address" value="<?= e($customer['address']) ?>"></div>
        <button class="btn btn-primary" type="submit">Save Changes</button>
      </form>
      <hr style="border:0;border-top:1px solid var(--line);margin:24px 0">
      <h3 style="font-family:'Hind Siliguri',sans-serif;margin-bottom:16px">Change Password</h3>
      <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="change_password" value="1">
        <div class="field"><label>Current password</label><input type="password" name="current_password" required></div>
        <div class="field"><label>New password</label><input type="password" name="new_password" required minlength="6"></div>
        <button class="btn btn-ghost" type="submit">Update Password</button>
      </form>
      <hr style="border:0;border-top:1px solid var(--line);margin:24px 0">
      <a class="btn btn-outline" href="<?= BASE_URL ?>/logout.php">Log out</a>
    </div>

    <div>
      <h3 style="font-family:'Hind Siliguri',sans-serif;margin-bottom:16px">Order History</h3>
      <?php if (empty($orders)): ?>
        <div class="empty"><p>No orders yet.</p><a class="btn btn-primary" style="margin-top:12px" href="<?= BASE_URL ?>/shop.php">Start Shopping</a></div>
      <?php else: foreach ($orders as $o): ?>
        <div class="form-card" style="max-width:none;margin-bottom:14px;padding:20px">
          <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px">
            <div>
              <strong>Order #<?= str_pad((string)$o['id'],5,'0',STR_PAD_LEFT) ?></strong>
              <div class="muted" style="font-size:.82rem"><?= e(date('d M Y', strtotime($o['created_at']))) ?> • <?= price($o['total']) ?></div>
            </div>
            <span class="stock-pill" style="background:<?= $statusColors[$o['status']] ?>1a;color:<?= $statusColors[$o['status']] ?>;text-transform:capitalize"><?= e($o['status']) ?></span>
          </div>
          <a href="<?= BASE_URL ?>/track-order.php?id=<?= (int)$o['id'] ?>" style="color:var(--crimson);font-weight:600;font-size:.88rem;margin-top:8px;display:inline-block">View details →</a>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
