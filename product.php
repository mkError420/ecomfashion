<?php
require_once __DIR__ . '/config/db.php';

$slug = $_GET['slug'] ?? '';
$product = $slug ? get_product_by_slug($slug) : null;
if (!$product) {
    http_response_code(404);
    $pageTitle = 'Product not found';
    include __DIR__ . '/includes/header.php';
    echo '<div class="container section"><div class="empty"><h3>Product not found</h3><a class="btn btn-primary" style="margin-top:16px" href="'.BASE_URL.'/shop.php">Back to shop</a></div></div>';
    include __DIR__ . '/includes/footer.php';
    exit;
}

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review'])) {
    verify_csrf();
    $rating = max(1, min(5, (int)($_POST['rating'] ?? 5)));
    $comment = trim($_POST['comment'] ?? '');
    $customer = current_customer();
    $author = $customer ? $customer['name'] : trim($_POST['author'] ?? '');
    if ($author === '' ) {
        flash_set('error', 'Please enter your name to post a review.');
    } elseif ($comment === '') {
        flash_set('error', 'Please write your review.');
    } else {
        $stmt = db()->prepare('INSERT INTO reviews (product_id, customer_id, author, rating, comment) VALUES (?,?,?,?,?)');
        $stmt->execute([(int)$product['id'], $customer['id'] ?? null, $author, $rating, $comment]);
        flash_set('success', 'Thank you! Your review has been posted.');
    }
    redirect('product.php?slug=' . urlencode($product['slug']));
}

$reviews = db()->prepare('SELECT * FROM reviews WHERE product_id=? ORDER BY created_at DESC');
$reviews->execute([(int)$product['id']]);
$reviews = $reviews->fetchAll();
$avgRating = $reviews ? round(array_sum(array_column($reviews,'rating'))/count($reviews),1) : 0;

$related = db()->prepare(
  'SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id=p.category_id
   WHERE p.category_id=? AND p.id<>? ORDER BY RAND() LIMIT 4');
$related->execute([$product['category_id'], (int)$product['id']]);
$related = $related->fetchAll();

$sale = (float)$product['sale_price'] > 0;
$now = effective_price($product);
$stock = (int)$product['stock'];
$pageTitle = $product['name'];
$metaDesc = mb_substr(strip_tags($product['description'] ?? ''), 0, 155);

function star_row(float $r): string {
    $full = (int)round($r);
    return str_repeat('★', $full) . str_repeat('☆', 5-$full);
}

include __DIR__ . '/includes/header.php';
?>

<div class="container section" style="padding-top:24px">
  <div class="breadcrumb">
    <a href="<?= BASE_URL ?>/index.php">Home</a><span>/</span>
    <a href="<?= BASE_URL ?>/shop.php">Shop</a><span>/</span>
    <a href="<?= BASE_URL ?>/shop.php?category=<?= e($product['category_slug']) ?>"><?= e($product['category_name']) ?></a><span>/</span>
    <?= e($product['name']) ?>
  </div>

  <div class="pd">
    <div class="pd-media">
      <img src="<?= e(product_image_url($product['image'])) ?>" alt="<?= e($product['name']) ?>">
    </div>
    <div class="pd-info">
      <div class="pc-cat"><?= e($product['category_name']) ?></div>
      <h1><?= e($product['name']) ?></h1>
      <div class="stars"><?= star_row($avgRating) ?> <span class="muted" style="font-size:.85rem">(<?= count($reviews) ?> review<?= count($reviews)===1?'':'s' ?>)</span></div>

      <div class="pd-price">
        <span class="now"><?= price($now) ?></span>
        <?php if ($sale): ?>
          <span class="was"><?= price($product['price']) ?></span>
          <span class="badge sale" style="position:static">Save <?= price((float)$product['price']-$now) ?></span>
        <?php endif; ?>
      </div>

      <p class="pd-desc"><?= nl2br(e($product['description'])) ?></p>

      <?php if ($stock > 0): ?>
        <span class="stock-pill <?= $stock<=5?'stock-low':'stock-in' ?>">
          <?= $stock<=5 ? 'Only '.$stock.' left in stock' : 'In stock' ?>
        </span>
      <?php else: ?>
        <span class="stock-pill stock-out">Out of stock</span>
      <?php endif; ?>

      <?php if ($stock > 0): ?>
        <form action="<?= BASE_URL ?>/cart-add.php" method="post" class="qty-row">
          <?= csrf_field() ?>
          <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
          <div class="qty-input">
            <button type="button" data-step="-1" aria-label="Decrease">−</button>
            <input type="number" name="qty" value="1" min="1" max="<?= $stock ?>" aria-label="Quantity">
            <button type="button" data-step="1" aria-label="Increase">+</button>
          </div>
          <button class="btn btn-primary" type="submit" name="action" value="add">Add to Cart</button>
          <button class="btn btn-gold" type="submit" name="action" value="buy">Buy Now</button>
        </form>
      <?php endif; ?>

      <div class="pd-meta">
        <div><strong>SKU:</strong> <?= e(strtoupper(substr($product['slug'],0,3)).'-'.$product['id']) ?></div>
        <div><strong>Category:</strong> <?= e($product['category_name']) ?></div>
        <div><strong>Delivery:</strong> 2–5 working days nationwide • Cash on Delivery available</div>
      </div>
    </div>
  </div>

  <!-- REVIEWS -->
  <div class="section" style="padding-top:40px">
    <h2 class="section-title">Customer Reviews</h2>
    <div style="max-width:760px">
      <?php if (empty($reviews)): ?>
        <p class="muted">No reviews yet. Be the first to review this product!</p>
      <?php else: foreach ($reviews as $rv): ?>
        <div class="review">
          <div class="review-head">
            <div>
              <span class="review-author"><?= e($rv['author']) ?></span>
              <span class="stars" style="margin-left:8px"><?= star_row((float)$rv['rating']) ?></span>
            </div>
            <span class="muted" style="font-size:.8rem"><?= e(date('d M Y', strtotime($rv['created_at']))) ?></span>
          </div>
          <p><?= nl2br(e($rv['comment'])) ?></p>
        </div>
      <?php endforeach; endif; ?>

      <div class="form-card" style="max-width:none;margin-top:26px">
        <h3 style="font-family:'Hind Siliguri',sans-serif;margin-bottom:16px">Write a review</h3>
        <form method="post">
          <?= csrf_field() ?>
          <input type="hidden" name="review" value="1">
          <?php if (!is_logged_in()): ?>
            <div class="field"><label>Your name</label><input type="text" name="author" required></div>
          <?php endif; ?>
          <div class="field">
            <label>Rating</label>
            <select name="rating">
              <option value="5">★★★★★ Excellent</option>
              <option value="4">★★★★☆ Good</option>
              <option value="3">★★★☆☆ Average</option>
              <option value="2">★★☆☆☆ Poor</option>
              <option value="1">★☆☆☆☆ Terrible</option>
            </select>
          </div>
          <div class="field"><label>Your review</label><textarea name="comment" required placeholder="Share your experience with this product…"></textarea></div>
          <button class="btn btn-primary" type="submit">Submit Review</button>
        </form>
      </div>
    </div>
  </div>

  <!-- RELATED -->
  <?php if ($related): ?>
  <div class="section" style="padding-top:20px">
    <h2 class="section-title">You may also like</h2>
    <div class="grid">
      <?php foreach ($related as $p) include __DIR__ . '/includes/product-card.php'; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
