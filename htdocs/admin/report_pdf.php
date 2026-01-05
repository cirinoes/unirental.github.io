<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';

require_admin();

$period = strtolower(trim($_GET['period'] ?? 'daily'));
$date = trim($_GET['date'] ?? date('Y-m-d'));
if (!in_array($period, ['daily', 'weekly', 'monthly'], true)) {
  $period = 'daily';
}

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
  $label = 'Daily (' . $start->format('Y-m-d') . ')';
}

$start_s = $start->format('Y-m-d') . ' 00:00:00';
$end_s = $end->format('Y-m-d') . ' 00:00:00';

$stmt = db()->prepare(
  "SELECT t.id, t.created_at, t.payment_ref, t.total_amount, t.status, u.email
   FROM transactions t
   JOIN users u ON u.id = t.user_id
   WHERE t.created_at >= ? AND t.created_at < ?
   ORDER BY t.id DESC"
);
$stmt->execute([$start_s, $end_s]);
$rows = $stmt->fetchAll();

$sum = db()->prepare(
  "SELECT COUNT(*) AS c, COALESCE(SUM(total_amount), 0) AS s
   FROM transactions
   WHERE created_at >= ? AND created_at < ? AND status='PAID'"
);
$sum->execute([$start_s, $end_s]);
$summary = $sum->fetch();

require_once __DIR__ . '/../lib/fpdf.php';
$pdf = new FPDF();
$pdf->AddPage();

$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'UNIRENTAL TRANSACTION REPORT', 0, 1, 'C');

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 8, 'Period: ' . $label, 0, 1);
$pdf->Cell(0, 8, 'From: ' . $start_s, 0, 1);
$pdf->Cell(0, 8, 'To:   ' . $end_s, 0, 1);
$pdf->Ln(2);
$pdf->Cell(0, 8, 'Total Transactions: ' . (string)$summary['c'], 0, 1);
$pdf->Cell(0, 8, 'Total Sales (RM): ' . number_format((float)$summary['s'], 2), 0, 1);
$pdf->Ln(6);

// Table header
$pdf->SetFillColor(108, 60, 240);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 9);

$pdf->Cell(18, 8, 'TX ID', 1, 0, 'R', true);
$pdf->Cell(32, 8, 'Date/Time', 1, 0, 'L', true);
$pdf->Cell(60, 8, 'Member Email', 1, 0, 'L', true);
$pdf->Cell(48, 8, 'Payment Ref', 1, 0, 'L', true);
$pdf->Cell(22, 8, 'Total', 1, 0, 'R', true);
$pdf->Cell(18, 8, 'Status', 1, 1, 'L', true);

$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Arial', '', 8);

foreach ($rows as $r) {
  $txId = (string)$r['id'];
  $dt = substr((string)$r['created_at'], 0, 16);
  $email = substr((string)$r['email'], 0, 34);
  $ref = substr((string)$r['payment_ref'], 0, 28);
  $total = number_format((float)$r['total_amount'], 2);
  $status = substr((string)$r['status'], 0, 10);

  $pdf->Cell(18, 8, $txId, 1, 0, 'R');
  $pdf->Cell(32, 8, $dt, 1, 0, 'L');
  $pdf->Cell(60, 8, $email, 1, 0, 'L');
  $pdf->Cell(48, 8, $ref, 1, 0, 'L');
  $pdf->Cell(22, 8, $total, 1, 0, 'R');
  $pdf->Cell(18, 8, $status, 1, 1, 'L');
}

// Output PDF
$filename = 'transaction_report_' . $period . '_' . date('Ymd_His') . '.pdf';

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $filename . '"');

echo $pdf->Output('S');