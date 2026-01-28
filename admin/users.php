<?php
declare(strict_types=1);

require __DIR__ . '/../app/db.php';
require __DIR__ . '/../app/helpers.php';
require __DIR__ . '/../app/auth.php';

$u = require_login();
if (!is_super($u)) { http_response_code(403); exit('Forbidden'); }

$pdo = db();

$action = $_GET['a'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);

function fetch_user(PDO $pdo, int $id): ?array {
  $st = $pdo->prepare("SELECT * FROM users WHERE id=:id LIMIT 1");
  $st->execute([':id'=>$id]);
  $row = $st->fetch();
  return $row ?: null;
}

if ($action === 'delete' && $id>0) {
  csrf_check();
  $st = $pdo->prepare("DELETE FROM users WHERE id=:id");
  $st->execute([':id'=>$id]);
  flash_set('ok','Deleted');
  redirect('?p=users');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();

  $mode = (string)($_POST['mode'] ?? '');
  $email = trim((string)($_POST['email'] ?? ''));
  $role = (string)($_POST['role'] ?? 'DEPT_ADMIN');
  $departmentId = $_POST['department_id'] !== '' ? (int)$_POST['department_id'] : null;
  $is_active = isset($_POST['is_active']) ? 1 : 0;
  $password = (string)($_POST['password'] ?? '');

  if ($role === 'SUPER_ADMIN') $departmentId = null;

  if ($email==='') {
    flash_set('err','Email required');
    redirect('?p=users&a=' . ($mode==='edit'?'edit&id='.(int)$_POST['id']:'new'));
  }

  if ($mode === 'new') {
    if ($password==='') {
      flash_set('err','Password required for new user');
      redirect('?p=users&a=new');
    }
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $st = $pdo->prepare("INSERT INTO users (email,password_hash,role,department_id,is_active,created_at)
      VALUES (:e,:h,:r,:d,:a,NOW())");
    $st->execute([':e'=>$email,':h'=>$hash,':r'=>$role,':d'=>$departmentId,':a'=>$is_active]);

    flash_set('ok','Created');
    redirect('?p=users');
  }

  if ($mode === 'edit') {
    $uid = (int)($_POST['id'] ?? 0);
    $row = fetch_user($pdo, $uid);
    if (!$row) { flash_set('err','Not found'); redirect('?p=users'); }

    $sql = "UPDATE users SET email=:e, role=:r, department_id=:d, is_active=:a";
    $par = [':e'=>$email,':r'=>$role,':d'=>$departmentId,':a'=>$is_active,':id'=>$uid];

    if ($password!=='') {
      $sql .= ", password_hash=:h";
      $par[':h'] = password_hash($password, PASSWORD_DEFAULT);
    }
    $sql .= " WHERE id=:id";

    $st = $pdo->prepare($sql);
    $st->execute($par);

    flash_set('ok','Saved');
    redirect('?p=users');
  }
}

$ok = flash_get('ok');
$err = flash_get('err');

// departments for select
$st = $pdo->prepare("SELECT d.id, d.slug, dt.name FROM departments d JOIN department_translations dt ON dt.department_id=d.id AND dt.lang='ge' ORDER BY dt.name");
$st->execute();
$depts = $st->fetchAll();

if ($action === 'new' || ($action === 'edit' && $id>0)) {
  $row = null;
  if ($action === 'edit') {
    $row = fetch_user($pdo, $id);
    if (!$row) { echo "<div class='alert'>Not found</div>"; return; }
  }
  ?>
  <h1><?= $action==='new'?'New User':'Edit User' ?></h1>
  <?php if ($ok): ?><div class="ok"><?= e($ok) ?></div><?php endif; ?>
  <?php if ($err): ?><div class="alert"><?= e($err) ?></div><?php endif; ?>

  <form method="post">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <input type="hidden" name="mode" value="<?= e($action==='new'?'new':'edit') ?>">
    <?php if ($action==='edit'): ?><input type="hidden" name="id" value="<?= (int)$id ?>"><?php endif; ?>

    <label>Email</label>
    <input name="email" value="<?= e($row['email'] ?? '') ?>" required>

    <label>Role</label>
    <select name="role" id="roleSelect" onchange="toggleDept()">
      <option value="DEPT_ADMIN" <?= ($row['role'] ?? 'DEPT_ADMIN')==='DEPT_ADMIN'?'selected':'' ?>>DEPT_ADMIN</option>
      <option value="SUPER_ADMIN" <?= ($row['role'] ?? '')==='SUPER_ADMIN'?'selected':'' ?>>SUPER_ADMIN</option>
    </select>

    <div id="deptBox">
      <label>Department</label>
      <select name="department_id">
        <option value="">-- choose --</option>
        <?php foreach ($depts as $d): ?>
          <option value="<?= (int)$d['id'] ?>" <?= ($row && (int)$row['department_id']===(int)$d['id'])?'selected':'' ?>>
            <?= e($d['name']) ?> (<?= e($d['slug']) ?>)
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <label>Password <?= $action==='edit' ? '(leave blank to keep)' : '' ?></label>
    <input name="password" type="password" <?= $action==='new'?'required':'' ?>>

    <label><input type="checkbox" name="is_active" <?= ($row && (int)$row['is_active']===1) || !$row ? 'checked' : '' ?>> Active</label>

    <button class="btn">Save</button>
    <a class="btn ghost" href="?p=users">Cancel</a>
  </form>

  <script>
  function toggleDept(){
    var role = document.getElementById('roleSelect').value;
    document.getElementById('deptBox').style.display = (role === 'SUPER_ADMIN') ? 'none' : 'block';
  }
  toggleDept();
  </script>
  <?php
  return;
}

$st = $pdo->prepare("
  SELECT u.id,u.email,u.role,u.department_id,u.is_active,dt.name AS dept_name
  FROM users u
  LEFT JOIN department_translations dt ON dt.department_id=u.department_id AND dt.lang='ge'
  ORDER BY u.id DESC
");
$st->execute();
$rows = $st->fetchAll();
?>
<h1>Users</h1>
<?php if ($ok): ?><div class="ok"><?= e($ok) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert"><?= e($err) ?></div><?php endif; ?>

<p><a class="btn" href="?p=users&a=new">+ New User</a></p>

<table class="tbl">
  <thead>
    <tr>
      <th>ID</th><th>Email</th><th>Role</th><th>Department</th><th>Active</th><th class="actions">Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><?= (int)$r['id'] ?></td>
        <td><?= e($r['email']) ?></td>
        <td><?= e($r['role']) ?></td>
        <td><?= $r['department_id'] ? e($r['dept_name'] ?? ('Dept#'.$r['department_id'])) : '-' ?></td>
        <td><?= (int)$r['is_active']===1 ? 'yes' : 'no' ?></td>
        <td class="actions">
          <a href="?p=users&a=edit&id=<?= (int)$r['id'] ?>">Edit</a>
          <form method="post" action="?p=users&a=delete&id=<?= (int)$r['id'] ?>" style="display:inline" onsubmit="return confirm('Delete?')">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <button class="btn ghost" style="padding:6px 10px">Delete</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
