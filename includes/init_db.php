<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

function init_db(): void {
  if (!is_dir(UPLOAD_DIR_ITEMS)) mkdir(UPLOAD_DIR_ITEMS, 0775, true);

  $schema = file_get_contents(__DIR__ . '/sql/schema.sql');
  db()->exec($schema);

  // Seed admin
  $stmt = db()->prepare('SELECT 1 FROM users WHERE email=? LIMIT 1');
  $stmt->execute([DEFAULT_ADMIN_EMAIL]);
  if (!$stmt->fetch()) {
    $ins = db()->prepare('INSERT INTO users (email, full_name, password_hash, role, created_at) VALUES (?,?,?,?,?)');
    $ins->execute([
      DEFAULT_ADMIN_EMAIL,
      'Administrator',
      password_hash(DEFAULT_ADMIN_PASSWORD, PASSWORD_DEFAULT),
      'admin',
      now_str(),
    ]);
  }
}