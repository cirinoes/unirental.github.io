<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$cart = $_SESSION['cart'] ?? [];
$cart_items = [];
$total = 0.0;

foreach ($cart as $ci) {
  $stmt = db()->prepare('SELECT id, name, category, daily_rate FROM items WHERE id=?');
  $stmt->execute([(int)$ci['item_id']]);
  $it = $stmt->fetch();
  if (!$it) continue;

  $qty = max(1, (int)($ci['quantity'] ?? 1));
  $unit = (float)$it['daily_rate'];
  $sub = $unit * $qty;
  $total += $sub;

  $cart_items[] = [
    'cart_id' => $ci['cart_id'],
    'name' => $it['name'],
    'category' => $it['category'],
    'unit' => $unit,
    'qty' => $qty,
    'sub' => $sub,
    'start_date' => (string)($ci['start_date'] ?? ''),
    'end_date' => (string)($ci['end_date'] ?? ''),
  ];
}

include __DIR__ . '/_partials/header.php';
?>
<div class="card">
  <h2>Cart</h2>

  <?php if (!$cart_items): ?>
    <p>Your cart is empty.</p>
    <a class="btn-nav" href="/vehicles.php">Browse</a>
  <?php else: ?>
    <div style="overflow-x:auto;">
      <table style="width:100%;border-collapse:collapse;">
        <thead>
          <tr style="background:#6c3cf0;color:#fff;">
            <th style="padding:10px;text-align:left;">Item</th>
            <th style="padding:10px;text-align:left;">Category</th>
            <th style="padding:10px;text-align:right;">Unit (RM)</th>
            <th style="padding:10px;text-align:right;">Qty</th>
            <th style="padding:10px;text-align:right;">Subtotal (RM)</th>
            <th style="padding:10px;text-align:left;">Dates</th>
            <th style="padding:10px;">Action</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($cart_items as $r): ?>
          <tr style="border-bottom:1px solid #eee;">
            <td data-label="Item"><?= e($r['name']) ?></td>
            <td data-label="Category"><?= e($r['category']) ?></td>
            <td data-label="Unit" style="text-align:right;"><?= e(number_format($r['unit'],2)) ?></td>
            <td data-label="Qty" style="text-align:right;">
              <form method="post" action="/cart_update.php" style="display:flex;gap:6px;justify-content:flex-end;">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="cart_id" value="<?= e($r['cart_id']) ?>">
                <input type="number" name="quantity" min="1" value="<?= e((string)$r['qty']) ?>" style="width:90px;padding:6px;">
                <button class="btn-nav" type="submit">Update</button>
              </form>
            </td>
            <td data-label="Subtotal" style="text-align:right;"><?= e(number_format($r['sub'],2)) ?></td>
            <td data-label="Dates">
              <?php if ($r['start_date'] && $r['end_date']): ?>
                <?= e($r['start_date']) ?> → <?= e($r['end_date']) ?>
              <?php else: ?>
                -
              <?php endif; ?>
            </td>
            <td data-label="Action">
              <form method="post" action="/cart_remove.php">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="cart_id" value="<?= e($r['cart_id']) ?>">
                <button class="btn-nav" type="submit" style="background:#ef4444;">Remove</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div style="margin-top:12px;display:flex;justify-content:space-between;flex-wrap:wrap;gap:10px;align-items:center;">
      <div style="font-size:18px;font-weight:800;">Total: RM <?= e(number_format($total,2)) ?></div>
      <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <a class="btn-nav" href="/vehicles.php">Continue Shopping</a>
        <form method="post" action="/cart_clear.php">
          <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
          <button class="btn-nav" type="submit" style="background:#6b7280;">Clear Cart</button>
        </form>
        <a class="btn-nav" href="/checkout.php" style="background:#10b981;">Checkout / Make Payment</a>
      </div>
    </div>

    <?php if (!current_user()): ?>
      <div style="margin-top:12px;padding:10px;border-radius:8px;border:1px solid #fdba74;background:#fff7ed;">
        Public user note: clicking Checkout will redirect you to Member Registration.
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>
<?php include __DIR__ . '/_partials/footer.php'; ?>