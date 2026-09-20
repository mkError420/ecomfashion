<?php
/** Expects $p (product row). Renders one product card. */
$sale = (float)$p['sale_price'] > 0;
$now = effective_price($p);
$out = (int)$p['stock'] <= 0;
?>
<article class="product-card">
  <div class="pc-media">
    <a href="<?= BASE_URL ?>/product.php?slug=<?= e($p['slug']) ?>">
      <img src="<?= e(product_image_url($p['image'])) ?>" alt="<?= e($p['name']) ?>" loading="lazy">
    </a>
    <?php if ($out): ?>
      <span class="badge out">Sold out</span>
    <?php elseif ($sale): ?>
      <span class="badge sale">Sale</span>
    <?php elseif ((int)$p['featured']): ?>
      <span class="badge">Featured</span>
    <?php endif; ?>
  </div>
  <div class="pc-body">
    <?php if (!empty($p['category_name'])): ?>
      <div class="pc-cat"><?= e($p['category_name']) ?></div>
    <?php endif; ?>
    <div class="pc-name"><a href="<?= BASE_URL ?>/product.php?slug=<?= e($p['slug']) ?>"><?= e($p['name']) ?></a></div>
    <div class="pc-price">
      <span class="now"><?= price($now) ?></span>
      <?php if ($sale): ?><span class="was"><?= price($p['price']) ?></span><?php endif; ?>
    </div>
    <?php if (!$out): ?>
      <form action="<?= BASE_URL ?>/cart-add.php" method="post" style="margin-top:10px">
        <?= csrf_field() ?>
        <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
        <input type="hidden" name="qty" value="1">
        <button class="btn btn-primary btn-block btn-sm" type="submit">Add to Cart</button>
      </form>
    <?php else: ?>
      <button class="btn btn-ghost btn-block btn-sm" type="button" disabled style="margin-top:10px">Unavailable</button>
    <?php endif; ?>
  </div>
</article>
