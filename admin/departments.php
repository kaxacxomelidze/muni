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

function fetch_dept(PDO $pdo, int $id): ?array {
  $st = $pdo->prepare("SELECT * FROM departments WHERE id=:id LIMIT 1");
  $st->execute([':id'=>$id]);
  $row = $st->fetch();
  return $row ?: null;
}

if ($action === 'delete' && $id>0) {
  csrf_check();
  $st = $pdo->prepare("DELETE FROM departments WHERE id=:id");
  $st->execute([':id'=>$id]);
  flash_set('ok','Deleted');
  redirect('?p=departments');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();

  $mode = (string)($_POST['mode'] ?? '');
  $slug = trim((string)($_POST['slug'] ?? ''));
  $is_active = isset($_POST['is_active']) ? 1 : 0;

  $ge = [
    'name' => trim((string)($_POST['ge_name'] ?? '')),
    'description' => (string)($_POST['ge_description'] ?? ''),
    'address' => (string)($_POST['ge_address'] ?? ''),
    'phone' => (string)($_POST['ge_phone'] ?? ''),
    'email' => (string)($_POST['ge_email'] ?? ''),
  ];
  $en = [
    'name' => trim((string)($_POST['en_name'] ?? '')),
    'description' => (string)($_POST['en_description'] ?? ''),
    'address' => (string)($_POST['en_address'] ?? ''),
    'phone' => (string)($_POST['en_phone'] ?? ''),
    'email' => (string)($_POST['en_email'] ?? ''),
  ];

  if ($slug==='' || $ge['name']==='' || $en['name']==='') {
    flash_set('err','Slug and names required');
    redirect('?p=departments&a=' . ($mode==='edit'?'edit&id='.(int)$_POST['id']:'new'));
  }

  if ($mode === 'new') {
    $st = $pdo->prepare("INSERT INTO departments (slug,is_active,created_at) VALUES (:s,:a,NOW())");
    $st->execute([':s'=>$slug,':a'=>$is_active]);
    $did = (int)$pdo->lastInsertId();

    $st2 = $pdo->prepare("INSERT INTO department_translations (department_id,lang,name,description,address,phone,email)
      VALUES
      (:id,'ge',:gn,:gd,:ga,:gp,:ge),
      (:id,'en',:en,:ed,:ea,:ep,:ee,:em)
    ");
    $st2->execute([
      ':id'=>$did,
      ':gn'=>$ge['name'],':gd'=>$ge['description'],':ga'=>$ge['address'],':gp'=>$ge['phone'],':ge'=>$ge['email'],
      ':en'=>$en['name'],':ed'=>$en['description'],':ea'=>$en['address'],':ep'=>$en['phone'],':ee'=>$en['email'],':em'=>$en['email'],
    ]);

    flash_set('ok','Created');
    redirect('?p=departments');
  }

  if ($mode === 'edit') {
    $did = (int)($_POST['id'] ?? 0);
    $row = fetch_dept($pdo, $did);
    if (!$row) { flash_set('err','Not found'); redirect('?p=departments'); }

    $st = $pdo->prepare("UPDATE departments SET slug=:s,is_active=:a WHERE id=:id");
    $st->execute([':s'=>$slug,':a'=>$is_active,':id'=>$did]);

    foreach (['ge'=>$ge,'en'=>$en] as $lang=>$d) {
      $stx = $pdo->prepare("SELECT 1 FROM department_translations WHERE department_id=:id AND lang=:l");
      $stx->execute([':id'=>$did,':l'=>$lang]);
      if ($stx->fetchColumn()) {
        $stu = $pdo->prepare("UPDATE department_translations SET name=:n,description=:d,address=:a,phone=:p,email=:e WHERE department_id=:id AND lang=:l");
        $stu->execute([':n'=>$d['name'],':d'=>$d['description'],':a'=>$d['address'],':p'=>$d['phone'],':e'=>$d['email'],':id'=>$did,':l'=>$lang]);
      } else {
        $sti = $pdo->prepare("INSERT INTO department_translations (department_id,lang,name,description,address,phone,email)
          VALUES (:id,:l,:n,:d,:a,:p,:e)");
        $sti->execute([':id'=>$did,':l'=>$lang,':n'=>$d['name'],':d'=>$d['description'],':a'=>$d['address'],':p'=>$d['phone'],':e'=>$d['email']]);
      }
    }

    flash_set('ok','Saved');
    redirect('?p=departments');
  }
}

$ok = flash_get('ok');
$err = flash_get('err');

if ($action === 'new' || ($action === 'edit' && $id>0)) {
  $row = null;
  $tr = ['ge'=>['name'=>'','description'=>'','address'=>'','phone'=>'','email'=>''],
         'en'=>['name'=>'','description'=>'','address'=>'','phone'=>'','email'=>'']];

  if ($action === 'edit') {
    $row = fetch_dept($pdo, $id);
    if (!$row) { echo "<div class='alert'>Not found</div>"; return; }
    $st = $pdo->prepare("SELECT * FROM department_translations WHERE department_id=:id");
    $st->execute([':id'=>$id]);
    foreach ($st->fetchAll() as $r) {
      $tr[$r['lang']] = [
        'name'=>$r['name'],'description'=>$r['description'],'address'=>$r['address'],'phone'=>$r['phone'],'email'=>$r['email']
      ];
    }
  }

  ?>
  <h1><?= $action==='new'?'New Department':'Edit Department' ?></h1>
  <?php if ($ok): ?><div class="ok"><?= e($ok) ?></div><?php endif; ?>
  <?php if ($err): ?><div class="alert"><?= e($err) ?></div><?php endif; ?>

  <form method="post">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <input type="hidden" name="mode" value="<?= e($action==='new'?'new':'edit') ?>">
    <?php if ($action==='edit'): ?><input type="hidden" name="id" value="<?= (int)$id ?>"><?php endif; ?>

    <label>Slug</label>
    <input name="slug" value="<?= e($row['slug'] ?? '') ?>" required>

    <label><input type="checkbox" name="is_active" <?= ($row && (int)$row['is_active']===1) || !$row ? 'checked' : '' ?>> Active</label>

    <div class="grid2">
      <div class="box2">
        <h3>Georgian</h3>
        <label>Name (GE)</label><input name="ge_name" value="<?= e($tr['ge']['name'] ?? '') ?>" required>
        <label>Description (GE)</label><textarea name="ge_description" rows="6"><?= e($tr['ge']['description'] ?? '') ?></textarea>
        <label>Address (GE)</label><input name="ge_address" value="<?= e($tr['ge']['address'] ?? '') ?>">
        <label>Phone (GE)</label><input name="ge_phone" value="<?= e($tr['ge']['phone'] ?? '') ?>">
        <label>Email (GE)</label><input name="ge_email" value="<?= e($tr['ge']['email'] ?? '') ?>">
      </div>
      <div class="box2">
        <h3>English</h3>
        <label>Name (EN)</label><input name="en_name" value="<?= e($tr['en']['name'] ?? '') ?>" required>
        <label>Description (EN)</label><textarea name="en_description" rows="6"><?= e($tr['en']['description'] ?? '') ?></textarea>
        <label>Address (EN)</label><input name="en_address" value="<?= e($tr['en']['address'] ?? '') ?>">
        <label>Phone (EN)</label><input name="en_phone" value="<?= e($tr['en']['phone'] ?? '') ?>">
        <label>Email (EN)</label><input name="en_email" value="<?= e($tr['en']['email'] ?? '') ?>">
      </div>
    </div>

    <button class="btn">Save</button>
    <a class="btn ghost" href="?p=departments">Cancel</a>
  </form>
  <?php
  return;
}

$st = $pdo->prepare("
  SELECT d.id, d.slug, d.is_active, dt.name
  FROM departments d
  LEFT JOIN department_translations dt ON dt.department_id=d.id AND dt.lang='ge'
  ORDER BY d.id ASC
");
$st->execute();
$rows = $st->fetchAll();
?>
<h1>Departments</h1>
<?php if ($ok): ?><div class="ok"><?= e($ok) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert"><?= e($err) ?></div><?php endif; ?>

<p><a class="btn" href="?p=departments&a=new">+ New Department</a></p>

<table class="tbl">
  <thead>
    <tr>
      <th>ID</th><th>Slug</th><th>Name (GE)</th><th>Active</th><th class="actions">Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><?= (int)$r['id'] ?></td>
        <td><?= e($r['slug']) ?></td>
        <td><?= e($r['name'] ?? '') ?></td>
        <td><?= (int)$r['is_active']===1 ? 'yes' : 'no' ?></td>
        <td class="actions">
          <a href="?p=departments&a=edit&id=<?= (int)$r['id'] ?>">Edit</a>
          <form method="post" action="?p=departments&a=delete&id=<?= (int)$r['id'] ?>" style="display:inline" onsubmit="return confirm('Delete?')">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <button class="btn ghost" style="padding:6px 10px">Delete</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
