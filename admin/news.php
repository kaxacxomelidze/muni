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

function fetch_news(PDO $pdo, int $id): ?array {
  $st = $pdo->prepare("SELECT * FROM news WHERE id=:id LIMIT 1");
  $st->execute([':id'=>$id]);
  $row = $st->fetch();
  return $row ?: null;
}

if ($action === 'delete' && $id>0) {
  csrf_check();
  $row = fetch_news($pdo, $id);
  if (!$row) { flash_set('err','Not found'); redirect('?p=news'); }
  if (!can_manage_dept($u, $row['department_id'] !== null ? (int)$row['department_id'] : null)) {
    http_response_code(403); exit('Forbidden');
  }
  $st = $pdo->prepare("DELETE FROM news WHERE id=:id");
  $st->execute([':id'=>$id]);
  flash_set('ok','Deleted');
  redirect('?p=news');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();

  $mode = (string)($_POST['mode'] ?? '');
  $status = (string)($_POST['status'] ?? 'published');
  $published_at = trim((string)($_POST['published_at'] ?? ''));

  $departmentId = $_POST['department_id'] !== '' ? (int)$_POST['department_id'] : null;
  if (!is_super($u)) $departmentId = dept_id($u);

  $ge_title = trim((string)($_POST['ge_title'] ?? ''));
  $ge_body  = (string)($_POST['ge_body'] ?? '');
  $en_title = trim((string)($_POST['en_title'] ?? ''));
  $en_body  = (string)($_POST['en_body'] ?? '');

  $cover = (string)($_POST['cover_current'] ?? '');
  $cfg = require __DIR__ . '/../app/config.php';

  // upload cover
  if (!empty($_FILES['cover']['name']) && is_uploaded_file($_FILES['cover']['tmp_name'])) {
    $dir = $cfg['upload_dir'] . '/news';
    if (!is_dir($dir)) mkdir($dir, 0775, true);

    $ext = pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION) ?: 'jpg';
    $safe = 'news_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . preg_replace('/[^a-z0-9]/i','', $ext);
    $dst = $dir . '/' . $safe;
    move_uploaded_file($_FILES['cover']['tmp_name'], $dst);

    $cover = $cfg['upload_url'] . '/news/' . $safe;
  }

  if ($ge_title==='' || $en_title==='') {
    flash_set('err','Please fill titles');
    redirect('?p=news&a=' . ($mode==='edit'?'edit&id='.(int)$_POST['id']:'new'));
  }

  if ($mode === 'new') {
    if (!can_manage_dept($u, $departmentId)) { http_response_code(403); exit('Forbidden'); }

    $st = $pdo->prepare("INSERT INTO news (department_id, cover, status, published_at, created_at) VALUES (:d,:c,:s,:p,NOW())");
    $st->execute([
      ':d'=>$departmentId,
      ':c'=>$cover ?: null,
      ':s'=>$status,
      ':p'=>$published_at!=='' ? $published_at : null
    ]);
    $nid = (int)$pdo->lastInsertId();

    $st2 = $pdo->prepare("INSERT INTO news_translations (news_id, lang, title, body) VALUES
      (:id,'ge',:gt,:gb), (:id,'en',:et,:eb)
    ");
    $st2->execute([':id'=>$nid,':gt'=>$ge_title,':gb'=>$ge_body,':et'=>$en_title,':eb'=>$en_body]);

    flash_set('ok','Created');
    redirect('?p=news');
  }

  if ($mode === 'edit') {
    $nid = (int)($_POST['id'] ?? 0);
    $row = fetch_news($pdo, $nid);
    if (!$row) { flash_set('err','Not found'); redirect('?p=news'); }
    if (!can_manage_dept($u, $row['department_id'] !== null ? (int)$row['department_id'] : null)) {
      http_response_code(403); exit('Forbidden');
    }

    $st = $pdo->prepare("UPDATE news SET cover=:c, status=:s, published_at=:p, updated_at=NOW() WHERE id=:id");
    $st->execute([
      ':c'=>$cover ?: null,
      ':s'=>$status,
      ':p'=>$published_at!=='' ? $published_at : null,
      ':id'=>$nid
    ]);

    foreach (['ge','en'] as $lang) {
      $title = $lang==='ge' ? $ge_title : $en_title;
      $body  = $lang==='ge' ? $ge_body  : $en_body;

      $stx = $pdo->prepare("SELECT 1 FROM news_translations WHERE news_id=:id AND lang=:l");
      $stx->execute([':id'=>$nid,':l'=>$lang]);
      if ($stx->fetchColumn()) {
        $stu = $pdo->prepare("UPDATE news_translations SET title=:t, body=:b WHERE news_id=:id AND lang=:l");
        $stu->execute([':t'=>$title,':b'=>$body,':id'=>$nid,':l'=>$lang]);
      } else {
        $sti = $pdo->prepare("INSERT INTO news_translations (news_id, lang, title, body) VALUES (:id,:l,:t,:b)");
        $sti->execute([':id'=>$nid,':l'=>$lang,':t'=>$title,':b'=>$body]);
      }
    }

    flash_set('ok','Saved');
    redirect('?p=news');
  }
}

$ok = flash_get('ok');
$err = flash_get('err');

if ($action === 'new' || ($action === 'edit' && $id>0)) {
  $row = null;
  $tr = ['ge'=>['title'=>'','body'=>''], 'en'=>['title'=>'','body'=>'']];

  if ($action === 'edit') {
    $row = fetch_news($pdo, $id);
    if (!$row) { echo "<div class='alert'>Not found</div>"; return; }
    if (!can_manage_dept($u, $row['department_id'] !== null ? (int)$row['department_id'] : null)) {
      http_response_code(403); exit('Forbidden');
    }
    $st = $pdo->prepare("SELECT lang,title,body FROM news_translations WHERE news_id=:id");
    $st->execute([':id'=>$id]);
    foreach ($st->fetchAll() as $r) $tr[$r['lang']] = ['title'=>$r['title'], 'body'=>$r['body']];
  }

  // departments for super admin
  $depts = [];
  if (is_super($u)) {
    $st = $pdo->prepare("SELECT d.id, d.slug, dt.name FROM departments d JOIN department_translations dt ON dt.department_id=d.id AND dt.lang='ge' ORDER BY dt.name");
    $st->execute();
    $depts = $st->fetchAll();
  }

  ?>
  <h1><?= $action==='new'?'New News':'Edit News' ?></h1>
  <?php if ($ok): ?><div class="ok"><?= e($ok) ?></div><?php endif; ?>
  <?php if ($err): ?><div class="alert"><?= e($err) ?></div><?php endif; ?>

  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <input type="hidden" name="mode" value="<?= e($action==='new'?'new':'edit') ?>">
    <?php if ($action==='edit'): ?><input type="hidden" name="id" value="<?= (int)$id ?>"><?php endif; ?>
    <input type="hidden" name="cover_current" value="<?= e($row['cover'] ?? '') ?>">

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

    <label>Status</label>
    <select name="status">
      <option value="published" <?= ($row['status'] ?? 'published')==='published'?'selected':'' ?>>published</option>
      <option value="draft" <?= ($row['status'] ?? '')==='draft'?'selected':'' ?>>draft</option>
    </select>

    <label>Published at (YYYY-MM-DD HH:MM:SS)</label>
    <input name="published_at" value="<?= e($row['published_at'] ?? '') ?>" placeholder="2026-01-27 12:00:00">

    <label>Cover image</label>
    <input type="file" name="cover" accept="image/*">
    <?php if (!empty($row['cover'])): ?>
      <div class="muted">Current: <?= e($row['cover']) ?></div>
    <?php endif; ?>

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
    <a class="btn ghost" href="?p=news">Cancel</a>
  </form>
  <?php
  return;
}

$st = $pdo->prepare("
  SELECT n.id, n.status, n.department_id, n.published_at, n.created_at, dt.name AS dept_name,
         nt.title AS ge_title
  FROM news n
  LEFT JOIN department_translations dt
    ON dt.department_id=n.department_id AND dt.lang='ge'
  LEFT JOIN news_translations nt
    ON nt.news_id=n.id AND nt.lang='ge'
  WHERE {$where}
  ORDER BY n.id DESC
");
$st->execute($params);
$rows = $st->fetchAll();
?>
<h1>News</h1>
<?php if ($ok): ?><div class="ok"><?= e($ok) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert"><?= e($err) ?></div><?php endif; ?>

<p><a class="btn" href="?p=news&a=new">+ New News</a></p>

<table class="tbl">
  <thead>
    <tr>
      <th>ID</th><th>Scope</th><th>Title (GE)</th><th>Status</th><th>Published</th><th class="actions">Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><?= (int)$r['id'] ?></td>
        <td><?= $r['department_id'] ? e($r['dept_name'] ?? ('Dept#'.$r['department_id'])) : '(GLOBAL)' ?></td>
        <td><?= e($r['ge_title'] ?? '') ?></td>
        <td><?= e($r['status']) ?></td>
        <td><?= e($r['published_at'] ?? '') ?></td>
        <td class="actions">
          <a href="?p=news&a=edit&id=<?= (int)$r['id'] ?>">Edit</a>
          <form method="post" action="?p=news&a=delete&id=<?= (int)$r['id'] ?>" style="display:inline" onsubmit="return confirm('Delete?')">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <button class="btn ghost" style="padding:6px 10px">Delete</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
