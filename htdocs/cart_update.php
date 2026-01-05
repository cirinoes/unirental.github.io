<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_post();
csrf_validate();

$cart_id = (string)($_POST['cart_id'] ?? '');
$qty = max(1, (int)($_POST['quantity'] ?? 1));

$cart = $_SESSION['cart'] ?? [];
foreach ($cart as &$ci) {
  if (($ci['cart_id'] ?? '') === $cart_id) {
    $ci['quantity'] = $qty;
    break;
  }
}
$_SESSION['cart'] = $cart;

flash_set('success', 'Cart updated.');
redirect('/cart.php');