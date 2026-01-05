<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_login();
$u = current_user();

$stmt = db()->prepare('SELECT * FROM transactions WHERE user_id=? ORDER BY id DESC');
$stmt->execute([(int)$u['id']]);
$rows = $stmt->fetchAll();

include __DIR__ . '/../_partials/header.php';
?>
<div class="card">
  <h2>My Transactions</h2>

  <?php if (!$rows): ?>
    <p>No transactions yet.</p>
    <a class="btn-nav" href="/vehicles.php">Browse</a>
  <?php else: ?>
    <div style="overflow-x:auto;">
      <table style="width:100%;border-collapse:collapse;">
        <thead>
          <tr style="background:#6c3cf0;color:#fff;">
            <th style="padding:10px;text-align:left;">Date & Time</th>
            <th style="padding:10px;text-align:right;">Transaction ID</th>
            <th style="padding:10px;text-align:left;">Payment Ref</th>
            <th style="padding:10px;text-align:right;">Total (RM)</th>
            <th style="padding:10px;">Receipt</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $r): ?>
            <tr style="border-bottom:1px solid #eee;">
              <td data-label="Date"><?= e($r['created_at']) ?></td>
              <td data-label="TX ID" style="text-align:right;"><?= e((string)$r['id']) ?></td>
              <td data-label="Ref"><?= e($r['payment_ref']) ?></td>
              <td data-label="Total" style="text-align:right;"><?= e(number_format((float)$r['total_amount'],2)) ?></td>
              <td data-label="Receipt">
                <a class="btn-nav" href="/receipt.php?id=<?= e((string)$r['id']) ?>" style="background:#10b981;">View</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
<?php include __DIR__ . '/../_partials/footer.php'; ?>