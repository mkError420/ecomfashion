<?php
require_once __DIR__ . '/config/db.php';
$pageTitle = 'Contact Us';
$activeNav = 'contact';

$old = ['name'=>'','email'=>'','subject'=>'','message'=>''];
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    if (isset($_POST['newsletter'])) {
        $email = trim($_POST['email'] ?? '');
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $stmt = db()->prepare('INSERT INTO contact_messages (name,email,subject,message) VALUES (?,?,?,?)');
            $stmt->execute(['Newsletter Subscriber', $email, 'Newsletter signup', 'Subscribed via footer newsletter form.']);
            flash_set('success', 'Thanks for subscribing!');
        } else {
            flash_set('error', 'Please enter a valid email.');
        }
        redirect('contact.php');
    }

    foreach ($old as $k=>$_) $old[$k] = trim($_POST[$k] ?? '');
    if ($old['name']==='') $errors[]='Name is required.';
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors[]='A valid email is required.';
    if ($old['message']==='') $errors[]='Message is required.';

    if (!$errors) {
        $stmt = db()->prepare('INSERT INTO contact_messages (name,email,subject,message) VALUES (?,?,?,?)');
        $stmt->execute([$old['name'], $old['email'], $old['subject'] ?: null, $old['message']]);
        flash_set('success', 'Thanks for reaching out! We\'ll get back to you soon.');
        redirect('contact.php');
    }
}
include __DIR__ . '/includes/header.php';
?>
<div class="page-head"><div class="container"><h1>Contact Us</h1><p>We'd love to hear from you</p></div></div>

<div class="container section" style="padding-top:30px">
  <div class="cart-layout" style="grid-template-columns:1fr 340px">
    <div class="form-card" style="max-width:none">
      <?php foreach ($errors as $err): ?><div class="alert alert-error"><?= e($err) ?></div><?php endforeach; ?>
      <form method="post">
        <?= csrf_field() ?>
        <div class="form-grid">
          <div class="field"><label>Name *</label><input name="name" value="<?= e($old['name']) ?>" required></div>
          <div class="field"><label>Email *</label><input type="email" name="email" value="<?= e($old['email']) ?>" required></div>
        </div>
        <div class="field"><label>Subject</label><input name="subject" value="<?= e($old['subject']) ?>"></div>
        <div class="field"><label>Message *</label><textarea name="message" required><?= e($old['message']) ?></textarea></div>
        <button class="btn btn-primary" type="submit">Send Message</button>
      </form>
    </div>
    <aside class="cart-summary">
      <h3 style="font-family:'Hind Siliguri',sans-serif;margin-bottom:14px">Get in touch</h3>
      <p style="margin-bottom:12px"><strong>Address</strong><br><span class="muted"><?= e(setting('site_address','Dhaka, Bangladesh')) ?></span></p>
      <p style="margin-bottom:12px"><strong>Phone</strong><br><span class="muted"><?= e(setting('site_phone','')) ?></span></p>
      <p style="margin-bottom:12px"><strong>Email</strong><br><span class="muted"><?= e(setting('site_email','')) ?></span></p>
      <p><strong>Hours</strong><br><span class="muted">Sat–Thu: 10am – 8pm</span></p>
    </aside>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
