<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_admin();
require_post();
csrf_validate();

$id = (int)($_POST['id'] ?? 0);

$stmt = db()->prepare('SELECT image_filename FROM items WHERE id=?');
$stmt->execute([$id]);
$row = $stmt->fetch();
if ($row && $row['image_filename']) {
  $path = UPLOAD_DIR_ITEMS . '/' . $row['image_filename'];
  if (is_file($path)) @unlink($path);
}

$del = db()->prepare('DELETE FROM items WHERE id=?');
$del->execute([$id]);

flash_set('success', 'Item deleted.');
redirect('/admin/items.php');