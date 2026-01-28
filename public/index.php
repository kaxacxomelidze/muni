<?php
declare(strict_types=1);

require __DIR__ . '/../app/db.php';
require __DIR__ . '/../app/helpers.php';
require __DIR__ . '/../app/i18n.php';
require __DIR__ . '/../app/router.php';

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$uri = str_replace(base_path(), '', $uri);
$uri = '/' . ltrim($uri, '/');

try {
  $parts = array_values(array_filter(explode('/', trim($uri,'/')), fn($x)=>$x!==''));

  // language from first segment
  $langs = cfg('langs', ['ge','en']);
  if (isset($parts[0]) && in_array($parts[0], $langs, true)) {
    set_lang($parts[0]);
    array_shift($parts);
  } else {
    // redirect to default lang prefix
    set_lang(cfg('default_lang','ge'));
    redirect(base_path() . '/' . current_lang() . $uri);
  }

  $path = '/' . implode('/', $parts);
  [$view, $data] = route_public($path);

  $viewFile = __DIR__ . '/../app/views/' . $view;
} catch (Throwable $e) {
  error_log('Unhandled error: ' . $e->getMessage());
  http_response_code(500);
  $viewFile = __DIR__ . '/../app/views/error.php';
  $data = [];
}

extract($data, EXTR_SKIP);
require __DIR__ . '/../app/views/layout.php';
