<?php
require_once __DIR__ . '/config/db.php';
$activeNav = 'shop';

$categories = get_category_tree();
$q = trim($_GET['q'] ?? '');
$catSlug = $_GET['category'] ?? '';
$sort = $_GET['sort'] ?? 'newest';
$minP = (isset($_GET['min']) && $_GET['min'] !== '') ? (float)$_GET['min'] : null;
$maxP = (isset($_GET['max']) && $_GET['max'] !== '') ? (float)$_GET['max'] : null;
$onSale = isset($_GET['sale']) && $_GET['sale'] === '1';

$where = [];
$params = [];
if ($q !== '') { $where[] = '(p.name LIKE ? OR p.description LIKE ?)'; $params[] = "%$q%"; $params[] = "%$q%"; }
if ($catSlug !== '') {
    $selectedCat = get_category_by_slug($catSlug);
    if ($selectedCat) {
        if ($selectedCat['parent_id'] === null) {
            // Parent category selected - include all sub-categories
            $subCats = get_subcategories($selectedCat['id']);
            $catIds = [$selectedCat['id']];
            foreach ($subCats as $sub) {
                $catIds[] = $sub['id'];
            }
            $placeholders = implode(',', array_fill(0, count($catIds), '?'));
            $where[] = "p.category_id IN ($placeholders)";
            $params = array_merge($params, $catIds);
        } else {
            // Sub-category selected - only this category
            $where[] = 'c.slug = ?';
            $params[] = $catSlug;
        }
    }
}
if ($minP !== null) { $where[] = 'COALESCE(NULLIF(p.sale_price,0), p.price) >= ?'; $params[] = $minP; }
if ($maxP !== null) { $where[] = 'COALESCE(NULLIF(p.sale_price,0), p.price) <= ?'; $params[] = $maxP; }
if ($onSale) { $where[] = 'p.sale_price > 0'; }
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

switch ($sort) {
    case 'price_asc':  $orderBy = 'final_price ASC'; break;
    case 'price_desc': $orderBy = 'final_price DESC'; break;
    case 'name':       $orderBy = 'p.name ASC'; break;
    case 'popular':    $orderBy = 'p.featured DESC, p.created_at DESC'; break;
    default:           $orderBy = 'p.created_at DESC'; break;
}

$perPage = 12;
$page = max(1, (int)($_GET['page'] ?? 1));

// Count
$countSql = "SELECT COUNT(*) FROM products p LEFT JOIN categories c ON c.id=p.category_id $whereSql";
$cs = db()->prepare($countSql); $cs->execute($params);
$total = (int)$cs->fetchColumn();
$totalPages = max(1, (int)ceil($total / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$sql = "SELECT p.*, c.name AS category_name, c.slug AS category_slug,
               COALESCE(NULLIF(p.sale_price,0), p.price) AS final_price
        FROM products p LEFT JOIN categories c ON c.id=p.category_id
        $whereSql ORDER BY $orderBy LIMIT $perPage OFFSET $offset";
$stmt = db()->prepare($sql); $stmt->execute($params);
$products = $stmt->fetchAll();

$currentCat = $catSlug ? get_category_by_slug($catSlug) : null;
$pageTitle = $currentCat ? $currentCat['name'] : ($q !== '' ? "Search: $q" : 'Shop');

// Get parent category for breadcrumb if viewing a sub-category
$parentCat = null;
if ($currentCat && $currentCat['parent_id']) {
    $stmt = db()->prepare('SELECT * FROM categories WHERE id = ? LIMIT 1');
    $stmt->execute([$currentCat['parent_id']]);
    $parentCat = $stmt->fetch() ?: null;
}

function qs(array $overrides): string {
    $merged = array_merge($_GET, $overrides);
    return htmlspecialchars(http_build_query($merged), ENT_QUOTES);
}

include __DIR__ . '/includes/header.php';
?>

<div class="page-head">
  <div class="container">
    <h1><?= e($pageTitle) ?></h1>
    <p><?= $total ?> product<?= $total===1?'':'s' ?> found<?= $q!=='' ? ' for “'.e($q).'”' : '' ?></p>
  </div>
</div>

<div class="container section" style="padding-top:30px">
  <div class="breadcrumb">
    <a href="<?= BASE_URL ?>/index.php">Home</a><span>/</span>
    <a href="<?= BASE_URL ?>/shop.php">Shop</a>
    <?php if ($parentCat): ?><span>/</span><a href="<?= BASE_URL ?>/shop.php?category=<?= e($parentCat['slug']) ?>"><?= e($parentCat['name']) ?></a><?php endif; ?>
    <?php if ($currentCat): ?><span>/</span><?= e($currentCat['name']) ?><?php endif; ?>
  </div>

  <div class="shop-layout">
    <!-- Filters -->
    <aside class="filters" id="filters">
      <form method="get" action="<?= BASE_URL ?>/shop.php">
        <?php if ($q !== ''): ?><input type="hidden" name="q" value="<?= e($q) ?>"><?php endif; ?>
        <input type="hidden" name="sort" value="<?= e($sort) ?>">

        <div class="filter-group">
          <h4>Category</h4>
          <label><input type="radio" name="category" value="" <?= $catSlug===''?'checked':'' ?>> All</label>
          <?php foreach ($categories as $parentCat): ?>
            <label><input type="radio" name="category" value="<?= e($parentCat['category']['slug']) ?>" <?= $catSlug===$parentCat['category']['slug']?'checked':'' ?>> <?= e($parentCat['category']['name']) ?></label>
            <?php foreach ($parentCat['children'] as $childCat): ?>
              <label style="padding-left:20px"><input type="radio" name="category" value="<?= e($childCat['slug']) ?>" <?= $catSlug===$childCat['slug']?'checked':'' ?>> ↳ <?= e($childCat['name']) ?></label>
            <?php endforeach; ?>
          <?php endforeach; ?>
        </div>

        <div class="filter-group">
          <h4>Price (<?= CURRENCY ?>)</h4>
          <div class="price-inputs">
            <input type="number" name="min" min="0" placeholder="Min" value="<?= $minP!==null?e((string)$minP):'' ?>">
            <input type="number" name="max" min="0" placeholder="Max" value="<?= $maxP!==null?e((string)$maxP):'' ?>">
          </div>
        </div>

        <div class="filter-group">
          <label><input type="checkbox" name="sale" value="1" <?= $onSale?'checked':'' ?>> On sale only</label>
        </div>

        <button class="btn btn-primary btn-block" type="submit">Apply Filters</button>
        <a class="btn btn-ghost btn-block" style="margin-top:8px" href="<?= BASE_URL ?>/shop.php">Reset</a>
      </form>
    </aside>

    <!-- Results -->
    <div>
      <div class="shop-toolbar">
        <button class="btn btn-ghost btn-sm filter-toggle" type="button">☰ Filters</button>
        <form method="get" action="<?= BASE_URL ?>/shop.php" style="margin-left:auto">
          <?php foreach (['q'=>$q,'category'=>$catSlug,'min'=>$minP,'max'=>$maxP,'sale'=>$onSale?'1':''] as $k=>$v): ?>
            <?php if ($v!=='' && $v!==null): ?><input type="hidden" name="<?= e($k) ?>" value="<?= e((string)$v) ?>"><?php endif; ?>
          <?php endforeach; ?>
          <select name="sort" onchange="this.form.submit()">
            <option value="newest" <?= $sort==='newest'?'selected':'' ?>>Newest first</option>
            <option value="popular" <?= $sort==='popular'?'selected':'' ?>>Most popular</option>
            <option value="price_asc" <?= $sort==='price_asc'?'selected':'' ?>>Price: low to high</option>
            <option value="price_desc" <?= $sort==='price_desc'?'selected':'' ?>>Price: high to low</option>
            <option value="name" <?= $sort==='name'?'selected':'' ?>>Name: A–Z</option>
          </select>
        </form>
      </div>

      <?php if (empty($products)): ?>
        <div class="empty">
          <svg viewBox="0 0 24 24" fill="none" stroke-width="1.5"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4-4"/></svg>
          <h3>No products found</h3>
          <p>Try adjusting your filters or search terms.</p>
          <a class="btn btn-primary" style="margin-top:16px" href="<?= BASE_URL ?>/shop.php">Browse all</a>
        </div>
      <?php else: ?>
        <div class="grid">
          <?php foreach ($products as $p) include __DIR__ . '/includes/product-card.php'; ?>
        </div>

        <?php if ($totalPages > 1): ?>
          <div class="pagination">
            <?php for ($i=1; $i<=$totalPages; $i++): ?>
              <?php if ($i===$page): ?><span class="current"><?= $i ?></span>
              <?php else: ?><a href="?<?= qs(['page'=>$i]) ?>"><?= $i ?></a><?php endif; ?>
            <?php endfor; ?>
          </div>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
