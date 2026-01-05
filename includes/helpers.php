<?php
declare(strict_types=1);

function e(string $s): string {
  return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function now_str(): string {
  return date('Y-m-d H:i:s');
}

function redirect(string $path): never {
  header('Location: ' . $path);
  exit;
}

function require_post(): void {
  if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
  }
}

function session_init(): void {
  if (session_status() !== PHP_SESSION_ACTIVE) session_start();
}

function flash_set(string $type, string $msg): void {
  session_init();
  $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function flash_get(): ?array {
  session_init();
  if (empty($_SESSION['flash'])) return null;
  $f = $_SESSION['flash'];
  unset($_SESSION['flash']);
  return $f;
}

function uuid_hex(): string {
  return bin2hex(random_bytes(16));
}