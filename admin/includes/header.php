<?php
require_once __DIR__ . '/../../config/db.php';
require_admin();
$admin = current_admin();
$activeKey = $activeKey ?? '';
$adminTitle = $adminTitle ?? 'Dashboard';

$navItems = [
    ['key'=>'dashboard','label'=>'Dashboard','icon'=>'📊','url'=>'index.php'],
    ['key'=>'products','label'=>'Products','icon'=>'👗','url'=>'products.php'],
    ['key'=>'categories','label'=>'Categories','icon'=>'🏷️','url'=>'categories.php'],
    ['key'=>'orders','label'=>'Orders','icon'=>'📦','url'=>'orders.php'],
    ['key'=>'customers','label'=>'Customers','icon'=>'👥','url'=>'customers.php'],
    ['key'=>'messages','label'=>'Messages','icon'=>'✉️','url'=>'messages.php'],
    ['key'=>'settings','label'=>'Settings','icon'=>'⚙️','url'=>'settings.php'],
];
$unread = (int)db()->query('SELECT COUNT(*) FROM contact_messages WHERE is_read=0')->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($adminTitle) ?> • Admin | <?= e(SITE_NAME) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
</head>
<body>
<div class="admin-layout">
  <aside class="sidebar" id="sidebar">
    <div class="brand"><span class="logo">র</span> <?= e(SITE_NAME) ?></div>
    <nav class="side-nav">
      <?php foreach ($navItems as $n): ?>
        <a href="<?= BASE_URL ?>/admin/<?= $n['url'] ?>" class="<?= $activeKey===$n['key']?'active':'' ?>">
          <span class="ico"><?= $n['icon'] ?></span> <?= $n['label'] ?>
          <?php if ($n['key']==='messages' && $unread>0): ?><span class="pill pill-cancelled" style="margin-left:auto"><?= $unread ?></span><?php endif; ?>
        </a>
      <?php endforeach; ?>
      <div class="side-label">Storefront</div>
      <a href="<?= BASE_URL ?>/index.php" target="_blank"><span class="ico">🌐</span> View Site</a>
      <a href="<?= BASE_URL ?>/admin/logout.php"><span class="ico">🚪</span> Logout</a>
    </nav>
  </aside>
  <div class="backdrop" id="backdrop"></div>

  <div class="main">
    <div class="topnav">
      <div style="display:flex;align-items:center;gap:14px">
        <button class="menu-toggle" id="menuToggle" aria-label="Menu">☰</button>
        <h1><?= e($adminTitle) ?></h1>
      </div>
      <div class="user">
        <div class="avatar"><?= e(strtoupper(substr($admin['name'],0,1))) ?></div>
        <div style="line-height:1.2"><strong><?= e($admin['name']) ?></strong><br><span class="muted" style="font-size:.78rem">Administrator</span></div>
      </div>
    </div>
    <div class="content">
    <?php $flash = flash_render(); if ($flash) echo $flash; ?>
