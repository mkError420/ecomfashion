<?php
require_once __DIR__ . '/../config/db.php';
$pageTitle = $pageTitle ?? SITE_NAME;
$activeNav = $activeNav ?? '';
$categories = get_categories();
$bySlug = [];
foreach ($categories as $c) { $bySlug[$c['slug']] = $c; }
$menuGroups = [
    "Women's Fashion" => ['saree', 'salwar-kameez', 'lehenga', 'western'],
    "Men's Fashion"   => ['panjabi'],
    'More'            => ['kids', 'accessories'],
];
$customer = current_customer();
$cartCount = cart_count();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="<?= e($metaDesc ?? 'Rongdhonu Fashion — sarees, salwar kameez, panjabi, lehenga & more. Authentic Bangladeshi fashion delivered nationwide.') ?>">
<title><?= e($pageTitle) ?> | <?= e(SITE_NAME) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
<link rel="icon" href="<?= BASE_URL ?>/assets/img/placeholder.svg">
</head>
<body>

<div class="topbar">
  <div class="container">
    <div>📞 <?= e(setting('site_phone','+880 1700-000000')) ?> &nbsp;•&nbsp; ✉️ <?= e(setting('site_email','hello@rongdhonu.com')) ?></div>
    <div class="tb-links">
      <a href="<?= BASE_URL ?>/track-order.php">Track Order</a>
      <a href="<?= BASE_URL ?>/contact.php">Contact</a>
      <?php if ($customer): ?>
        <a href="<?= BASE_URL ?>/account.php">My Account</a>
      <?php else: ?>
        <a href="<?= BASE_URL ?>/login.php">Login</a>
      <?php endif; ?>
    </div>
  </div>
</div>

<header class="site-header">
  <div class="container header-inner">
    <a class="brand" href="<?= BASE_URL ?>/index.php">
      <span class="logo">র</span>
      <span><?= e(SITE_NAME) ?><small><?= e(setting('tagline','Tradition woven in every thread')) ?></small></span>
    </a>

    <div class="header-actions">
      <div class="search-wrap" id="searchWrap">
        <form class="search-form" action="<?= BASE_URL ?>/shop.php" method="get">
          <input type="search" name="q" placeholder="Search products…" value="<?= e($_GET['q'] ?? '') ?>" aria-label="Search products">
          <button type="submit" aria-label="Search">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4-4"/></svg>
          </button>
        </form>
      </div>
      <button class="icon-btn" id="searchToggle" aria-label="Toggle search" style="display:none">
        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4-4"/></svg>
      </button>
      <a class="icon-btn" href="<?= BASE_URL ?>/cart.php" aria-label="Cart">
        <svg viewBox="0 0 24 24"><path d="M6 6h15l-1.5 9h-12z"/><circle cx="9" cy="20" r="1.4"/><circle cx="18" cy="20" r="1.4"/><path d="M6 6L5 3H2"/></svg>
        <?php if ($cartCount>0): ?><span class="cart-badge"><?= $cartCount ?></span><?php endif; ?>
      </a>
      <?php if ($customer): ?>
        <a class="icon-btn" href="<?= BASE_URL ?>/account.php" aria-label="Account" title="<?= e($customer['name']) ?>">
          <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/></svg>
        </a>
      <?php else: ?>
        <a class="icon-btn" href="<?= BASE_URL ?>/login.php" aria-label="Login">
          <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/></svg>
        </a>
      <?php endif; ?>
      <button class="icon-btn nav-toggle" id="navToggle" aria-label="Menu">
        <svg viewBox="0 0 24 24"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
      </button>
    </div>
  </div>

  <nav class="menu-bar" id="mainNav" aria-label="Product categories">
    <div class="container menu-bar-inner">
      <div class="menu-item has-drop menu-allcats">
        <button class="menu-link menu-trigger" type="button" aria-haspopup="true" aria-expanded="false">All Categories</button>
        <div class="dropdown">
          <?php foreach ($categories as $c): ?>
            <a href="<?= BASE_URL ?>/shop.php?category=<?= e($c['slug']) ?>"><?= e($c['name']) ?></a>
          <?php endforeach; ?>
        </div>
      </div>
      <a class="menu-link <?= $activeNav==='home'?'active':'' ?>" href="<?= BASE_URL ?>/index.php">Home</a>
      <a class="menu-link <?= $activeNav==='shop'?'active':'' ?>" href="<?= BASE_URL ?>/shop.php">Shop</a>
      <?php foreach ($menuGroups as $label => $slugs): ?>
        <?php $items = []; foreach ($slugs as $s) { if (isset($bySlug[$s])) { $items[] = $bySlug[$s]; } } ?>
        <?php if ($items || $label === 'More'): ?>
        <div class="menu-item has-drop">
          <button class="menu-link menu-trigger" type="button" aria-haspopup="true" aria-expanded="false"><?= e($label) ?></button>
          <div class="dropdown">
            <?php foreach ($items as $c): ?>
              <a href="<?= BASE_URL ?>/shop.php?category=<?= e($c['slug']) ?>"><?= e($c['name']) ?></a>
            <?php endforeach; ?>
            <?php if ($label === 'More'): ?>
              <a href="<?= BASE_URL ?>/contact.php">Contact Us</a>
            <?php endif; ?>
          </div>
        </div>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
  </nav>
</header>

<main>
<?php $flash = flash_render(); if ($flash): ?>
  <div class="container" style="padding-top:18px"><?= $flash ?></div>
<?php endif; ?>
