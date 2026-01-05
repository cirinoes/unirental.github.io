<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_post();
csrf_validate();
$_SESSION['cart'] = [];
flash_set('success', 'Cart cleared.');
redirect('/cart.php');