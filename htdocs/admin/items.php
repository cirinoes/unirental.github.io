<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_admin();

$rows = db()->query("SELECT * FROM items ORDER BY id DESC")->fetchAll();

include __DIR__ . '/../_partials/header.php';
?>
<div class="card">
  <h2>Manage Items</h2>
  <p><a class="btn-nav" href="/admin/item_add.php" style="background:#10b981;">Add Item</a></p>

  <div style="overflow-x:auto;">
    <table style="width:100%;border-collapse:collapse;">
      <thead>
        <tr style="background:#6c3cf0;color:#fff;">
          <th style="padding:10px;">ID</th>
          <th style="padding:10px;text-align:left;">Name</th>
          <th style="padding:10px;text-align:left;">Category</th>
          <th style="padding:10px;">SKU</th>
          <th style="padding:10px;text-align:right;">Price (RM/day)</th>
          <th style="padding:10px;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr style="border-bottom:1px solid #eee;">
            <td data-label="ID"><?= e((string)$r['id']) ?></td>
            <td data-label="Name"><?= e($r['name']) ?></td>
            <td data-label="Category"><?= e($r['category']) ?></td>
            <td data-label="SKU"><?= e($r['sku']) ?></td>
            <td data-label="Price" style="text-align:right;"><?= e(number_format((float)$r['daily_rate'],2)) ?></td>
            <td data-label="Actions">
              <a class="btn-nav" href="/admin/item_edit.php?id=<?= e((string)$r['id']) ?>">Edit</a>
              <form method="post" action="/admin/item_delete.php" style="display:inline;">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="id" value="<?= e((string)$r['id']) ?>">
                <button class="btn-nav" type="submit" style="background:#ef4444;">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include __DIR__ . '/../_partials/footer.php'; ?>