<?php
declare(strict_types=1);

return [
  'db' => [
    'host'    => '127.0.0.1',
    'port'    => '',
    'name'    => 'municipality_db',
    'user'    => 'root',
    'pass'    => '',
    'charset' => 'utf8mb4',
    // Optional: full DSN overrides host/name/port/charset.
    'dsn'     => '',
  ],

  // If project is hosted in a subfolder, set public_url like '/municipality'
  // base_path is kept for backward compatibility with older configs.
  'public_url' => '',
  'base_path' => '',

  'default_lang' => 'ge',
  'langs' => ['ge','en'],

  // uploads
  'upload_dir' => dirname(__DIR__) . '/storage/uploads',
  'upload_url' => '/storage/uploads',
];
