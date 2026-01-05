<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/mailers.php';

$u = current_user();

// Requirement: Public user clicking checkout must go to registration
if (!$u) redirect('/signup.php?next=' . urlencode('/checkout.php'));

$cart = $_SESSION['cart'] ?? [];
if (!$cart) {
  flash_set('warning', 'Cart is empty.');
  redirect('/cart.php');
}

// Build totals from DB (do not trust client)
$lines = [];
$total = 0.0;

function has_overlap(int $item_id, string $start, string $end): bool {
  $stmt = db()->prepare(
    "SELECT 1 FROM rentals
     WHERE item_id=? AND status!='Cancelled'
       AND start_date < ? AND end_date > ?
     LIMIT 1"
  );
  $stmt->execute([$item_id, $end, $start]);
  return (bool)$stmt->fetch();
}

foreach ($cart as $ci) {
  $stmt = db()->prepare('SELECT id, name, daily_rate FROM items WHERE id=?');
  $stmt->execute([(int)$ci['item_id']]);
  $it = $stmt->fetch();
  if (!$it) continue;

  $qty = max(1, (int)($ci['quantity'] ?? 1));
  $unit = (float)$it['daily_rate'];
  $sub = $unit * $qty;
  $total += $sub;

  $sd = trim((string)($ci['start_date'] ?? ''));
  $ed = trim((string)($ci['end_date'] ?? ''));
  $lines[] = ['ci'=>$ci, 'it'=>$it, 'qty'=>$qty, 'unit'=>$unit, 'sub'=>$sub, 'sd'=>$sd, 'ed'=>$ed];
}

if (!$lines) {
  $_SESSION['cart'] = [];
  flash_set('warning', 'Cart items invalid.');
  redirect('/cart.php');
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'GET') {
  include __DIR__ . '/_partials/header.php';
  ?>
  <div class="card">
    <h2>Checkout (Dummy Payment)</h2>
    <p><b>Member:</b> <?= e($u['full_name']) ?> (<?= e($u['email']) ?>)</p>
    <p><b>Total Amount:</b> RM <?= e(number_format($total,2)) ?></p>
    <form method="post">
      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
      <button class="btn-nav" type="submit" style="background:#10b981;">Confirm Payment</button>
      <a class="btn-nav" href="/cart.php">Back to Cart</a>
    </form>
  </div>
  <?php
  include __DIR__ . '/_partials/footer.php';
  exit;
}

// POST
require_post();
csrf_validate();

// Validate availability for date-based items
foreach ($lines as $L) {
  if ($L['sd'] && $L['ed']) {
    $s = DateTime::createFromFormat('Y-m-d', $L['sd']);
    $e = DateTime::createFromFormat('Y-m-d', $L['ed']);
    if (!$s || !$e || $e <= $s) {
      flash_set('danger', 'Invalid date range in cart.');
      redirect('/cart.php');
    }
    if (has_overlap((int)$L['it']['id'], $L['sd'], $L['ed'])) {
      flash_set('danger', "Item '{$L['it']['name']}' no longer available for {$L['sd']} to {$L['ed']}.");
      redirect('/cart.php');
    }
  }
}

$tx_time = now_str();
$payment_ref = 'DUMMY-' . date('YmdHis') . '-' . strtoupper(substr(uuid_hex(), 0, 6));

db()->beginTransaction();
try {
  $insTx = db()->prepare('INSERT INTO transactions (user_id, created_at, status, payment_method, payment_ref, total_amount, email_sent)
                          VALUES (?,?,?,?,?,?,0)');
  $insTx->execute([(int)$u['id'], $tx_time, 'PAID', 'DUMMY', $payment_ref, (float)$total]);
  $tx_id = (int)db()->lastInsertId();

  $insItem = db()->prepare('INSERT INTO transaction_items (transaction_id, item_id, unit_price, quantity, subtotal, start_date, end_date)
                            VALUES (?,?,?,?,?,?,?)');

  $insRental = db()->prepare('INSERT INTO rentals (user_id, item_id, start_date, end_date, cost, status, created_at)
                              VALUES (?,?,?,?,?,?,?)');

  foreach ($lines as $L) {
    $insItem->execute([
      $tx_id,
      (int)$L['it']['id'],
      (float)$L['unit'],
      (int)$L['qty'],
      (float)$L['sub'],
      $L['sd'] ?: null,
      $L['ed'] ?: null,
    ]);

    // Create rentals only when dates are provided (to enforce availability search)
    if ($L['sd'] && $L['ed']) {
      $insRental->execute([(int)$u['id'], (int)$L['it']['id'], $L['sd'], $L['ed'], (float)$L['sub'], 'Paid', $tx_time]);
    }
  }

  db()->commit();
} catch (Throwable $e) {
  db()->rollBack();
  flash_set('danger', 'Checkout failed.');
  redirect('/cart.php');
}

// Email notification (best-effort)
$email_ok = send_mail(
  $u['email'],
  'UniRental Payment Receipt',
  "Thank you for your payment.\n\nTransaction ID: {$tx_id}\nPayment Ref: {$payment_ref}\nTotal: RM " . number_format($total,2) . "\nDate: {$tx_time}\n"
);
if ($email_ok) {
  $up = db()->prepare('UPDATE transactions SET email_sent=1 WHERE id=?');
  $up->execute([$tx_id]);
}

$_SESSION['cart'] = [];
redirect('/receipt.php?id=' . urlencode((string)$tx_id));