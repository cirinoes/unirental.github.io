<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$q = trim($_GET['q'] ?? '');
$category = trim($_GET['category'] ?? '');
$start = trim($_GET['start_date'] ?? '');
$end = trim($_GET['end_date'] ?? '');

$cats = db()->query('SELECT DISTINCT category FROM items ORDER BY category')->fetchAll();

$sql = 'SELECT * FROM items WHERE 1=1';
$params = [];
if ($category !== '') { $sql .= ' AND category=?'; $params[] = $category; }
if ($q !== '') { $sql .= ' AND name LIKE ?'; $params[] = '%' . $q . '%'; }
$sql .= ' ORDER BY id DESC';
$stmt = db()->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll();

$valid_range = false;
$total_days = null;
if ($start && $end) {
  $s = DateTime::createFromFormat('Y-m-d', $start);
  $e = DateTime::createFromFormat('Y-m-d', $end);
  if ($s && $e && $e > $s) {
    $valid_range = true;
    $total_days = (int)$e->diff($s)->days;
  } else {
    flash_set('warning', 'Invalid date range. End date must be after start date.');
  }
}

function has_overlap(int $item_id, string $start, string $end): bool {
  // overlap rule: existing.start < new.end AND existing.end > new.start
  $stmt = db()->prepare(
    "SELECT 1 FROM rentals
     WHERE item_id=? AND status!='Cancelled'
       AND start_date < ? AND end_date > ?
     LIMIT 1"
  );
  $stmt->execute([$item_id, $end, $start]);
  return (bool)$stmt->fetch();
}

$filtered = [];
foreach ($items as $it) {
  if ($valid_range) {
    if (!has_overlap((int)$it['id'], $start, $end)) $filtered[] = $it;
  } else {
    $filtered[] = $it;
  }
}

include __DIR__ . '/_partials/header.php';
?>
<script defer src="/assets/js/vehicles.js"></script>


<div class="card">
  <h2>Browse Items</h2>

  <div style="display:flex;gap:8px;flex-wrap:wrap;margin:10px 0;">
    <a class="btn-nav" href="/vehicles.php">All</a>
    <?php foreach ($cats as $c): ?>
      <a class="btn-nav" href="/vehicles.php?category=<?= e(urlencode($c['category'])) ?>"><?= e($c['category']) ?></a>
    <?php endforeach; ?>
  </div>

  <form method="get" style="display:flex;gap:8px;flex-wrap:wrap;align-items:end;">
    <div>
      <label>Search</label><br>
      <input name="q" value="<?= e($q) ?>" style="padding:8px;">
    </div>
    <div>
      <label>Category</label><br>
      <input name="category" value="<?= e($category) ?>" style="padding:8px;">
    </div>
    <div>
      <label>Start</label><br>
      <input type="date" name="start_date" value="<?= e($start) ?>" style="padding:8px;">
    </div>
    <div>
      <label>End</label><br>
      <input type="date" name="end_date" value="<?= e($end) ?>" style="padding:8px;">
    </div>
    <button class="btn-nav" type="submit" style="background:#10b981;">Search</button>
  </form>

  <?php if ($valid_range): ?>
    <p style="margin-top:10px;"><b>Showing available items:</b> <?= e($start) ?> to <?= e($end) ?> (<?= e((string)$total_days) ?> day(s))</p>
  <?php else: ?>
    <p style="margin-top:10px;">Dates are optional (only needed for availability search).</p>
  <?php endif; ?>
</div>

<div class="grid" style="margin-top:16px;">
  <?php foreach ($filtered as $it): ?>
    <?php
      $img = $it['image_filename'] ? '/uploads/items/' . rawurlencode($it['image_filename']) : '';
      $priceText = $valid_range ? ('RM ' . number_format((float)$it['daily_rate'] * (int)$total_days, 2) . " ({$total_days} days)") : ('RM ' . number_format((float)$it['daily_rate'], 2) . '/day');
      $addUrl = '/cart_add.php?item_id=' . urlencode((string)$it['id'])
        . '&start_date=' . urlencode($start)
        . '&end_date=' . urlencode($end);
    ?>
    <div class="card">
      <h3><?= e($it['name']) ?></h3>
      <div><?= e($it['category']) ?></div>
      <div style="margin:8px 0;font-weight:700;"><?= e($priceText) ?></div>

      <?php if ($img): ?>
        <img src="<?= e($img) ?>" alt="<?= e($it['name']) ?>" style="width:100%;height:160px;object-fit:cover;border-radius:10px;">
      <?php endif; ?>

      <div style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap;">
        <a class="btn-nav" href="<?= e($addUrl) ?>" style="background:#6c3cf0;">Add to Cart</a>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<?php include __DIR__ . '/_partials/footer.php'; ?>