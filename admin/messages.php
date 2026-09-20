<?php
$activeKey = 'messages';
$adminTitle = 'Messages';
require_once __DIR__ . '/includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = (int)($_POST['id'] ?? 0);
    if (isset($_POST['delete'])) { db()->prepare('DELETE FROM contact_messages WHERE id=?')->execute([$id]); flash_set('success','Message deleted.'); }
    elseif (isset($_POST['toggle'])) { db()->prepare('UPDATE contact_messages SET is_read = 1 - is_read WHERE id=?')->execute([$id]); }
    redirect('admin/messages.php');
}

$messages = db()->query('SELECT * FROM contact_messages ORDER BY is_read ASC, created_at DESC')->fetchAll();
$view = null;
if (isset($_GET['view'])) {
    $s = db()->prepare('SELECT * FROM contact_messages WHERE id=?'); $s->execute([(int)$_GET['view']]); $view = $s->fetch();
    if ($view && !(int)$view['is_read']) { db()->prepare('UPDATE contact_messages SET is_read=1 WHERE id=?')->execute([(int)$view['id']]); $view['is_read']=1; }
}
?>

<div class="dash-grid">
  <div class="panel">
    <div class="panel-head"><h2>Inbox (<?= count($messages) ?>)</h2></div>
    <div style="overflow-x:auto">
    <table class="data">
      <thead><tr><th>From</th><th>Subject</th><th>Date</th><th>Actions</th></tr></thead>
      <tbody>
      <?php if (empty($messages)): ?><tr><td colspan="4" class="muted">No messages.</td></tr><?php endif; ?>
      <?php foreach ($messages as $m): ?>
        <tr style="<?= (int)$m['is_read']?'':'font-weight:600' ?>">
          <td data-label="From"><?= e($m['name']) ?><br><span class="muted" style="font-size:.8rem"><?= e($m['email']) ?></span></td>
          <td data-label="Subject"><?= e($m['subject'] ?: '(no subject)') ?><?php if(!(int)$m['is_read']): ?> <span class="pill pill-processing">New</span><?php endif; ?></td>
          <td data-label="Date" class="muted nowrap"><?= e(date('d M Y', strtotime($m['created_at']))) ?></td>
          <td data-label="Actions">
            <div class="actions">
              <a class="btn btn-ghost btn-sm" href="?view=<?= (int)$m['id'] ?>">View</a>
              <form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$m['id'] ?>"><button class="btn btn-danger btn-sm" name="delete" value="1" type="submit" onclick="return confirm('Delete message?')">Del</button></form>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  </div>

  <div class="panel">
    <div class="panel-head"><h2><?= $view ? 'Message' : 'Select a message' ?></h2>
      <?php if ($view): ?><form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$view['id'] ?>"><button class="btn btn-ghost btn-sm" name="toggle" value="1" type="submit">Mark <?= (int)$view['is_read']?'unread':'read' ?></button></form><?php endif; ?>
    </div>
    <div class="panel-body">
      <?php if (!$view): ?><p class="muted">Choose a message from the inbox to read it here.</p>
      <?php else: ?>
        <p><strong><?= e($view['name']) ?></strong> <span class="muted">&lt;<?= e($view['email']) ?>&gt;</span></p>
        <p class="muted" style="font-size:.82rem;margin-bottom:12px"><?= e(date('d M Y, h:i A', strtotime($view['created_at']))) ?> • Subject: <?= e($view['subject'] ?: '—') ?></p>
        <hr style="border:0;border-top:1px solid var(--line);margin:14px 0">
        <p><?= nl2br(e($view['message'])) ?></p>
        <a class="btn btn-primary btn-sm" style="margin-top:16px" href="mailto:<?= e($view['email']) ?>">Reply via email</a>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
