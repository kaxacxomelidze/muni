<?php
declare(strict_types=1);

function db(): PDO {
  static $pdo = null;
  if ($pdo) return $pdo;

  $cfg = require __DIR__ . '/config.php';
  $db = $cfg['db'] ?? [];

  $dsn = (string)($db['dsn'] ?? '');
  if ($dsn === '') {
    $host = (string)($db['host'] ?? '');
    $name = (string)($db['name'] ?? '');
    $charset = (string)($db['charset'] ?? 'utf8mb4');
    $port = (string)($db['port'] ?? '');
    if ($host === '' || $name === '') {
      throw new RuntimeException('Database host/name not configured.');
    }
    $portPart = $port !== '' ? ";port={$port}" : '';
    $dsn = "mysql:host={$host}{$portPart};dbname={$name};charset={$charset}";
  }

  $user = (string)($db['user'] ?? '');
  $pass = (string)($db['pass'] ?? '');

  $options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
  ];

  try {
    $pdo = new PDO($dsn, $user, $pass, $options);
  } catch (PDOException $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    throw new RuntimeException('Database connection failed.');
  }
  return $pdo;
}
