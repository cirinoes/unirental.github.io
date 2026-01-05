<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$next = trim($_GET['next'] ?? ($_POST['next'] ?? ''));

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
  csrf_validate();

  $email = strtolower(trim($_POST['email'] ?? ''));
  $pass = (string)($_POST['password'] ?? '');

  $stmt = db()->prepare('SELECT id, email, full_name, password_hash, role FROM users WHERE email=?');
  $stmt->execute([$email]);
  $u = $stmt->fetch();

  if ($u && password_verify($pass, $u['password_hash'])) {
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int)$u['id'];
    flash_set('success', 'Login successful.');
    redirect($next ?: '/vehicles.php');
  }
  flash_set('danger', 'Login failed (wrong email or password).');
  redirect('/login.php?next=' . urlencode($next));
}

include __DIR__ . '/_partials/header.php';
?>
<div class="card" style="max-width:520px;">
  <h2>Login</h2>
  <form method="post">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <input type="hidden" name="next" value="<?= e($next) ?>">

    <label>Email</label>
    <input type="email" name="email" required style="width:100%;padding:8px;margin:6px 0 12px;">

    <label>Password</label>
    <input type="password" name="password" required style="width:100%;padding:8px;margin:6px 0 12px;">

    <button class="btn-nav" type="submit">Login</button>
    <a class="btn-nav" href="/signup.php?next=<?= e(urlencode($next)) ?>">Sign Up</a>
  </form>
</div>
<?php include __DIR__ . '/_partials/footer.php'; ?>