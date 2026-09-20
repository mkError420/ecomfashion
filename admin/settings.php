<?php
$activeKey = 'settings';
$adminTitle = 'Settings';
require_once __DIR__ . '/includes/header.php';

$fields = [
    'site_name'        => ['label'=>'Site name','type'=>'text'],
    'tagline'          => ['label'=>'Tagline','type'=>'text'],
    'shipping_cost'    => ['label'=>'Shipping cost (৳)','type'=>'number','step'=>'0.01'],
    'free_shipping_over'=> ['label'=>'Free shipping over (৳)','type'=>'number','step'=>'0.01'],
    'site_phone'       => ['label'=>'Phone','type'=>'text'],
    'site_email'       => ['label'=>'Email','type'=>'email'],
    'site_address'     => ['label'=>'Address','type'=>'text'],
    'facebook'         => ['label'=>'Facebook URL','type'=>'text'],
    'instagram'        => ['label'=>'Instagram URL','type'=>'text'],
    'about_text'       => ['label'=>'About text','type'=>'textarea'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $up = db()->prepare('INSERT INTO settings (setting_key,setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)');
    foreach (array_keys($fields) as $k) {
        $up->execute([$k, trim((string)($_POST[$k] ?? ''))]);
    }
    flash_set('success','Settings saved.');
    redirect('admin/settings.php');
}
?>

<div class="panel" style="max-width:820px">
  <div class="panel-head"><h2>Store Settings</h2></div>
  <div class="panel-body">
    <form method="post">
      <?= csrf_field() ?>
      <div class="form-grid">
        <?php foreach ($fields as $key=>$meta): ?>
          <?php if ($meta['type']==='textarea'): ?>
            <div class="field" style="grid-column:1/-1"><label><?= e($meta['label']) ?></label><textarea name="<?= e($key) ?>"><?= e(setting($key)) ?></textarea></div>
          <?php else: ?>
            <div class="field"><label><?= e($meta['label']) ?></label><input type="<?= e($meta['type']) ?>" <?= isset($meta['step'])?'step="'.$meta['step'].'"':'' ?> name="<?= e($key) ?>" value="<?= e(setting($key)) ?>"></div>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
      <button class="btn btn-primary" type="submit">Save Settings</button>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
