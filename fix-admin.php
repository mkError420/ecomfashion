<?php
// One-time admin repair tool. Uses the site's own DB connection.
// Open:  https://your-domain/fix-admin.php?key=rongdhonu-fix-2026
// DELETE THIS FILE FROM THE SERVER IMMEDIATELY AFTER USE.
if (($_GET['key'] ?? '') !== 'rongdhonu-fix-2026') {
    http_response_code(404);
    exit('Not found');
}
require __DIR__ . '/config/db.php';

$email = 'admin@rongdhonu.com';
$pass  = 'admin123';
$hash  = password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12]);

$pdo = db();
$out = [];
$out[] = 'DB connected: ' . DB_NAME . ' @ ' . DB_HOST;

$rows = $pdo->query('SELECT id, email, password_hash FROM admins')->fetchAll();
$out[] = 'admins rows found: ' . count($rows);
foreach ($rows as $r) {
    $out[] = '  id=' . $r['id']
        . ' | email=' . $r['email']
        . ' | hash_len=' . strlen($r['password_hash'])
        . ' | verifies_admin123=' . (password_verify($pass, $r['password_hash']) ? 'YES' : 'NO');
}

$pdo->prepare('UPDATE admins SET password_hash = ? WHERE email = ?')->execute([$hash, $email]);
$chk = $pdo->prepare('SELECT id FROM admins WHERE email = ?');
$chk->execute([$email]);
if ($chk->fetchColumn()) {
    $out[] = 'action: updated existing admin row';
} else {
    $pdo->prepare('INSERT INTO admins (name, email, password_hash) VALUES (?,?,?)')
        ->execute(['Store Admin', $email, $hash]);
    $out[] = 'action: inserted new admin row';
}

$stmt = $pdo->prepare('SELECT * FROM admins WHERE email = ? LIMIT 1');
$stmt->execute([$email]);
$admin = $stmt->fetch();
$out[] = 'after fix: email=' . ($admin['email'] ?? 'MISSING')
    . ' | verifies_admin123=' . ($admin && password_verify($pass, $admin['password_hash']) ? 'YES' : 'NO');

echo '<pre style="font-family:monospace;background:#f4f1ea;padding:16px;border:1px solid #ddd">'
    . htmlspecialchars(implode("\n", $out)) . '</pre>';
echo '<p><strong>Next: DELETE fix-admin.php from the server, then log in at /admin/login.php with '
    . htmlspecialchars($email) . ' / ' . htmlspecialchars($pass) . '</strong></p>';
