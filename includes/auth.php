<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

function auth_init(): void {
  if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
      'httponly' => true,
      'samesite' => 'Lax',
      'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    ]);
    session_start();
  }
}

function current_user(): ?array {
  auth_init();
  if (empty($_SESSION['user_id'])) return null;

  $stmt = db()->prepare('SELECT id, email, full_name, role, created_at FROM users WHERE id = ?');
  $stmt->execute([$_SESSION['user_id']]);
  $u = $stmt->fetch();
  return $u ?: null;
}

function require_login(): void {
  if (!current_user()) redirect('/login.php?next=' . urlencode($_SERVER['REQUEST_URI'] ?? '/'));
}

function require_admin(): void {
  $u = current_user();
  if (!$u || ($u['role'] ?? '') !== 'admin') {
    redirect('/login.php?next=' . urlencode($_SERVER['REQUEST_URI'] ?? '/'));
  }
}