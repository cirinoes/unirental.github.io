<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);

$stmt = db()->prepare('SELECT * FROM items WHERE id=?');
$stmt->execute([$id]);
$item = $stmt->fetch();
if (!$item) { http_response_code(404); exit('Not found'); }

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
  csrf_validate();
  $name = trim($_POST['name'] ?? '');
  $category = trim($_POST['category'] ?? 'General') ?: 'General';
  $rate = (float)($_POST['daily_rate'] ?? 0);

  if ($name === '' || $rate < 0) {
    flash_set('danger', 'Invalid input.');
    redirect('/admin/item_edit.php?id=' . urlencode((string)$id));
  }

  $up = db()->prepare('UPDATE items SET name=?, category=?, daily_rate=? WHERE id=?');
  $up->execute([$name, $category, $rate, $id]);
  flash_set('success', 'Item updated.');
  redirect('/admin/items.php');
}

include __DIR__ . '/../_partials/header.php';
?>
<div class="card" style="max-width:560px;">
  <h2>Edit Item</h2>
  <form method="post">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <label>Name</label>
    <input name="name" value="<?= e($item['name']) ?>" required style="width:100%;padding:8px;margin:6px 0 12px;">
    <label>Category</label>
    <input name="category" value="<?= e($item['category']) ?>" required style="width:100%;padding:8px;margin:6px 0 12px;">
    <label>SKU (fixed)</label>
    <input value="<?= e($item['sku']) ?>" readonly style="width:100%;padding:8px;margin:6px 0 12px;">
    <label>Price (RM/day)</label>
    <input type="number" step="0.01" name="daily_rate" value="<?= e((string)$item['daily_rate']) ?>" required style="width:100%;padding:8px;margin:6px 0 12px;">
    <button class="btn-nav" type="submit" style="background:#10b981;">Save</button>
    <a class="btn-nav" href="/admin/items.php">Cancel</a>
  </form>
</div>
<?php include __DIR__ . '/../_partials/footer.php'; ?>