<?php
$activeKey = 'products';
$adminTitle = 'Products';
require_once __DIR__ . '/includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if (isset($_POST['delete'])) {
        $id = (int)$_POST['delete'];
        db()->prepare('DELETE FROM products WHERE id=?')->execute([$id]);
        flash_set('success', 'Product deleted.');
    }
    redirect('admin/products.php');
}

$q = trim($_GET['q'] ?? '');
$catFilter = (int)($_GET['cat'] ?? 0);
$where = []; $params = [];
if ($q !== '') { $where[] = 'p.name LIKE ?'; $params[] = "%$q%"; }
if ($catFilter) { $where[] = 'p.category_id = ?'; $params[] = $catFilter; }
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$stmt = db()->prepare("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id=p.category_id $whereSql ORDER BY p.created_at DESC");
$stmt->execute($params);
$products = $stmt->fetchAll();
$categories = get_category_tree();
?>

<div class="panel">
  <div class="panel-head">
    <h2>All Products (<?= count($products) ?>)</h2>
    <div style="display:flex;gap:10px;flex-wrap:wrap">
      <form method="get" style="display:flex;gap:8px">
        <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search…" style="padding:8px 12px;border:1px solid var(--line);border-radius:8px">
        <select name="cat" style="padding:8px 12px;border:1px solid var(--line);border-radius:8px">
          <option value="0">All categories</option>
          <?php foreach ($categories as $parentCat): ?>
            <option value="<?= (int)$parentCat['category']['id'] ?>" <?= $catFilter===(int)$parentCat['category']['id']?'selected':'' ?>><?= e($parentCat['category']['name']) ?></option>
            <?php foreach ($parentCat['children'] as $childCat): ?>
              <option value="<?= (int)$childCat['id'] ?>" <?= $catFilter===(int)$childCat['id']?'selected':'' ?>>— <?= e($childCat['name']) ?></option>
            <?php endforeach; ?>
          <?php endforeach; ?>
        </select>
        <button class="btn btn-ghost btn-sm" type="submit">Filter</button>
      </form>
      <a class="btn btn-primary btn-sm" href="<?= BASE_URL ?>/admin/product-edit.php">+ Add Product</a>
    </div>
  </div>
  <div style="overflow-x:auto">
  <table class="data">
    <thead><tr><th></th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Featured</th><th>Actions</th></tr></thead>
    <tbody>
    <?php if (empty($products)): ?><tr><td colspan="7" class="muted">No products found.</td></tr><?php endif; ?>
    <?php foreach ($products as $p): $sale=(float)$p['sale_price']>0; ?>
      <tr>
        <td data-label=""><img class="thumb" src="<?= e(product_image_url($p['image'])) ?>" alt=""></td>
        <td data-label="Name"><strong><?= e($p['name']) ?></strong></td>
        <td data-label="Category"><?= e($p['category_name'] ?? '—') ?></td>
        <td data-label="Price" class="nowrap">
          <?= price(effective_price($p)) ?>
          <?php if ($sale): ?><br><s class="muted" style="font-size:.78rem"><?= price($p['price']) ?></s><?php endif; ?>
        </td>
        <td data-label="Stock"><span class="pill <?= (int)$p['stock']===0?'pill-cancelled':((int)$p['stock']<=5?'pill-pending':'pill-delivered') ?>"><?= (int)$p['stock'] ?></span></td>
        <td data-label="Featured"><span class="pill <?= (int)$p['featured']?'pill-yes':'pill-no' ?>"><?= (int)$p['featured']?'Yes':'No' ?></span></td>
        <td data-label="Actions">
          <div class="actions">
            <a class="btn btn-ghost btn-sm" href="<?= BASE_URL ?>/admin/product-edit.php?id=<?= (int)$p['id'] ?>">Edit</a>
            <form method="post" onsubmit="return confirm('Delete this product?')"><?= csrf_field() ?><button class="btn btn-danger btn-sm" name="delete" value="<?= (int)$p['id'] ?>" type="submit">Delete</button></form>
          </div>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
