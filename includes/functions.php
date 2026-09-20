<?php
// Shared helper functions: security, flash, auth, cart, data access, formatting.
declare(strict_types=1);

/* ---------------- PHP 7.4 compatibility polyfills ---------------- */
// These exist natively in PHP 8; define them only when missing so the app
// also runs on older PHP (e.g. some shared hosts still on 7.4).
if (!function_exists('str_starts_with')) {
    function str_starts_with(string $haystack, string $needle): bool
    {
        return $needle === '' || strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}
if (!function_exists('str_ends_with')) {
    function str_ends_with(string $haystack, string $needle): bool
    {
        return $needle === '' || substr($haystack, -strlen($needle)) === $needle;
    }
}
if (!function_exists('str_contains')) {
    function str_contains(string $haystack, string $needle): bool
    {
        return $needle === '' || strpos($haystack, $needle) !== false;
    }
}

/* ---------------- Escaping & formatting ---------------- */

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void
{
    $url = str_starts_with($path, 'http') ? $path : BASE_URL . '/' . ltrim($path, '/');
    header('Location: ' . $url);
    exit;
}

function price(float|int|string $amount): string
{
    return CURRENCY . number_format((float)$amount, 2);
}

function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-') ?: 'item';
}

/* ---------------- CSRF protection ---------------- */

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals(csrf_token(), $token)) {
        http_response_code(419);
        exit('Invalid security token. Please go back and try again.');
    }
}

/* ---------------- Flash messages ---------------- */

function flash_set(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function flash_render(): string
{
    if (empty($_SESSION['flash'])) {
        return '';
    }
    $out = '';
    foreach ($_SESSION['flash'] as $f) {
        $out .= '<div class="alert alert-' . e($f['type']) . '">' . e($f['message']) . '</div>';
    }
    unset($_SESSION['flash']);
    return $out;
}

/* ---------------- Customer auth ---------------- */

function current_customer(): ?array
{
    if (empty($_SESSION['customer_id'])) {
        return null;
    }
    static $customer = null;
    if ($customer !== null) {
        return $customer;
    }
    $stmt = db()->prepare('SELECT * FROM customers WHERE id = ? LIMIT 1');
    $stmt->execute([$_SESSION['customer_id']]);
    $customer = $stmt->fetch() ?: null;
    return $customer;
}

function is_logged_in(): bool
{
    return current_customer() !== null;
}

function require_login(): void
{
    if (!is_logged_in()) {
        flash_set('warning', 'Please log in to continue.');
        redirect('login.php');
    }
}

function customer_login(string $email, string $password): bool
{
    $stmt = db()->prepare('SELECT * FROM customers WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $customer = $stmt->fetch();
    if ($customer && password_verify($password, $customer['password_hash'])) {
        // false = keep old session file; deleting it (true) fails to persist on some shared hosts.
        session_regenerate_id(false);
        $_SESSION['customer_id'] = (int)$customer['id'];
        return true;
    }
    return false;
}

function customer_logout(): void
{
    unset($_SESSION['customer_id']);
}

/* ---------------- Admin auth ---------------- */

function current_admin(): ?array
{
    if (empty($_SESSION['admin_id'])) {
        return null;
    }
    static $admin = null;
    if ($admin !== null) {
        return $admin;
    }
    $stmt = db()->prepare('SELECT * FROM admins WHERE id = ? LIMIT 1');
    $stmt->execute([$_SESSION['admin_id']]);
    $admin = $stmt->fetch() ?: null;
    return $admin;
}

function require_admin(): void
{
    if (current_admin() === null) {
        redirect('admin/login.php');
    }
}

function admin_login(string $email, string $password): bool
{
    $stmt = db()->prepare('SELECT * FROM admins WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $admin = $stmt->fetch();
    if ($admin && password_verify($password, $admin['password_hash'])) {
        // false = keep old session file; deleting it (true) fails to persist on some shared hosts.
        session_regenerate_id(false);
        $_SESSION['admin_id'] = (int)$admin['id'];
        return true;
    }
    return false;
}

function admin_logout(): void
{
    unset($_SESSION['admin_id']);
}

/* ---------------- Cart (session based) ---------------- */

function cart(): array
{
    return $_SESSION['cart'] ?? [];
}

function cart_add(int $productId, int $qty = 1): void
{
    $cart = cart();
    $cart[$productId] = ($cart[$productId] ?? 0) + max(1, $qty);
    $_SESSION['cart'] = $cart;
}

function cart_update(int $productId, int $qty): void
{
    $cart = cart();
    if ($qty <= 0) {
        unset($cart[$productId]);
    } else {
        $cart[$productId] = $qty;
    }
    $_SESSION['cart'] = $cart;
}

function cart_remove(int $productId): void
{
    $cart = cart();
    unset($cart[$productId]);
    $_SESSION['cart'] = $cart;
}

function cart_clear(): void
{
    unset($_SESSION['cart']);
}

function cart_count(): int
{
    return array_sum(cart());
}

/**
 * Build detailed cart lines joined with live product data.
 * @return array{lines: array, subtotal: float}
 */
function cart_details(): array
{
    $cart = cart();
    if (empty($cart)) {
        return ['lines' => [], 'subtotal' => 0.0];
    }
    $ids = array_map('intval', array_keys($cart));
    $in = implode(',', array_fill(0, count($ids), '?'));
    $stmt = db()->prepare("SELECT * FROM products WHERE id IN ($in)");
    $stmt->execute($ids);
    $products = $stmt->fetchAll();

    $lines = [];
    $subtotal = 0.0;
    foreach ($products as $p) {
        $qty = $cart[(int)$p['id']];
        $unit = effective_price($p);
        $lineTotal = $unit * $qty;
        $subtotal += $lineTotal;
        $lines[] = [
            'product' => $p,
            'qty'     => $qty,
            'unit'    => $unit,
            'total'   => $lineTotal,
        ];
    }
    return ['lines' => $lines, 'subtotal' => $subtotal];
}

function effective_price(array $product): float
{
    $sale = (float)($product['sale_price'] ?? 0);
    return $sale > 0 ? $sale : (float)$product['price'];
}

/* ---------------- Settings ---------------- */

function setting(string $key, string $default = ''): string
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        try {
            foreach (db()->query('SELECT setting_key, setting_value FROM settings') as $row) {
                $cache[$row['setting_key']] = $row['setting_value'];
            }
        } catch (Throwable $t) {
            // settings table may be unavailable during setup
        }
    }
    return $cache[$key] ?? $default;
}

function shipping_cost(): float
{
    return (float)setting('shipping_cost', '60');
}

/* ---------------- Data access ---------------- */

function get_categories(): array
{
    return db()->query('SELECT * FROM categories ORDER BY name ASC')->fetchAll();
}

function get_category_by_slug(string $slug): ?array
{
    $stmt = db()->prepare('SELECT * FROM categories WHERE slug = ? LIMIT 1');
    $stmt->execute([$slug]);
    return $stmt->fetch() ?: null;
}

function get_product_by_slug(string $slug): ?array
{
    $stmt = db()->prepare(
        'SELECT p.*, c.name AS category_name, c.slug AS category_slug
         FROM products p LEFT JOIN categories c ON c.id = p.category_id
         WHERE p.slug = ? LIMIT 1'
    );
    $stmt->execute([$slug]);
    return $stmt->fetch() ?: null;
}

function get_product(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM products WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

function product_image_url(?string $image): string
{
    if (!$image) {
        return BASE_URL . '/assets/img/placeholder.svg';
    }
    if (str_starts_with($image, 'http')) {
        return $image;
    }
    // Bundled assets are stored with a path (e.g. "assets/img/saree.svg");
    // user uploads are stored in /uploads by bare filename.
    if (str_contains($image, '/')) {
        return BASE_URL . '/' . ltrim($image, '/');
    }
    return UPLOAD_URL . '/' . ltrim($image, '/');
}

/* ---------------- Image upload ---------------- */

function handle_image_upload(string $field, ?string $existing = null): ?string
{
    if (empty($_FILES[$field]['name']) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return $existing;
    }
    if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Image upload failed.');
    }
    $file = $_FILES[$field];
    if ($file['size'] > 4 * 1024 * 1024) {
        throw new RuntimeException('Image too large (max 4MB).');
    }
    $mime = mime_content_type($file['tmp_name']);
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Only JPG, PNG, WEBP or GIF images are allowed.');
    }
    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0775, true);
    }
    $name = bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
    if (!move_uploaded_file($file['tmp_name'], UPLOAD_DIR . '/' . $name)) {
        throw new RuntimeException('Could not save uploaded image.');
    }
    return $name;
}
