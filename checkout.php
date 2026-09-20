<?php
require_once __DIR__ . '/config/db.php';
$pageTitle = 'Checkout';

$details = cart_details();
$lines = $details['lines'];
if (empty($lines)) {
    flash_set('warning', 'Your cart is empty.');
    redirect('cart.php');
}

$customer = current_customer();
$subtotal = $details['subtotal'];
$freeOver = (float)setting('free_shipping_over', '5000');
$ship = $subtotal >= $freeOver ? 0.0 : shipping_cost();
$total = $subtotal + $ship;

$errors = [];
$old = [
    'name' => $customer['name'] ?? '', 'email' => $customer['email'] ?? '',
    'phone' => $customer['phone'] ?? '', 'address' => $customer['address'] ?? '',
    'city' => $customer['city'] ?? '', 'payment_method' => 'cash_on_delivery', 'note' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    foreach (['name','email','phone','address','city'] as $f) {
        $old[$f] = trim($_POST[$f] ?? '');
    }
    $old['payment_method'] = $_POST['payment_method'] ?? 'cash_on_delivery';
    $old['note'] = trim($_POST['note'] ?? '');

    if ($old['name'] === '') $errors[] = 'Full name is required.';
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
    if (!preg_match('/^[0-9+\-\s]{6,20}$/', $old['phone'])) $errors[] = 'A valid phone number is required.';
    if ($old['address'] === '') $errors[] = 'Delivery address is required.';
    if ($old['city'] === '') $errors[] = 'City is required.';
    if (!in_array($old['payment_method'], ['cash_on_delivery','bkash','nagad','card'], true)) $errors[] = 'Invalid payment method.';

    // Re-validate stock
    foreach ($lines as $line) {
        if ($line['qty'] > (int)$line['product']['stock']) {
            $errors[] = 'Not enough stock for “'.$line['product']['name'].'”.';
        }
    }

    if (empty($errors)) {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare(
              'INSERT INTO orders (customer_id,name,email,phone,address,city,payment_method,subtotal,shipping,total,status,note)
               VALUES (?,?,?,?,?,?,?,?,?,?, "pending", ?)');
            $stmt->execute([
                $customer['id'] ?? null, $old['name'], $old['email'], $old['phone'],
                $old['address'], $old['city'], $old['payment_method'],
                $subtotal, $ship, $total, $old['note'] ?: null,
            ]);
            $orderId = (int)$pdo->lastInsertId();

            $itemStmt = $pdo->prepare('INSERT INTO order_items (order_id,product_id,product_name,price,quantity) VALUES (?,?,?,?,?)');
            $stockStmt = $pdo->prepare('UPDATE products SET stock = GREATEST(stock - ?, 0) WHERE id = ?');
            foreach ($lines as $line) {
                $p = $line['product'];
                $itemStmt->execute([$orderId, (int)$p['id'], $p['name'], $line['unit'], $line['qty']]);
                $stockStmt->execute([$line['qty'], (int)$p['id']]);
            }
            $pdo->commit();
            cart_clear();
            $_SESSION['last_order'] = $orderId;
            redirect('order-success.php?id=' . $orderId);
        } catch (Throwable $e) {
            $pdo->rollBack();
            $errors[] = 'Could not place your order. Please try again.';
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="page-head"><div class="container"><h1>Checkout</h1><p>Complete your order</p></div></div>

<div class="container section" style="padding-top:30px">
  <?php foreach ($errors as $err): ?><div class="alert alert-error"><?= e($err) ?></div><?php endforeach; ?>

  <form method="post" class="cart-layout" style="align-items:start">
    <?= csrf_field() ?>
    <div>
      <div class="form-card" style="max-width:none">
        <h3 style="font-family:'Hind Siliguri',sans-serif;margin-bottom:18px">Delivery Details</h3>
        <div class="form-grid">
          <div class="field"><label>Full name *</label><input name="name" value="<?= e($old['name']) ?>" required></div>
          <div class="field"><label>Phone *</label><input name="phone" value="<?= e($old['phone']) ?>" placeholder="01XXXXXXXXX" required></div>
        </div>
        <div class="field"><label>Email *</label><input type="email" name="email" value="<?= e($old['email']) ?>" required></div>
        <div class="field"><label>Address *</label><input name="address" value="<?= e($old['address']) ?>" placeholder="House, Road, Area" required></div>
        <div class="form-grid">
          <div class="field">
            <label>City *</label>
            <select name="city" required>
              <?php foreach (['Dhaka','Chattogram','Sylhet','Khulna','Rajshahi','Barishal','Rangpur','Mymensingh','Cumilla','Narayanganj','Gazipur','Other'] as $city): ?>
                <option value="<?= e($city) ?>" <?= $old['city']===$city?'selected':'' ?>><?= e($city) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field">
            <label>Payment method *</label>
            <select name="payment_method" required>
              <option value="cash_on_delivery" <?= $old['payment_method']==='cash_on_delivery'?'selected':'' ?>>Cash on Delivery</option>
              <option value="bkash" <?= $old['payment_method']==='bkash'?'selected':'' ?>>bKash</option>
              <option value="nagad" <?= $old['payment_method']==='nagad'?'selected':'' ?>>Nagad</option>
              <option value="card" <?= $old['payment_method']==='card'?'selected':'' ?>>Card (pay on delivery)</option>
            </select>
          </div>
        </div>
        <div class="field"><label>Order note (optional)</label><textarea name="note" placeholder="Any special instructions…"><?= e($old['note']) ?></textarea></div>
      </div>
    </div>

    <aside class="cart-summary">
      <h3 style="font-family:'Hind Siliguri',sans-serif;margin-bottom:16px">Your Order</h3>
      <?php foreach ($lines as $line): ?>
        <div class="summary-row"><span><?= e($line['product']['name']) ?> × <?= (int)$line['qty'] ?></span><span><?= price($line['total']) ?></span></div>
      <?php endforeach; ?>
      <div class="summary-row" style="border-top:1px solid var(--line);margin-top:10px;padding-top:12px"><span>Subtotal</span><span><?= price($subtotal) ?></span></div>
      <div class="summary-row"><span>Shipping</span><span><?= $ship==0?'Free':price($ship) ?></span></div>
      <div class="summary-row total"><span>Total</span><span><?= price($total) ?></span></div>
      <button class="btn btn-primary btn-block" style="margin-top:16px" type="submit">Place Order</button>
      <?php if (!$customer): ?><p class="muted text-center" style="font-size:.8rem;margin-top:10px">Checking out as guest. <a href="<?= BASE_URL ?>/login.php" style="color:var(--crimson)">Login</a> for faster checkout.</p><?php endif; ?>
    </aside>
  </form>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
