<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_admin();

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
  csrf_validate();

  $name = trim($_POST['name'] ?? '');
  $category = trim($_POST['category'] ?? 'General') ?: 'General';
  $sku = strtoupper(trim($_POST['sku'] ?? ''));
  $rate = (float)($_POST['daily_rate'] ?? 0);

  if ($name === '' || $sku === '' || $rate < 0) {
    flash_set('danger', 'Invalid input.');
    redirect('/admin/item_add.php');
  }

  // SKU whitelist to avoid path tricks
  if (!preg_match('/^[A-Z0-9_-]{2,40}$/', $sku)) {
    flash_set('danger', 'SKU must be A-Z, 0-9, underscore or dash only.');
    redirect('/admin/item_add.php');
  }

  if (empty($_FILES['image']['name'])) {
    flash_set('danger', 'Image required (JPG).');
    redirect('/admin/item_add.php');
  }

  $file = $_FILES['image'];
  if ($file['size'] > 2 * 1024 * 1024) {
    flash_set('danger', 'Image must be under 2MB.');
    redirect('/admin/item_add.php');
  }

  $finfo = new finfo(FILEINFO_MIME_TYPE);
  $mime = $finfo->file($file['tmp_name']);
  if (!in_array($mime, ['image/jpeg'], true)) {
    flash_set('danger', 'Only JPG images allowed.');
    redirect('/admin/item_add.php');
  }

  $filename = $sku . '.jpg';
  $dest = UPLOAD_DIR_ITEMS . '/' . $filename;
  if (!move_uploaded_file($file['tmp_name'], $dest)) {
    flash_set('danger', 'Failed to save image.');
    redirect('/admin/item_add.php');
  }

  try {
    $ins = db()->prepare('INSERT INTO items (name, category, sku, daily_rate, image_filename) VALUES (?,?,?,?,?)');
    $ins->execute([$name, $category, $sku, $rate, $filename]);
    flash_set('success', 'Item added.');
    redirect('/admin/items.php');
  } catch (Throwable $e) {
    @unlink($dest);
    flash_set('danger', 'SKU already exists.');
    redirect('/admin/item_add.php');
  }
}

include __DIR__ . '/../_partials/header.php';
?>
<div class="card" style="max-width:560px;">
  <h2>Add Item</h2>
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <label>Name</label>
    <input name="name" required style="width:100%;padding:8px;margin:6px 0 12px;">
    <label>Category</label>
    <input name="category" value="General" required style="width:100%;padding:8px;margin:6px 0 12px;">
    <label>SKU (Plate)</label>
    <input name="sku" required style="width:100%;padding:8px;margin:6px 0 12px;">
    <label>Price (RM/day)</label>
    <input type="number" step="0.01" name="daily_rate" required style="width:100%;padding:8px;margin:6px 0 12px;">
    <label>Image (JPG, max 2MB)</label>
    <input type="file" name="image" accept=".jpg,.jpeg" required style="width:100%;margin:6px 0 12px;">
    <button class="btn-nav" type="submit" style="background:#10b981;">Save</button>
    <a class="btn-nav" href="/admin/items.php">Cancel</a>
  </form>
</div>
<?php include __DIR__ . '/../_partials/footer.php'; ?>