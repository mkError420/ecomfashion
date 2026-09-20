<?php
$activeKey = 'categories';
$adminTitle = 'Categories';
require_once __DIR__ . '/includes/header.php';

$edit = null;
if (isset($_GET['edit'])) {
    $s = db()->prepare('SELECT * FROM categories WHERE id=?'); $s->execute([(int)$_GET['edit']]); $edit = $s->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim($_POST['name'] ?? '');
    $slug = slugify(trim($_POST['slug'] ?? '') ?: $name);
    $desc = trim($_POST['description'] ?? '');
    $parentId = (int)($_POST['parent_id'] ?? 0);
    $cid = (int)($_POST['id'] ?? 0);
    if ($name === '') {
        flash_set('error','Category name is required.');
    } elseif (isset($_POST['delete'])) {
        db()->prepare('DELETE FROM categories WHERE id=?')->execute([(int)$_POST['delete']]);
        flash_set('success','Category deleted.');
    } elseif ($cid > 0 && $parentId === $cid) {
        flash_set('error','A category cannot be its own parent.');
    } elseif ($cid > 0) {
        // Check if the selected parent is a descendant of this category (prevent circular reference)
        if ($parentId > 0) {
            $checkDescendant = $cid;
            while ($checkDescendant) {
                $stmt = db()->prepare('SELECT parent_id FROM categories WHERE id = ?');
                $stmt->execute([$checkDescendant]);
                $checkParent = $stmt->fetch();
                if (!$checkParent) break;
                if ($checkParent['parent_id'] == $parentId) {
                    flash_set('error','Cannot set parent to a sub-category of this category.');
                    redirect('admin/categories.php');
                }
                $checkDescendant = $checkParent['parent_id'];
            }
        }
        db()->prepare('UPDATE categories SET name=?,slug=?,description=?,parent_id=? WHERE id=?')->execute([$name,$slug,$desc ?: null,$parentId ?: null,$cid]);
        flash_set('success','Category updated.');
    } else {
        db()->prepare('INSERT INTO categories (name,slug,description,parent_id) VALUES (?,?,?,?)')->execute([$name,$slug,$desc ?: null,$parentId ?: null]);
        flash_set('success','Category added.');
    }
    redirect('admin/categories.php');
}

$categories = db()->query(
  'SELECT c.*, parent.name AS parent_name, (SELECT COUNT(*) FROM products p WHERE p.category_id=c.id) AS product_count
   FROM categories c LEFT JOIN categories parent ON parent.id=c.parent_id ORDER BY c.name')->fetchAll();

$parentCategories = db()->query('SELECT * FROM categories WHERE parent_id IS NULL ORDER BY name ASC')->fetchAll();

// Organize categories hierarchically for display
$hierarchicalCategories = [];
foreach ($categories as $cat) {
    if ($cat['parent_id'] === null) {
        $hierarchicalCategories[$cat['id']] = [
            'category' => $cat,
            'children' => []
        ];
    }
}
foreach ($categories as $cat) {
    if ($cat['parent_id'] !== null && isset($hierarchicalCategories[$cat['parent_id']])) {
        $hierarchicalCategories[$cat['parent_id']]['children'][] = $cat;
    }
}
?>

<div class="dash-grid" style="grid-template-columns:1fr 1.6fr">
  <div class="panel">
    <div class="panel-head"><h2><?= $edit ? 'Edit Category' : 'Add Category' ?></h2></div>
    <div class="panel-body">
      <form method="post">
        <?= csrf_field() ?>
        <?php if ($edit): ?><input type="hidden" name="id" value="<?= (int)$edit['id'] ?>"><?php endif; ?>
        <div class="field"><label>Name *</label><input name="name" value="<?= e($edit['name'] ?? '') ?>" required></div>
        <div class="field"><label>Slug</label><input name="slug" value="<?= e($edit['slug'] ?? '') ?>" placeholder="auto"></div>
        <div class="field"><label>Parent Category</label>
          <select name="parent_id">
            <option value="">— None (Top-level) —</option>
            <?php foreach ($parentCategories as $pc): ?>
              <option value="<?= (int)$pc['id'] ?>" <?= (string)($edit['parent_id'] ?? '') === (string)$pc['id'] ? 'selected' : '' ?>><?= e($pc['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field"><label>Description</label><textarea name="description" style="min-height:80px"><?= e($edit['description'] ?? '') ?></textarea></div>
        <div style="display:flex;gap:10px">
          <button class="btn btn-primary" type="submit"><?= $edit?'Update':'Add' ?></button>
          <?php if ($edit): ?><a class="btn btn-ghost" href="<?= BASE_URL ?>/admin/categories.php">Cancel</a><?php endif; ?>
        </div>
      </form>
    </div>
  </div>

  <div class="panel">
    <div class="panel-head"><h2>All Categories (<?= count($categories) ?>)</h2></div>
    <div style="overflow-x:auto">
    <table class="data">
      <thead><tr><th>Name</th><th>Slug</th><th>Products</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($hierarchicalCategories as $parentData): ?>
        <tr style="background:var(--soft)">
          <td data-label="Name"><strong><?= e($parentData['category']['name']) ?></strong><br><span class="muted" style="font-size:.8rem"><?= e($parentData['category']['description']) ?></span></td>
          <td data-label="Slug"><code><?= e($parentData['category']['slug']) ?></code></td>
          <td data-label="Products"><?= (int)$parentData['category']['product_count'] ?></td>
          <td data-label="Actions">
            <div class="actions">
              <a class="btn btn-ghost btn-sm" href="?edit=<?= (int)$parentData['category']['id'] ?>">Edit</a>
              <form method="post" onsubmit="return confirm('Delete this category?')"><?= csrf_field() ?><button class="btn btn-danger btn-sm" name="delete" value="<?= (int)$parentData['category']['id'] ?>" type="submit">Delete</button></form>
            </div>
          </td>
        </tr>
        <?php foreach ($parentData['children'] as $child): ?>
          <tr>
            <td data-label="Name"> ↳ <strong><?= e($child['name']) ?></strong><br><span class="muted" style="font-size:.8rem"> <?= e($child['description']) ?></span></td>
            <td data-label="Slug"><code><?= e($child['slug']) ?></code></td>
            <td data-label="Products"><?= (int)$child['product_count'] ?></td>
            <td data-label="Actions">
              <div class="actions">
                <a class="btn btn-ghost btn-sm" href="?edit=<?= (int)$child['id'] ?>">Edit</a>
                <form method="post" onsubmit="return confirm('Delete this category?')"><?= csrf_field() ?><button class="btn btn-danger btn-sm" name="delete" value="<?= (int)$child['id'] ?>" type="submit">Delete</button></form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
