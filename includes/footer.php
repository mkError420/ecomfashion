</main>

<footer class="site-footer">
  <div class="container footer-grid">
    <div>
      <div class="footer-brand"><?= e(SITE_NAME) ?></div>
      <p style="font-size:.92rem"><?= e(setting('about_text','Authentic Bangladeshi fashion, delivered nationwide.')) ?></p>
      <div class="socials">
        <a href="<?= e(setting('facebook','#')) ?>" aria-label="Facebook" target="_blank" rel="noopener">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="#fff"><path d="M13 22v-8h3l1-4h-4V7.5c0-1.1.4-2 2-2h2V2.2C16.6 2.1 15.4 2 14 2c-3 0-5 1.8-5 5v3H6v4h3v8z"/></svg>
        </a>
        <a href="<?= e(setting('instagram','#')) ?>" aria-label="Instagram" target="_blank" rel="noopener">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="#fff" stroke="none"/></svg>
        </a>
      </div>
    </div>
    <div>
      <h4>Shop</h4>
      <?php foreach (array_slice(get_categories(),0,6) as $c): ?>
        <a href="<?= BASE_URL ?>/shop.php?category=<?= e($c['slug']) ?>"><?= e($c['name']) ?></a>
      <?php endforeach; ?>
    </div>
    <div>
      <h4>Help</h4>
      <a href="<?= BASE_URL ?>/track-order.php">Track Order</a>
      <a href="<?= BASE_URL ?>/contact.php">Contact Us</a>
      <a href="<?= BASE_URL ?>/login.php">My Account</a>
      <a href="<?= BASE_URL ?>/cart.php">Shopping Cart</a>
    </div>
    <div>
      <h4>Get in touch</h4>
      <p style="font-size:.92rem"><?= e(setting('site_address','Dhaka, Bangladesh')) ?></p>
      <p style="font-size:.92rem;margin-top:6px"><?= e(setting('site_phone','')) ?></p>
      <form class="newsletter" action="<?= BASE_URL ?>/contact.php" method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="newsletter" value="1">
        <input type="hidden" name="subject" value="Newsletter signup">
        <input type="email" name="email" placeholder="Your email" aria-label="Email" required>
        <button class="btn btn-gold btn-sm" type="submit">Join</button>
      </form>
    </div>
  </div>
  <div class="footer-bottom">
    <div class="container">
      &copy; <?= date('Y') ?> <?= e(SITE_NAME) ?>. All rights reserved. • Made in Bangladesh 🇧🇩
      &nbsp;•&nbsp; <a href="<?= BASE_URL ?>/admin/login.php" style="display:inline">Admin Login</a>
    </div>
  </div>
</footer>

<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
