<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_admin();

$period = strtolower(trim($_GET['period'] ?? 'daily'));
$date = trim($_GET['date'] ?? date('Y-m-d'));
if (!in_array($period, ['daily','weekly','monthly'], true)) $period = 'daily';

$today = new DateTime('today');

if ($period === 'weekly') {
  $start = (clone $today)->modify('-6 days');
  $end = (clone $today)->modify('+1 day');
  $label = 'Last 7 days';
} elseif ($period === 'monthly') {
  $start = new DateTime(date('Y-m-01'));
  $end = (clone $start)->modify('+1 month');
  $label = 'Current month';
} else {
  $d = DateTime::createFromFormat('Y-m-d', $date) ?: $today;
  $start = new DateTime($d->format('Y-m-d'));
  $end = (clone $start)->modify('+1 day');
  $label = 'Daily';
}

$start_s = $start->format('Y-m-d') . ' 00:00:00';
$end_s = $end->format('Y-m-d') . ' 00:00:00';

$stmt = db()->prepare(
  "SELECT t.*, u.email
   FROM transactions t
   JOIN users u ON u.id=t.user_id
   WHERE t.created_at >= ? AND t.created_at < ?
   ORDER BY t.id DESC"
);
$stmt->execute([$start_s, $end_s]);
$rows = $stmt->fetchAll();

$sum = db()->prepare(
  "SELECT COUNT(*) AS c, COALESCE(SUM(total_amount),0) AS s
   FROM transactions
   WHERE created_at >= ? AND created_at < ? AND status='PAID'"
);
$sum->execute([$start_s, $end_s]);
$summary = $sum->fetch();

include __DIR__ . '/../_partials/header.php';
?>
<div class="card">
  <h2>Transaction Summary</h2>

  <form method="get" style="display:flex;gap:8px;flex-wrap:wrap;align-items:end;">
    <div>
      <label>Period</label><br>
      <select name="period" style="padding:8px;">
        <option value="daily" <?= $period==='daily'?'selected':'' ?>>Daily</option>
        <option value="weekly" <?= $period==='weekly'?'selected':'' ?>>Weekly</option>
        <option value="monthly" <?= $period==='monthly'?'selected':'' ?>>Monthly</option>
      </select>
    </div>
    <div>
      <label>Date (Daily)</label><br>
      <input type="date" name="date" value="<?= e($date) ?>" style="padding:8px;">
    </div>
    <button class="btn-nav" type="submit">Apply</button>
    <a class="btn-nav" href="/admin/report_pdf.php?period=<?= e(urlencode($period)) ?>&date=<?= e(urlencode($date)) ?>" style="background:#4a2bbd;">Download PDF Report</a>
  </form>

  <hr style="margin:12px 0;">
  <p><b>Filter:</b> <?= e($label) ?></p>
  <p><b>Total Transactions:</b> <?= e((string)$summary['c']) ?></p>
  <p><b>Total Sales (RM):</b> <?= e(number_format((float)$summary['s'],2)) ?></p>

  <div style="overflow-x:auto;margin-top:12px;">
    <table style="width:100%;border-collapse:collapse;">
      <thead>
        <tr style="background:#6c3cf0;color:#fff;">
          <th style="padding:10px;text-align:left;">Date & Time</th>
          <th style="padding:10px;text-align:right;">TX ID</th>
          <th style="padding:10px;text-align:left;">Member Email</th>
          <th style="padding:10px;text-align:left;">Payment Ref</th>
          <th style="padding:10px;text-align:right;">Total (RM)</th>
          <th style="padding:10px;">Receipt</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr style="border-bottom:1px solid #eee;">
            <td data-label="Date"><?= e($r['created_at']) ?></td>
            <td data-label="TX" style="text-align:right;"><?= e((string)$r['id']) ?></td>
            <td data-label="Email"><?= e($r['email']) ?></td>
            <td data-label="Ref"><?= e($r['payment_ref']) ?></td>
            <td data-label="Total" style="text-align:right;"><?= e(number_format((float)$r['total_amount'],2)) ?></td>
            <td data-label="Receipt"><a class="btn-nav" href="/receipt.php?id=<?= e((string)$r['id']) ?>" style="background:#10b981;">View</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include __DIR__ . '/../_partials/footer.php'; ?>