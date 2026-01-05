<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_admin();

$members = db()->query("SELECT COUNT(*) AS c FROM users WHERE role='member'")->fetch()['c'];
$items = db()->query("SELECT COUNT(*) AS c FROM items")->fetch()['c'];
$tx = db()->query("SELECT COUNT(*) AS c FROM transactions")->fetch()['c'];
$sales = db()->query("SELECT COALESCE(SUM(total_amount),0) AS s FROM transactions WHERE status='PAID'")->fetch()['s'];

include __DIR__ . '/../_partials/header.php';
?>
<div class="card">
  <h2>Administrator Dashboard</h2>
  <div style="display:flex;gap:8px;flex-wrap:wrap;">
    <a class="btn-nav" href="/admin/users.php">View Members</a>
    <a class="btn-nav" href="/admin/items.php">Manage Items</a>
    <a class="btn-nav" href="/admin/admin_transactions.php">Transaction Summary</a>
  </div>
  <hr style="margin:12px 0;">
  <p><b>Total Members:</b> <?= e((string)$members) ?></p>
  <p><b>Total Items:</b> <?= e((string)$items) ?></p>
  <p><b>Total Transactions:</b> <?= e((string)$tx) ?></p>
  <p><b>Total Sales (RM):</b> <?= e(number_format((float)$sales,2)) ?></p>
</div>
<?php include __DIR__ . '/../_partials/footer.php'; ?>