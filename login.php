<?php
require_once __DIR__ . '/config/db.php';
$pageTitle = 'Login';
if (is_logged_in()) { redirect('account.php'); }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (customer_login($email, $password)) {
        flash_set('success', 'Welcome back!');
        redirect('account.php');
    }
    $error = 'Invalid email or password.';
}
include __DIR__ . '/includes/header.php';
?>
<div class="container section">
  <div class="form-card">
    <h1 class="section-title text-center" style="margin-bottom:6px">Welcome back</h1>
    <p class="muted text-center" style="margin-bottom:22px">Log in to your account</p>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <form method="post">
      <?= csrf_field() ?>
      <div class="field"><label>Email</label><input type="email" name="email" required autofocus></div>
      <div class="field"><label>Password</label><input type="password" name="password" required></div>
      <button class="btn btn-primary btn-block" type="submit">Login</button>
    </form>
    <p class="form-note">Don't have an account? <a href="<?= BASE_URL ?>/register.php">Register</a></p>
    <p class="form-note muted" style="font-size:.8rem">Demo: customer@example.com / customer123</p>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
