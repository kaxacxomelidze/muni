<?php
declare(strict_types=1);

function cfg(string $key, $default = null) {
  static $cfg = null;
  if ($cfg === null) $cfg = require __DIR__ . '/config.php';
  return $cfg[$key] ?? $default;
}

function e(string $s): string {
  return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function base_path(): string {
  $base = cfg('base_path', null);
  if ($base === null || $base === '') {
    $base = cfg('public_url', '');
  }
  if ($base === null || $base === '') {
    $scriptName = (string)($_SERVER['SCRIPT_NAME'] ?? '');
    $dir = str_replace('\\', '/', dirname($scriptName));
    $dir = rtrim($dir, '/');
    if ($dir !== '' && $dir !== '.') {
      if (str_ends_with($dir, '/public')) {
        $dir = substr($dir, 0, -7);
      }
      $base = $dir === '/' ? '' : $dir;
    }
  }
  return rtrim((string)$base, '/');
}

function redirect(string $path): void {
  header('Location: ' . $path);
  exit;
}

function session_start_safe(): void {
  if (session_status() !== PHP_SESSION_ACTIVE) session_start();
}

function csrf_token(): string {
  session_start_safe();
  if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
  return (string)$_SESSION['csrf'];
}

function csrf_check(): void {
  session_start_safe();
  $tok = (string)($_POST['csrf'] ?? '');
  if (!$tok || empty($_SESSION['csrf']) || !hash_equals((string)$_SESSION['csrf'], $tok)) {
    http_response_code(419);
    exit('CSRF failed');
  }
}

function flash_set(string $key, string $msg): void {
  session_start_safe();
  $_SESSION['flash'][$key] = $msg;
}

function flash_get(string $key): ?string {
  session_start_safe();
  $m = $_SESSION['flash'][$key] ?? null;
  unset($_SESSION['flash'][$key]);
  return $m ? (string)$m : null;
}
