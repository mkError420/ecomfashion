<?php
$activeKey = 'products';
require_once __DIR__ . '/../config/db.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);
$isEdit = $id > 0;
$product = $isEdit ? get_product($id) : null;
if ($isEdit && !$product) { flash_set('error','Product not found.'); redirect('admin/products.php'); }
$adminTitle = $isEdit ? 'Edit Product' : 'Add Product';

$errors = [];
$old = [
    'category_id' => $product['category_id'] ?? '',
    'name'        => $product['name'] ?? '',
    'slug'        => $product['slug'] ?? '',
    'description' => $product['description'] ?? '',
    'price'       => $product['price'] ?? '',
    'sale_price'  => $product['sale_price'] ?? '',
    'stock'       => $product['stock'] ?? 0,
    'featured'    => $product['featured'] ?? 0,
    'image'       => $product['image'] ?? null,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    foreach (['category_id','name','slug','description','price','sale_price','stock'] as $f) {
        $old[$f] = trim((string)($_POST[$f] ?? ''));
    }
    $old['featured'] = isset($_POST['featured']) ? 1 : 0;
    $old['slug'] = $old['slug'] !== '' ? slugify($old['slug']) : slugify($old['name']);

    if ($old['name'] === '') $errors[] = 'Product name is required.';
    if ($old['price'] === '' || !is_numeric($old['price'])) $errors[] = 'A valid price is required.';
    $sale = ($old['sale_price'] !== '' && is_numeric($old['sale_price'])) ? (float)$old['sale_price'] : null;

    try {
        $old['image'] = handle_image_upload('image', $old['image'] ?: null);
    } catch (Throwable $e) {
        $errors[] = $e->getMessage();
    }

    if (!$errors) {
        // Ensure unique slug
        $chk = db()->prepare('SELECT id FROM products WHERE slug=? AND id<>?');
        $chk->execute([$old['slug'], $id]);
        if ($chk->fetch()) { $old['slug'] .= '-' . substr(bin2hex(random_bytes(2)),0,4); }

        if ($isEdit) {
            $stmt = db()->prepare('UPDATE products SET category_id=?,name=?,slug=?,description=?,price=?,sale_price=?,stock=?,featured=?,image=? WHERE id=?');
            $stmt->execute([$old['category_id'] ?: null, $old['name'], $old['slug'], $old['description'], (float)$old['price'], $sale, (int)$old['stock'], $old['featured'], $old['image'], $id]);
            flash_set('success','Product updated.');
        } else {
            $stmt = db()->prepare('INSERT INTO products (category_id,name,slug,description,price,sale_price,stock,featured,image) VALUES (?,?,?,?,?,?,?,?,?)');
            $stmt->execute([$old['category_id'] ?: null, $old['name'], $old['slug'], $old['description'], (float)$old['price'], $sale, (int)$old['stock'], $old['featured'], $old['image']]);
            flash_set('success','Product added.');
        }
        redirect('admin/products.php');
    }
}

require_once __DIR__ . '/includes/header.php';
$categories = get_categories();
?>

<div class="panel">
  <div class="panel-head"><h2><?= $isEdit ? 'Edit Product' : 'Add New Product' ?></h2><a class="btn btn-ghost btn-sm" href="<?= BASE_URL ?>/admin/products.php">← Back</a></div>
  <div class="panel-body">
    <?php foreach ($errors as $err): ?><div class="alert alert-error"><?= e($err) ?></div><?php endforeach; ?>
    <form method="post" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <div class="form-grid">
        <div>
          <div class="field"><label>Product name *</label><input name="name" value="<?= e($old['name']) ?>" required></div>
          <div class="field"><label>Slug (optional)</label><input name="slug" value="<?= e($old['slug']) ?>" placeholder="auto-generated-from-name"></div>
          <div class="field"><label>Category</label>
            <select name="category_id">
              <option value="">— Uncategorized —</option>
              <?php foreach ($categories as $c): ?><option value="<?= (int)$c['id'] ?>" <?= (string)$old['category_id']===(string)$c['id']?'selected':'' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
            </select>
          </div>
          <div class="field"><label>Description</label><textarea name="description"><?= e($old['description']) ?></textarea></div>
        </div>
        <div>
          <div class="form-grid">
            <div class="field"><label>Price (<?= CURRENCY ?>) *</label><input type="number" step="0.01" min="0" name="price" value="<?= e((string)$old['price']) ?>" required></div>
            <div class="field"><label>Sale price</label><input type="number" step="0.01" min="0" name="sale_price" value="<?= e((string)$old['sale_price']) ?>" placeholder="0 = none"></div>
          </div>
          <div class="form-grid">
            <div class="field"><label>Stock</label><input type="number" min="0" name="stock" value="<?= e((string)$old['stock']) ?>"></div>
            <div class="field"><label>&nbsp;</label><label class="check"><input type="checkbox" name="featured" value="1" <?= (int)$old['featured']?'checked':'' ?>> Featured product</label></div>
          </div>
          <div class="field"><label>Product image</label><input type="file" name="image" accept="image/*"></div>
          <?php if ($old['image']): ?>
            <div><span class="muted" style="font-size:.82rem">Current image:</span><br><img class="img-preview" src="<?= e(product_image_url($old['image'])) ?>" alt="current"></div>
          <?php endif; ?>
        </div>
      </div>
      <div style="display:flex;gap:10px;margin-top:10px">
        <button class="btn btn-primary" type="submit"><?= $isEdit?'Update Product':'Add Product' ?></button>
        <a class="btn btn-ghost" href="<?= BASE_URL ?>/admin/products.php">Cancel</a>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
