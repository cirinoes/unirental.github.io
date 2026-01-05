<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$item_id = (int)($_GET['item_id'] ?? 0);
$start = trim($_GET['start_date'] ?? '');
$end = trim($_GET['end_date'] ?? '');

$stmt = db()->prepare('SELECT id FROM items WHERE id=?');
$stmt->execute([$item_id]);
if (!$stmt->fetch()) {
  flash_set('danger', 'Item not found.');
  redirect('/vehicles.php');
}

$qty = 1;
if ($start && $end) {
  $s = DateTime::createFromFormat('Y-m-d', $start);
  $e = DateTime::createFromFormat('Y-m-d', $end);
  if ($s && $e && $e > $s) {
    $qty = (int)$e->diff($s)->days;
  } else {
    $start = '';
    $end = '';
  }
}

$_SESSION['cart'] = $_SESSION['cart'] ?? [];
$_SESSION['cart'][] = [
  'cart_id' => uuid_hex(),
  'item_id' => $item_id,
  'quantity' => $qty,
  'start_date' => $start,
  'end_date' => $end,
];

flash_set('success', 'Added to cart.');
redirect('/cart.php');