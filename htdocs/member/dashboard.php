<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_login();
$u = current_user();
include __DIR__ . '/../_partials/header.php';
?>
<div class="card">
  <h2>Member Dashboard</h2>
  <p>Welcome, <b><?= e($u['full_name']) ?></b></p>
  <div style="display:flex;gap:8px;flex-wrap:wrap;">
    <a class="btn-nav" href="/vehicles.php">Browse</a>
    <a class="btn-nav" href="/cart.php">Cart</a>
    <a class="btn-nav" href="/member/transactions.php">My Transactions</a>
    <a class="btn-nav" href="/member/profile.php">Edit Profile</a>
  </div>
</div>
<?php include __DIR__ . '/../_partials/footer.php'; ?>