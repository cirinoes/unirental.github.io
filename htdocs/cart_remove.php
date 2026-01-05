<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_post();
csrf_validate();

$cart_id = (string)($_POST['cart_id'] ?? '');
$cart = $_SESSION['cart'] ?? [];
$_SESSION['cart'] = array_values(array_filter($cart, fn($x) => ($x['cart_id'] ?? '') !== $cart_id));

flash_set('success', 'Item removed.');
redirect('/cart.php');