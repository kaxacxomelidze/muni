<?php
declare(strict_types=1);

require __DIR__ . '/../app/db.php';
require __DIR__ . '/../app/helpers.php';

session_start_safe();

$err = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();

  $email = trim((string)($_POST['email'] ?? ''));
  $pass  = (string)($_POST['password'] ?? '');

  $pdo = db();
  $st = $pdo->prepare("SELECT * FROM users WHERE email=:e AND is_active=1 LIMIT 1");
  $st->execute([':e'=>$email]);
  $u = $st->fetch();

  if ($u && password_verify($pass, $u['password_hash'])) {
    $_SESSION['user'] = [
      'id' => (int)$u['id'],
      'email' => $u['email'],
      'role' => $u['role'],
      'department_id' => $u['department_id'] !== null ? (int)$u['department_id'] : null
    ];
    redirect(base_path() . '/admin/');
  } else {
    $err = 'Invalid credentials';
  }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin Login</title>
  <link rel="stylesheet" href="<?= e(base_path()) ?>/admin/assets/admin.css">
</head>
<body class="center">
  <form class="login" method="post">
    <h1>Admin Login</h1>
    <?php if ($err): ?><div class="alert"><?= e($err) ?></div><?php endif; ?>
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <label>Email</label>
    <input name="email" required>
    <label>Password</label>
    <input name="password" type="password" required>
    <button class="btn">Login</button>
  </form>
</body>
</html>
