<?php
declare(strict_types=1);

return [
  'db' => [
    'host'    => '127.0.0.1',
    'name'    => 'municipality_db',
    'user'    => 'root',
    'pass'    => '',
    'charset' => 'utf8mb4',
  ],

  // If project is hosted in a subfolder, set public_url like '/municipality'
  'public_url' => '',

  'default_lang' => 'ge',
  'langs' => ['ge','en'],

  // uploads
  'upload_dir' => dirname(__DIR__) . '/storage/uploads',
  'upload_url' => '/storage/uploads',
];