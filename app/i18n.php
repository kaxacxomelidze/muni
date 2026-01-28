<?php
declare(strict_types=1);

function current_lang(): string {
  session_start_safe();
  return (string)($_SESSION['lang'] ?? cfg('default_lang', 'ge'));
}

function set_lang(string $lang): void {
  session_start_safe();
  $langs = cfg('langs', ['ge','en']);
  if (!in_array($lang, $langs, true)) $lang = cfg('default_lang','ge');
  $_SESSION['lang'] = $lang;
}

function url_lang_prefix(): string {
  return '/' . current_lang();
}

function url_to(string $pathWithoutLang): string {
  // $pathWithoutLang must start with "/"
  return base_path() . url_lang_prefix() . $pathWithoutLang;
}

function t(string $ge, string $en): string {
  return current_lang() === 'en' ? $en : $ge;
}
