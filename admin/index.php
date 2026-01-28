<?php
declare(strict_types=1);

require __DIR__ . '/../app/db.php';
require __DIR__ . '/../app/helpers.php';
require __DIR__ . '/../app/auth.php';

$u = require_login();

$page = $_GET['p'] ?? 'dash';
$allowed = ['dash','pages','news','departments','users'];
if (!in_array($page, $allowed, true)) $page = 'dash';

?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin</title>
  <link rel="stylesheet" href="<?= e(base_path()) ?>/admin/assets/admin.css">
</head>
<body>
  <div class="bar">
    <b>Admin</b>
    <div class="right">
      <span class="pill"><?= e($u['role']) ?></span>
      <a href="<?= e(base_path()) ?>/admin/logout.php">Logout</a>
    </div>
  </div>

  <div class="wrap">
    <aside class="side">
      <a class="<?= $page==='dash'?'active':'' ?>" href="?p=dash">Dashboard</a>
      <a class="<?= $page==='pages'?'active':'' ?>" href="?p=pages">Pages</a>
      <a class="<?= $page==='news'?'active':'' ?>" href="?p=news">News</a>
      <?php if (is_super($u)): ?>
        <a class="<?= $page==='departments'?'active':'' ?>" href="?p=departments">Departments</a>
        <a class="<?= $page==='users'?'active':'' ?>" href="?p=users">Users</a>
      <?php endif; ?>
      <hr>
      <a href="<?= e(base_path()) ?>/ge/">Open site (GE)</a>
      <a href="<?= e(base_path()) ?>/en/">Open site (EN)</a>
    </aside>

    <main class="content">
      <?php
        if ($page === 'dash') {
          require __DIR__ . '/pages/dashboard.php';
        } elseif ($page === 'pages') {
          require __DIR__ . '/pages.php';
        } elseif ($page === 'news') {
          require __DIR__ . '/news.php';
        } elseif ($page === 'departments') {
          require __DIR__ . '/departments.php';
        } elseif ($page === 'users') {
          require __DIR__ . '/users.php';
        }
      ?>
    </main>
  </div>
</body>
</html>
