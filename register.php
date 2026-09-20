<?php
require_once __DIR__ . '/config/db.php';
$pageTitle = 'Register';
if (is_logged_in()) { redirect('account.php'); }

$errors = [];
$old = ['name'=>'','email'=>'','phone'=>'','city'=>''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    foreach ($old as $k=>$_) $old[$k] = trim($_POST[$k] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if ($old['name'] === '') $errors[] = 'Name is required.';
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';

    if (!$errors) {
        $chk = db()->prepare('SELECT id FROM customers WHERE email=?');
        $chk->execute([$old['email']]);
        if ($chk->fetch()) {
            $errors[] = 'An account with this email already exists.';
        } else {
            $stmt = db()->prepare('INSERT INTO customers (name,email,phone,city,password_hash) VALUES (?,?,?,?,?)');
            $stmt->execute([$old['name'], $old['email'], $old['phone'] ?: null, $old['city'] ?: null, password_hash($password, PASSWORD_DEFAULT)]);
            $_SESSION['customer_id'] = (int)db()->lastInsertId();
            flash_set('success', 'Account created. Welcome to Rongdhonu Fashion!');
            redirect('account.php');
        }
    }
}
include __DIR__ . '/includes/header.php';
?>
<div class="container section">
  <div class="form-card">
    <h1 class="section-title text-center" style="margin-bottom:6px">Create account</h1>
    <p class="muted text-center" style="margin-bottom:22px">Join us for a faster checkout</p>
    <?php foreach ($errors as $err): ?><div class="alert alert-error"><?= e($err) ?></div><?php endforeach; ?>
    <form method="post">
      <?= csrf_field() ?>
      <div class="field"><label>Full name *</label><input name="name" value="<?= e($old['name']) ?>" required></div>
      <div class="field"><label>Email *</label><input type="email" name="email" value="<?= e($old['email']) ?>" required></div>
      <div class="form-grid">
        <div class="field"><label>Phone</label><input name="phone" value="<?= e($old['phone']) ?>" placeholder="01XXXXXXXXX"></div>
        <div class="field"><label>City</label><input name="city" value="<?= e($old['city']) ?>"></div>
      </div>
      <div class="field"><label>Password *</label><input type="password" name="password" required minlength="6"></div>
      <div class="field"><label>Confirm password *</label><input type="password" name="confirm" required minlength="6"></div>
      <button class="btn btn-primary btn-block" type="submit">Create Account</button>
    </form>
    <p class="form-note">Already have an account? <a href="<?= BASE_URL ?>/login.php">Login</a></p>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
