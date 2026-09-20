<?php
require_once __DIR__ . '/config/db.php';
$pageTitle = 'Home';
$activeNav = 'home';

$featured = db()->query(
  'SELECT p.*, c.name AS category_name FROM products p
   LEFT JOIN categories c ON c.id=p.category_id
   WHERE p.featured=1 ORDER BY p.created_at DESC LIMIT 8'
)->fetchAll();

$latest = db()->query(
  'SELECT p.*, c.name AS category_name FROM products p
   LEFT JOIN categories c ON c.id=p.category_id
   ORDER BY p.created_at DESC LIMIT 8'
)->fetchAll();

$catColors = [
  'saree' => 'linear-gradient(135deg,#191713,#4a463e)',
  'salwar-kameez' => 'linear-gradient(135deg,#2e2b26,#6e675c)',
  'panjabi' => 'linear-gradient(135deg,#3a3731,#8f887b)',
  'lehenga' => 'linear-gradient(135deg,#26231f,#55524c)',
  'western' => 'linear-gradient(135deg,#4a463e,#8f887b)',
  'kids' => 'linear-gradient(135deg,#55524c,#a49c8e)',
  'accessories' => 'linear-gradient(135deg,#14120f,#3a3731)',
];

include __DIR__ . '/includes/header.php';
?>

<!-- HERO -->
<section class="hero">
  <div class="container">
    <div>
      <h1>Tradition woven<br>in every thread</h1>
      <p>Discover authentic Bangladeshi fashion — handpicked Jamdani sarees, salwar kameez, panjabi, lehenga and contemporary styles, delivered to your doorstep nationwide.</p>
      <div class="hero-cta">
        <a href="<?= BASE_URL ?>/shop.php" class="btn btn-gold">Shop Now</a>
        <a href="<?= BASE_URL ?>/shop.php?category=saree" class="btn btn-outline" style="border-color:#fff;color:#fff">Explore Sarees</a>
      </div>
    </div>
    <div class="hero-art">
      <div class="card">
        <img src="<?= BASE_URL ?>/assets/img/products/saree-jamdani.svg" alt="Jamdani saree">
      </div>
      <div class="hero-badge">Free delivery over <?= price(setting('free_shipping_over','5000')) ?></div>
    </div>
  </div>
</section>

<!-- FEATURES -->
<section class="section" style="padding-bottom:20px">
  <div class="container features">
    <div class="feature"><div class="fi">🚚</div><div><h4>Fast Delivery</h4><p>Nationwide shipping across Bangladesh.</p></div></div>
    <div class="feature"><div class="fi">💵</div><div><h4>Cash on Delivery</h4><p>Pay when you receive your order.</p></div></div>
    <div class="feature"><div class="fi">🔄</div><div><h4>Easy Returns</h4><p>7-day hassle-free return policy.</p></div></div>
    <div class="feature"><div class="fi">✅</div><div><h4>100% Authentic</h4><p>Genuine handpicked products.</p></div></div>
  </div>
</section>

<!-- CATEGORIES -->
<section class="section">
  <div class="container">
    <h2 class="section-title">Shop by Category</h2>
    <p class="section-sub">Find exactly what you're looking for.</p>
    <div class="cat-grid">
      <?php foreach (get_category_tree() as $parentCat): ?>
        <a class="cat-card" style="background:<?= $catColors[$parentCat['category']['slug']] ?? 'linear-gradient(135deg,#191713,#8f887b)' ?>"
           href="<?= BASE_URL ?>/shop.php?category=<?= e($parentCat['category']['slug']) ?>">
          <span><?= e($parentCat['category']['name']) ?></span>
          <?php if (!empty($parentCat['children'])): ?>
            <div style="font-size:0.75rem;opacity:0.8;margin-top:4px">
              <?= count($parentCat['children']) ?> sub-categorie<?= count($parentCat['children']) > 1 ? 's' : '' ?>
            </div>
          <?php endif; ?>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- FEATURED -->
<section class="section" style="background:var(--soft)">
  <div class="container">
    <h2 class="section-title">Featured Products</h2>
    <p class="section-sub">Our most loved pieces this season.</p>
    <div class="grid">
      <?php foreach ($featured as $p) include __DIR__ . '/includes/product-card.php'; ?>
    </div>
  </div>
</section>

<!-- PROMO BANNER -->
<section class="section">
  <div class="container">
    <div style="background:linear-gradient(120deg,#14120f,#6e675c);border-radius:20px;color:#fff;padding:46px 34px;display:flex;justify-content:space-between;align-items:center;gap:24px;flex-wrap:wrap;filter:grayscale(1)">
      <div>
        <h2 style="font-size:clamp(1.5rem,3vw,2.2rem)">Eid Collection is here 🌙</h2>
        <p style="opacity:.92;max-width:520px">Up to 30% off on selected panjabi, saree & three-piece sets. Limited stock available.</p>
      </div>
      <a href="<?= BASE_URL ?>/shop.php" class="btn btn-gold">Shop the Sale</a>
    </div>
  </div>
</section>

<!-- LATEST -->
<section class="section" style="padding-top:0">
  <div class="container">
    <h2 class="section-title">New Arrivals</h2>
    <p class="section-sub">Fresh styles added to the store.</p>
    <div class="grid">
      <?php foreach ($latest as $p) include __DIR__ . '/includes/product-card.php'; ?>
    </div>
    <div class="text-center" style="margin-top:34px">
      <a href="<?= BASE_URL ?>/shop.php" class="btn btn-outline">View All Products</a>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
