<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
$me = current_user();

$tx = db()->prepare('SELECT * FROM transactions WHERE id=?');
$tx->execute([$id]);
$txrow = $tx->fetch();
if (!$txrow) { http_response_code(404); exit('Receipt not found'); }

// Members can only view their own receipt; admin can view all
if (($me['role'] ?? '') !== 'admin' && (int)$txrow['user_id'] !== (int)$me['id']) {
  http_response_code(403); exit('Forbidden');
}

$uStmt = db()->prepare('SELECT id, email, full_name FROM users WHERE id=?');
$uStmt->execute([(int)$txrow['user_id']]);
$owner = $uStmt->fetch();

$items = db()->prepare(
  'SELECT ti.*, i.name
   FROM transaction_items ti
   JOIN items i ON i.id = ti.item_id
   WHERE ti.transaction_id=?
   ORDER BY ti.id'
);
$items->execute([$id]);
$rows = $items->fetchAll();

include __DIR__ . '/_partials/header.php';
?>
<div class="card">
  <h2>Payment Receipt</h2>
  <p><b>Transaction ID:</b> <?= e((string)$txrow['id']) ?></p>
  <p><b>Date & Time:</b> <?= e($txrow['created_at']) ?></p>
  <p><b>Status:</b> <?= e($txrow['status']) ?></p>
  <p><b>Payment:</b> <?= e($txrow['payment_method']) ?> | Ref: <?= e($txrow['payment_ref']) ?></p>
  <p><b>Member:</b> <?= e($owner['full_name']) ?> (<?= e($owner['email']) ?>)</p>

  <div style="overflow-x:auto;margin-top:12px;">
    <table style="width:100%;border-collapse:collapse;">
      <thead>
        <tr style="background:#6c3cf0;color:#fff;">
          <th style="padding:10px;text-align:left;">Item</th>
          <th style="padding:10px;text-align:right;">Unit (RM)</th>
          <th style="padding:10px;text-align:right;">Qty</th>
          <th style="padding:10px;text-align:right;">Subtotal (RM)</th>
          <th style="padding:10px;text-align:left;">Dates</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr style="border-bottom:1px solid #eee;">
          <td data-label="Item"><?= e($r['name']) ?></td>
          <td data-label="Unit" style="text-align:right;"><?= e(number_format((float)$r['unit_price'],2)) ?></td>
          <td data-label="Qty" style="text-align:right;"><?= e((string)$r['quantity']) ?></td>
          <td data-label="Subtotal" style="text-align:right;"><?= e(number_format((float)$r['subtotal'],2)) ?></td>
          <td data-label="Dates">
            <?php if ($r['start_date'] && $r['end_date']): ?>
              <?= e($r['start_date']) ?> → <?= e($r['end_date']) ?>
            <?php else: ?>-<?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div style="margin-top:12px;font-weight:900;text-align:right;">
    Total: RM <?= e(number_format((float)$txrow['total_amount'],2)) ?>
  </div>

  <div style="margin-top:12px;display:flex;gap:8px;flex-wrap:wrap;">
    <a class="btn-nav" href="/receipt_pdf.php?id=<?= e((string)$txrow['id']) ?>" style="background:#4a2bbd;">Download PDF</a>
    <a class="btn-nav" href="/member/transactions.php">My Transactions</a>
  </div>

  <p style="margin-top:10px;font-size:13px;">
    Email notification: <b><?= ((int)$txrow['email_sent'] === 1) ? 'Sent' : 'Not sent' ?></b>
  </p>
</div>
<?php include __DIR__ . '/_partials/footer.php'; ?>