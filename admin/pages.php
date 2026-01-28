<?php
declare(strict_types=1);

require __DIR__ . '/../app/db.php';
require __DIR__ . '/../app/helpers.php';
require __DIR__ . '/../app/auth.php';

$u = require_login();
$pdo = db();

$action = $_GET['a'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);

[$where, $params] = scope_where_dept($u, 'department_id');

function fetch_page(PDO $pdo, int $id): ?array {
  $st = $pdo->prepare("SELECT * FROM pages WHERE id=:id LIMIT 1");
  $st->execute([':id'=>$id]);
  $row = $st->fetch();
  return $row ?: null;
}

if ($action === 'delete' && $id>0) {
  csrf_check();
  $row = fetch_page($pdo, $id);
  if (!$row) { flash_set('err','Not found'); redirect('?p=pages'); }
  if (!can_manage_dept($u, $row['department_id'] !== null ? (int)$row['department_id'] : null)) {
    http_response_code(403); exit('Forbidden');
  }
  $st = $pdo->prepare("DELETE FROM pages WHERE id=:id");
  $st->execute([':id'=>$id]);
  flash_set('ok','Deleted');
  redirect('?p=pages');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();

  $mode = (string)($_POST['mode'] ?? '');
  $slug = trim((string)($_POST['slug'] ?? ''));
  $status = (string)($_POST['status'] ?? 'published');

  $departmentId = $_POST['department_id'] !== '' ? (int)$_POST['department_id'] : null;

  if (!is_super($u)) $departmentId = dept_id($u); // dept admins forced to own dept

  $ge_title = trim((string)($_POST['ge_title'] ?? ''));
  $ge_body  = (string)($_POST['ge_body'] ?? '');
  $en_title = trim((string)($_POST['en_title'] ?? ''));
  $en_body  = (string)($_POST['en_body'] ?? '');

  if ($slug === '' || $ge_title==='' || $en_title==='') {
    flash_set('err','Please fill slug and titles');
    redirect('?p=pages&a=' . ($mode==='edit'?'edit&id='.(int)$_POST['id']:'new'));
  }

  if ($mode === 'new') {
    if (!can_manage_dept($u, $departmentId)) { http_response_code(403); exit('Forbidden'); }

    $st = $pdo->prepare("INSERT INTO pages (department_id, slug, status, created_at) VALUES (:d, :s, :st, NOW())");
    $st->execute([':d'=>$departmentId, ':s'=>$slug, ':st'=>$status]);
    $pid = (int)$pdo->lastInsertId();

    $st2 = $pdo->prepare("INSERT INTO page_translations (page_id, lang, title, body) VALUES
      (:id,'ge',:gt,:gb), (:id,'en',:et,:eb)
    ");
    $st2->execute([':id'=>$pid,':gt'=>$ge_title,':gb'=>$ge_body,':et'=>$en_title,':eb'=>$en_body]);

    flash_set('ok','Created');
    redirect('?p=pages');
  }

  if ($mode === 'edit') {
    $pid = (int)($_POST['id'] ?? 0);
    $row = fetch_page($pdo, $pid);
    if (!$row) { flash_set('err','Not found'); redirect('?p=pages'); }
    if (!can_manage_dept($u, $row['department_id'] !== null ? (int)$row['department_id'] : null)) {
      http_response_code(403); exit('Forbidden');
    }

    $st = $pdo->prepare("UPDATE pages SET slug=:s, status=:st, updated_at=NOW() WHERE id=:id");
    $st->execute([':s'=>$slug, ':st'=>$status, ':id'=>$pid]);

    // translations: update if exists else insert
    foreach (['ge','en'] as $lang) {
      $title = $lang==='ge' ? $ge_title : $en_title;
      $body  = $lang==='ge' ? $ge_body  : $en_body;

      $stx = $pdo->prepare("SELECT 1 FROM page_translations WHERE page_id=:id AND lang=:l");
      $stx->execute([':id'=>$pid,':l'=>$lang]);
      if ($stx->fetchColumn()) {
        $stu = $pdo->prepare("UPDATE page_translations SET title=:t, body=:b WHERE page_id=:id AND lang=:l");
        $stu->execute([':t'=>$title,':b'=>$body,':id'=>$pid,':l'=>$lang]);
      } else {
        $sti = $pdo->prepare("INSERT INTO page_translations (page_id, lang, title, body) VALUES (:id,:l,:t,:b)");
        $sti->execute([':id'=>$pid,':l'=>$lang,':t'=>$title,':b'=>$body]);
      }
    }

    flash_set('ok','Saved');
    redirect('?p=pages');
  }
}

$ok = flash_get('ok');
$err = flash_get('err');

if ($action === 'new' || ($action === 'edit' && $id>0)) {
  $row = null;
  $tr = ['ge'=>['title'=>'','body'=>''], 'en'=>['title'=>'','body'=>'']];

  if ($action === 'edit') {
    $row = fetch_page($pdo, $id);
    if (!$row) { echo "<div class='alert'>Not found</div>"; return; }
    if (!can_manage_dept($u, $row['department_id'] !== null ? (int)$row['department_id'] : null)) {
      http_response_code(403); exit('Forbidden');
    }
    $st = $pdo->prepare("SELECT lang,title,body FROM page_translations WHERE page_id=:id");
    $st->execute([':id'=>$id]);
    foreach ($st->fetchAll() as $r) $tr[$r['lang']] = ['title'=>$r['title'], 'body'=>$r['body']];
  }

  // departments list for super admin
  $depts = [];
  if (is_super($u)) {
    $lang='ge';
    $st = $pdo->prepare("SELECT d.id, d.slug, dt.name FROM departments d JOIN department_translations dt ON dt.department_id=d.id AND dt.lang=:l ORDER BY dt.name");
    $st->execute([':l'=>$lang]);
    $depts = $st->fetchAll();
  }

  ?>
  <h1><?= $action==='new'?'New Page':'Edit Page' ?></h1>
  <?php if ($ok): ?><div class="ok"><?= e($ok) ?></div><?php endif; ?>
  <?php if ($err): ?><div class="alert"><?= e($err) ?></div><?php endif; ?>

  <form method="post">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <input type="hidden" name="mode" value="<?= e($action==='new'?'new':'edit') ?>">
    <?php if ($action==='edit'): ?><input type="hidden" name="id" value="<?= (int)$id ?>"><?php endif; ?>

    <?php if (is_super($u)): ?>
      <label>Department</label>
      <select name="department_id">
        <option value="">(GLOBAL)</option>
        <?php foreach ($depts as $d): ?>
          <option value="<?= (int)$d['id'] ?>" <?= ($row && (int)$row['department_id']===(int)$d['id'])?'selected':'' ?>>
            <?= e($d['name']) ?> (<?= e($d['slug']) ?>)
          </option>
        <?php endforeach; ?>
      </select>
    <?php else: ?>
      <input type="hidden" name="department_id" value="<?= (int)dept_id($u) ?>">
      <div class="muted">Department: <?= (int)dept_id($u) ?></div>
    <?php endif; ?>

    <label>Slug</label>
    <input name="slug" value="<?= e($row['slug'] ?? '') ?>" placeholder="example: services" required>

    <label>Status</label>
    <select name="status">
      <option value="published" <?= ($row['status'] ?? 'published')==='published'?'selected':'' ?>>published</option>
      <option value="draft" <?= ($row['status'] ?? '')==='draft'?'selected':'' ?>>draft</option>
    </select>

    <div class="grid2">
      <div class="box2">
        <h3>Georgian</h3>
        <label>Title (GE)</label>
        <input name="ge_title" value="<?= e($tr['ge']['title'] ?? '') ?>" required>
        <label>Body (GE) (HTML allowed)</label>
        <textarea name="ge_body" rows="10"><?= e($tr['ge']['body'] ?? '') ?></textarea>
      </div>
      <div class="box2">
        <h3>English</h3>
        <label>Title (EN)</label>
        <input name="en_title" value="<?= e($tr['en']['title'] ?? '') ?>" required>
        <label>Body (EN) (HTML allowed)</label>
        <textarea name="en_body" rows="10"><?= e($tr['en']['body'] ?? '') ?></textarea>
      </div>
    </div>

    <button class="btn">Save</button>
    <a class="btn ghost" href="?p=pages">Cancel</a>
  </form>
  <?php
  return;
}

$st = $pdo->prepare("
  SELECT p.id, p.slug, p.status, p.department_id, p.created_at, dt.name AS dept_name
  FROM pages p
  LEFT JOIN department_translations dt
    ON dt.department_id=p.department_id AND dt.lang='ge'
  WHERE {$where}
  ORDER BY p.id DESC
");
$st->execute($params);
$rows = $st->fetchAll();
?>
<h1>Pages</h1>
<?php if ($ok): ?><div class="ok"><?= e($ok) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert"><?= e($err) ?></div><?php endif; ?>

<p><a class="btn" href="?p=pages&a=new">+ New Page</a></p>

<table class="tbl">
  <thead>
    <tr>
      <th>ID</th><th>Scope</th><th>Slug</th><th>Status</th><th>Created</th><th class="actions">Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><?= (int)$r['id'] ?></td>
        <td><?= $r['department_id'] ? e($r['dept_name'] ?? ('Dept#'.$r['department_id'])) : '(GLOBAL)' ?></td>
        <td><?= e($r['slug']) ?></td>
        <td><?= e($r['status']) ?></td>
        <td><?= e($r['created_at']) ?></td>
        <td class="actions">
          <a href="?p=pages&a=edit&id=<?= (int)$r['id'] ?>">Edit</a>
          <form method="post" action="?p=pages&a=delete&id=<?= (int)$r['id'] ?>" style="display:inline" onsubmit="return confirm('Delete?')">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <button class="btn ghost" style="padding:6px 10px">Delete</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
