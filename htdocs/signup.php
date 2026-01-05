<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$next = trim($_GET['next'] ?? ($_POST['next'] ?? ''));

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
  csrf_validate();

  $full = trim($_POST['full_name'] ?? '');
  $email = strtolower(trim($_POST['email'] ?? ''));
  $pass = (string)($_POST['password'] ?? '');

  if (!preg_match('/^[A-Za-z]+(?: [A-Za-z]+)*$/', $full)) {
    flash_set('danger', 'Full name must contain alphabets only (spaces allowed).');
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flash_set('danger', 'Invalid email format.');
  } elseif (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9])\S{6,8}$/', $pass)) {
    flash_set('danger', 'Password must be 6-8 chars, include 1 uppercase, 1 number, 1 special character, and no spaces.');
  } else {
    $stmt = db()->prepare('SELECT 1 FROM users WHERE email=?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
      flash_set('danger', 'Email already exists.');
    } else {
      $ins = db()->prepare('INSERT INTO users (email, full_name, password_hash, role, created_at) VALUES (?,?,?,?,?)');
      $ins->execute([$email, $full, password_hash($pass, PASSWORD_DEFAULT), 'member', now_str()]);
      flash_set('success', 'Account created. Please login.');
      redirect('/login.php?next=' . urlencode($next));
    }
  }
}

include __DIR__ . '/_partials/header.php';
?>
<div class="card" style="max-width:520px;">
  <h2>Member Registration</h2>
  <form method="post">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <input type="hidden" name="next" value="<?= e($next) ?>">

    <label>Full name</label>
    <input name="full_name" required style="width:100%;padding:8px;margin:6px 0 12px;">

    <label>Email</label>
    <input type="email" name="email" required style="width:100%;padding:8px;margin:6px 0 12px;">

    <label>Password</label>
    <input type="password" name="password" required style="width:100%;padding:8px;margin:6px 0 12px;">

    <button class="btn-nav" type="submit">Sign Up</button>
    <a class="btn-nav" href="/login.php?next=<?= e(urlencode($next)) ?>">Login</a>
  </form>

  <p style="margin-top:12px;font-size:13px;">
    Password: 6–8 chars, ONE uppercase, ONE numeric, ONE special character, no spaces.
  </p>
</div>
<?php include __DIR__ . '/_partials/footer.php'; ?>