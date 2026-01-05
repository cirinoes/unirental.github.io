<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_admin();

$rows = db()->query("SELECT id, full_name, email, role, created_at FROM users ORDER BY id DESC")->fetchAll();

include __DIR__ . '/../_partials/header.php';
?>
<div class="card">
  <h2>Registered Users (Read Only)</h2>
  <div style="overflow-x:auto;">
    <table style="width:100%;border-collapse:collapse;">
      <thead>
        <tr style="background:#6c3cf0;color:#fff;">
          <th style="padding:10px;">ID</th>
          <th style="padding:10px;text-align:left;">Full Name</th>
          <th style="padding:10px;text-align:left;">Email</th>
          <th style="padding:10px;">Role</th>
          <th style="padding:10px;text-align:left;">Created</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr style="border-bottom:1px solid #eee;">
            <td data-label="ID"><?= e((string)$r['id']) ?></td>
            <td data-label="Name"><?= e($r['full_name']) ?></td>
            <td data-label="Email"><?= e($r['email']) ?></td>
            <td data-label="Role"><?= e($r['role']) ?></td>
            <td data-label="Created"><?= e($r['created_at']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include __DIR__ . '/../_partials/footer.php'; ?>