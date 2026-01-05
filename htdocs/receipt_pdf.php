<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
$me = current_user();

$stmt = db()->prepare('SELECT * FROM transactions WHERE id=?');
$stmt->execute([$id]);
$tx = $stmt->fetch();
if (!$tx) { http_response_code(404); exit('Not found'); }

if (($me['role'] ?? '') !== 'admin' && (int)$tx['user_id'] !== (int)$me['id']) {
  http_response_code(403); exit('Forbidden');
}

$uStmt = db()->prepare('SELECT email, full_name FROM users WHERE id=?');
$uStmt->execute([(int)$tx['user_id']]);
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

require_once __DIR__ . '/lib/fpdf.php';

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,'UNIRENTAL PAYMENT RECEIPT',0,1,'C');
$pdf->SetFont('Arial','',12);
$pdf->Cell(0,8,'Transaction ID: ' . $tx['id'],0,1);
$pdf->Cell(0,8,'Date & Time: ' . $tx['created_at'],0,1);
$pdf->Cell(0,8,'Payment Ref: ' . $tx['payment_ref'],0,1);
$pdf->Cell(0,8,'Member: ' . $owner['full_name'] . ' (' . $owner['email'] . ')',0,1);
$pdf->Ln(4);

$pdf->SetFillColor(108,60,240);
$pdf->SetTextColor(255,255,255);
$pdf->SetFont('Arial','B',10);
$pdf->Cell(70,8,'Item',1,0,'L',true);
$pdf->Cell(25,8,'Unit',1,0,'R',true);
$pdf->Cell(15,8,'Qty',1,0,'R',true);
$pdf->Cell(25,8,'Subtotal',1,0,'R',true);
$pdf->Cell(55,8,'Dates',1,1,'L',true);

$pdf->SetTextColor(0,0,0);
$pdf->SetFont('Arial','',9);

foreach ($rows as $r) {
  $dates = '-';
  if ($r['start_date'] && $r['end_date']) $dates = $r['start_date'] . '->' . $r['end_date'];
  $pdf->Cell(70,8,substr((string)$r['name'],0,35),1,0,'L');
  $pdf->Cell(25,8,number_format((float)$r['unit_price'],2),1,0,'R');
  $pdf->Cell(15,8,(string)$r['quantity'],1,0,'R');
  $pdf->Cell(25,8,number_format((float)$r['subtotal'],2),1,0,'R');
  $pdf->Cell(55,8,substr($dates,0,30),1,1,'L');
}

$pdf->Ln(4);
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,10,'TOTAL: RM ' . number_format((float)$tx['total_amount'],2),0,1,'R');

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="receipt_' . $id . '.pdf"');
echo $pdf->Output('S');