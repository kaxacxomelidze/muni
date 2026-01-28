<?php
$pdo = db();
$u = auth_user();

[$where, $params] = scope_where_dept($u, 'department_id');

$st1 = $pdo->prepare("SELECT COUNT(*) c FROM pages WHERE {$where}");
$st1->execute($params);
$pagesCount = (int)$st1->fetch()['c'];

$st2 = $pdo->prepare("SELECT COUNT(*) c FROM news WHERE {$where}");
$st2->execute($params);
$newsCount = (int)$st2->fetch()['c'];
?>
<h1>Dashboard</h1>
<div class="grid">
  <div class="box"><b><?= $pagesCount ?></b><span>Pages</span></div>
  <div class="box"><b><?= $newsCount ?></b><span>News</span></div>
</div>

<p class="muted">
  Dept admin can manage only own department content. Super admin can manage all.
</p>
