<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_login();
$u = current_user();

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
  csrf_validate();
  $full = trim($_POST['full_name'] ?? '');
  $newp = (string)($_POST['new_password'] ?? '');

  if (!preg_match('/^[A-Za-z]+(?: [A-Za-z]+)*$/', $full)) {
    flash_set('danger', 'Full name must contain alphabets only (spaces allowed).');
    redirect('/member/profile.php');
  }

  $upd = db()->prepare('UPDATE users SET full_name=? WHERE id=?');
  $upd->execute([$full, (int)$u['id']]);

  if ($newp !== '') {
    if (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9])\S{6,8}$/', $newp)) {
      flash_set('danger', 'New password invalid (6-8, 1 uppercase, 1 number, 1 special, no spaces).');
      redirect('/member/profile.php');
    }
    $up2 = db()->prepare('UPDATE users SET password_hash=? WHERE id=?');
    $up2->execute([password_hash($newp, PASSWORD_DEFAULT), (int)$u['id']]);
  }

  flash_set('success', 'Profile updated.');
  redirect('/member/dashboard.php');
}

include __DIR__ . '/../_partials/header.php';
?>
<div class="card" style="max-width:520px;">
  <h2>Edit Profile</h2>
  <form method="post">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

    <label>Full Name</label>
    <input name="full_name" value="<?= e($u['full_name']) ?>" required style="width:100%;padding:8px;margin:6px 0 12px;">

    <label>Email</label>
    <input value="<?= e($u['email']) ?>" readonly style="width:100%;padding:8px;margin:6px 0 12px;">

    <label>New Password (optional)</label>
    <input type="password" name="new_password" style="width:100%;padding:8px;margin:6px 0 12px;">

    <button class="btn-nav" type="submit">Save</button>
  </form>
</div>
<?php include __DIR__ . '/../_partials/footer.php'; ?>