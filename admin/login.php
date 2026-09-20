<?php
require_once __DIR__ . '/../config/db.php';
if (current_admin()) { redirect('admin/index.php'); }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if (admin_login(trim($_POST['email'] ?? ''), $_POST['password'] ?? '')) {
        flash_set('success', 'Logged in.');
        redirect('admin/index.php');
    }
    $error = 'Invalid email or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login | <?= e(SITE_NAME) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
</head>
<body>
<div class="login-wrap">
  <div class="login-card">
    <div class="logo">র</div>
    <h1 style="text-align:center;font-family:'Playfair Display',serif;font-size:1.5rem">Admin Panel</h1>
    <p class="muted" style="text-align:center;margin-bottom:22px"><?= e(SITE_NAME) ?></p>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <?php $flash = flash_render(); if ($flash) echo $flash; ?>
    <form method="post">
      <?= csrf_field() ?>
      <div class="field"><label>Email</label><input type="email" name="email" required autofocus></div>
      <div class="field"><label>Password</label><input type="password" name="password" required></div>
      <button class="btn btn-primary btn-block" type="submit">Sign In</button>
    </form>
    <p class="muted" style="text-align:center;font-size:.8rem;margin-top:18px">Demo: admin@rongdhonu.com / admin123</p>
    <p style="text-align:center;margin-top:10px"><a href="<?= BASE_URL ?>/index.php" style="color:var(--crimson);font-size:.85rem;font-weight:600">← Back to store</a></p>
  </div>
</div>
</body>
</html>
