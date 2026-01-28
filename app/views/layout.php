<?php
declare(strict_types=1);
?>
<!doctype html>
<html lang="<?= e(current_lang()) ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= e(t('მუნიციპალიტეტი', 'Municipality')) ?></title>
  <link rel="stylesheet" href="<?= e(base_path()) ?>/public/assets/site.css">
</head>
<body>

<header class="topbar">
  <div class="wrap topbar-row">
    <div class="topbar-left">
      <span><?= e(t('ცხელი ხაზი', 'Hotline')) ?> (0493) 22 12 95</span>
      <span class="dot">•</span>
      <span>(0493) 22 10 15</span>
    </div>
    <div class="topbar-right">
      <a class="social" href="#" aria-label="Facebook">f</a>
      <a class="social" href="#" aria-label="YouTube">▶</a>
      <a class="social" href="#" aria-label="Instagram">◎</a>
      <span class="sep">|</span>
      <?php $cur = current_lang(); ?>
      <?php
        $curPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $noLang = preg_replace('#^/(ge|en)#','', $curPath);
        if ($noLang === '') $noLang = '/';
      ?>
      <a class="<?= $cur==='ge'?'active':'' ?>" href="<?= e(base_path()) ?>/ge<?= e($noLang) ?>">GE</a>
      <a class="<?= $cur==='en'?'active':'' ?>" href="<?= e(base_path()) ?>/en<?= e($noLang) ?>">EN</a>
    </div>
  </div>
</header>

<header class="masthead">
  <div class="wrap masthead-row">
    <a class="brand" href="<?= e(url_to('/')) ?>">
      <div class="logo">M</div>
      <div class="brand-text">
        <div class="brand-title"><?= e(t('ქალაქ ფოთის მუნიციპალიტეტი', 'Poti City Municipality')) ?></div>
        <div class="brand-sub"><?= e(t('ოფიციალური საინფორმაციო პორტალი', 'Official information portal')) ?></div>
      </div>
    </a>
    <div class="header-search" role="search">
      <input type="search" placeholder="<?= e(t('მოძებნე საიტზე...', 'Search the site...')) ?>" aria-label="<?= e(t('ძებნა', 'Search')) ?>">
      <button type="button"><?= e(t('ძებნა', 'Search')) ?></button>
    </div>
  </div>
</header>

<nav class="nav-bar">
  <div class="wrap nav-row">
    <a class="nav-home" href="<?= e(url_to('/')) ?>" aria-label="<?= e(t('მთავარი', 'Home')) ?>">⌂</a>
    <a href="<?= e(url_to('/')) ?>"><?= e(t('მთავარი', 'Home')) ?></a>
    <a href="<?= e(url_to('/news')) ?>"><?= e(t('სიახლეები', 'News')) ?></a>
    <a href="<?= e(url_to('/departments')) ?>"><?= e(t('დეპარტამენტები', 'Departments')) ?></a>
    <a href="<?= e(url_to('/page/about')) ?>"><?= e(t('ჩვენს შესახებ', 'About')) ?></a>
    <a href="<?= e(url_to('/page/about')) ?>"><?= e(t('სერვისები', 'Services')) ?></a>
    <a href="<?= e(url_to('/page/about')) ?>"><?= e(t('კონტაქტი', 'Contact')) ?></a>
    <div class="nav-spacer"></div>
    <a class="nav-search" href="#" aria-label="<?= e(t('ძებნა', 'Search')) ?>">🔍</a>
  </div>
</nav>

<main class="main">
  <?php require $viewFile; ?>
</main>

<footer class="footer">
  <div class="wrap footer-top">
    <div class="footer-brand">
      <div class="logo">M</div>
      <div>
        <div class="footer-title"><?= e(t('ქალაქ ფოთის მუნიციპალიტეტი', 'Poti City Municipality')) ?></div>
        <div class="footer-sub"><?= e(t('ოფიციალური საინფორმაციო პორტალი', 'Official information portal')) ?></div>
      </div>
    </div>
    <div class="footer-social">
      <a href="#" aria-label="Facebook">f</a>
      <a href="#" aria-label="YouTube">▶</a>
      <a href="#" aria-label="Instagram">◎</a>
      <a href="#" aria-label="LinkedIn">in</a>
    </div>
  </div>

  <div class="wrap footer-grid">
    <div>
      <h4><?= e(t('კონტაქტი', 'Contact')) ?></h4>
      <p><?= e(t('მისამართი: ქალაქი, ქუჩა #1', 'Address: City, Street #1')) ?></p>
      <p>info@municipality.ge</p>
      <p>+995 5xx xx xx xx</p>
    </div>
    <div>
      <h4><?= e(t('სასარგებლო ბმულები', 'Useful links')) ?></h4>
      <a href="<?= e(url_to('/page/about')) ?>"><?= e(t('ჩვენს შესახებ', 'About')) ?></a><br>
      <a href="<?= e(url_to('/news')) ?>"><?= e(t('სიახლეები', 'News')) ?></a><br>
      <a href="<?= e(url_to('/departments')) ?>"><?= e(t('დეპარტამენტები', 'Departments')) ?></a>
    </div>
    <div>
      <h4><?= e(t('მოქალაქის სერვისები', 'Citizen services')) ?></h4>
      <p><?= e(t('აქ შეიძლება იყოს სწრაფი ბმულები და სერვისები.', 'Here you can place quick links and services.')) ?></p>
    </div>
    <div>
      <h4><?= e(t('დოკუმენტები', 'Documents')) ?></h4>
      <p><?= e(t('კონცეფცია, ბიუჯეტი და სტრატეგია.', 'Concept, budget and strategy.')) ?></p>
    </div>
  </div>

  <div class="wrap footer-bottom">
    <span>© <?= date('Y') ?> Municipality</span>
  </div>
</footer>

</body>
</html>
