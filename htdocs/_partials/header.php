<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
$u = current_user();
$f = flash_get();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e(APP_NAME) ?></title>
  <link rel="stylesheet" href="/assets/css/style.css">
  <script defer src="/assets/js/main.js"></script>
</head>
<body>
  <div class="navbar">
    <div class="nav-left"><span class="brand"><?= e(APP_NAME) ?></span></div>
    <div class="nav-right">
      <a class="btn-nav" href="/vehicles.php">Browse</a>
      <a class="btn-nav" href="/cart.php">Cart</a>

      <?php if ($u && ($u['role'] ?? '') === 'admin'): ?>
        <a class="btn-nav" href="/admin/admin_dashboard.php">Admin</a>
      <?php endif; ?>

      <?php if ($u): ?>
        <a class="btn-nav" href="/member/dashboard.php">Dashboard</a>
        <a class="btn-nav" href="/logout.php">Logout</a>
      <?php else: ?>
        <a class="btn-nav" href="/login.php">Login</a>
        <a class="btn-nav" href="/signup.php">Sign Up</a>
      <?php endif; ?>

      <button class="dark-toggle" type="button" onclick="toggleDarkMode()">Theme</button>
    </div>
  </div>

  <div class="content">
    <?php if ($f): ?>
      <div class="flash flash-<?= e($f['type']) ?>"><?= e($f['msg']) ?></div>
    <?php endif; ?>