<?php
declare(strict_types=1);

function route_public(string $path): array {
  // returns [viewFile, dataArray]
  $pdo = db();
  $lang = current_lang();

  $path = trim($path, '/');
  $parts = $path === '' ? [] : explode('/', $path);

  // home
  if (!$parts) {
    // latest news
    $st = $pdo->prepare("
      SELECT n.id, n.cover, n.published_at, nt.title
      FROM news n
      JOIN news_translations nt ON nt.news_id=n.id AND nt.lang=:lang
      WHERE n.status='published'
      ORDER BY COALESCE(n.published_at, n.created_at) DESC
      LIMIT 6
    ");
    $st->execute([':lang'=>$lang]);
    $news = $st->fetchAll();

    // departments list
    $st2 = $pdo->prepare("
      SELECT d.id, d.slug, dt.name, dt.description
      FROM departments d
      JOIN department_translations dt ON dt.department_id=d.id AND dt.lang=:lang
      WHERE d.is_active=1
      ORDER BY dt.name ASC
    ");
    $st2->execute([':lang'=>$lang]);
    $depts = $st2->fetchAll();

    return ['home.php', compact('news','depts')];
  }

  // /news
  if ($parts[0] === 'news' && count($parts) === 1) {
    $st = $pdo->prepare("
      SELECT n.id, n.cover, n.published_at, nt.title, LEFT(REPLACE(nt.body, '<', ' '), 180) AS excerpt
      FROM news n
      JOIN news_translations nt ON nt.news_id=n.id AND nt.lang=:lang
      WHERE n.status='published'
      ORDER BY COALESCE(n.published_at, n.created_at) DESC
      LIMIT 30
    ");
    $st->execute([':lang'=>$lang]);
    $items = $st->fetchAll();
    return ['news_list.php', compact('items')];
  }

  // /news/{id}
  if ($parts[0] === 'news' && isset($parts[1]) && ctype_digit($parts[1])) {
    $id = (int)$parts[1];
    $st = $pdo->prepare("
      SELECT n.*, nt.title, nt.body
      FROM news n
      JOIN news_translations nt ON nt.news_id=n.id AND nt.lang=:lang
      WHERE n.id=:id AND n.status='published'
      LIMIT 1
    ");
    $st->execute([':lang'=>$lang, ':id'=>$id]);
    $item = $st->fetch();
    if (!$item) return ['not_found.php', []];
    return ['news_view.php', compact('item')];
  }

  // /departments
  if ($parts[0] === 'departments' && count($parts) === 1) {
    $st = $pdo->prepare("
      SELECT d.id, d.slug, dt.name, dt.description, dt.phone, dt.email
      FROM departments d
      JOIN department_translations dt ON dt.department_id=d.id AND dt.lang=:lang
      WHERE d.is_active=1
      ORDER BY dt.name ASC
    ");
    $st->execute([':lang'=>$lang]);
    $items = $st->fetchAll();
    return ['departments.php', compact('items')];
  }

  // /departments/{slug}
  if ($parts[0] === 'departments' && isset($parts[1])) {
    $slug = $parts[1];
    $st = $pdo->prepare("
      SELECT d.id, d.slug, dt.*
      FROM departments d
      JOIN department_translations dt ON dt.department_id=d.id AND dt.lang=:lang
      WHERE d.slug=:slug AND d.is_active=1
      LIMIT 1
    ");
    $st->execute([':lang'=>$lang, ':slug'=>$slug]);
    $dept = $st->fetch();
    if (!$dept) return ['not_found.php', []];

    // department pages
    $st2 = $pdo->prepare("
      SELECT p.id, p.slug, pt.title
      FROM pages p
      JOIN page_translations pt ON pt.page_id=p.id AND pt.lang=:lang
      WHERE p.department_id=:did AND p.status='published'
      ORDER BY pt.title ASC
    ");
    $st2->execute([':lang'=>$lang, ':did'=>$dept['id']]);
    $pages = $st2->fetchAll();

    // department news
    $st3 = $pdo->prepare("
      SELECT n.id, n.cover, n.published_at, nt.title
      FROM news n
      JOIN news_translations nt ON nt.news_id=n.id AND nt.lang=:lang
      WHERE n.department_id=:did AND n.status='published'
      ORDER BY COALESCE(n.published_at, n.created_at) DESC
      LIMIT 6
    ");
    $st3->execute([':lang'=>$lang, ':did'=>$dept['id']]);
    $news = $st3->fetchAll();

    return ['department_view.php', compact('dept','pages','news')];
  }

  // /page/{slug} (global page)
  if ($parts[0] === 'page' && isset($parts[1])) {
    $slug = $parts[1];
    $st = $pdo->prepare("
      SELECT p.id, p.slug, pt.title, pt.body
      FROM pages p
      JOIN page_translations pt ON pt.page_id=p.id AND pt.lang=:lang
      WHERE p.slug=:slug AND p.department_id IS NULL AND p.status='published'
      LIMIT 1
    ");
    $st->execute([':lang'=>$lang, ':slug'=>$slug]);
    $page = $st->fetch();
    if (!$page) return ['not_found.php', []];
    return ['page_view.php', compact('page')];
  }

  // /page-dept/{deptSlug}/{pageSlug}
  if ($parts[0] === 'page-dept' && isset($parts[1], $parts[2])) {
    $deptSlug = $parts[1];
    $pageSlug = $parts[2];

    $st0 = $pdo->prepare("SELECT id FROM departments WHERE slug=:s AND is_active=1 LIMIT 1");
    $st0->execute([':s'=>$deptSlug]);
    $d = $st0->fetch();
    if (!$d) return ['not_found.php', []];

    $st = $pdo->prepare("
      SELECT p.id, pt.title, pt.body
      FROM pages p
      JOIN page_translations pt ON pt.page_id=p.id AND pt.lang=:lang
      WHERE p.department_id=:did AND p.slug=:slug AND p.status='published'
      LIMIT 1
    ");
    $st->execute([':lang'=>$lang, ':did'=>$d['id'], ':slug'=>$pageSlug]);
    $page = $st->fetch();
    if (!$page) return ['not_found.php', []];
    return ['page_view.php', compact('page')];
  }

  return ['not_found.php', []];
}
