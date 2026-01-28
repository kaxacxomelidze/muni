<section class="wrap section">
  <a class="link" href="<?= e(url_to('/departments')) ?>">← <?= e(t('ყველა დეპარტამენტი', 'All departments')) ?></a>

  <div class="dept-hero">
    <div class="dept-icon big">🏛️</div>
    <div>
      <h1><?= e($dept['name']) ?></h1>
      <p><?= e($dept['description'] ?? '') ?></p>
      <div class="small">
        <?= e($dept['address'] ?? '') ?> • <?= e($dept['phone'] ?? '') ?> • <?= e($dept['email'] ?? '') ?>
      </div>
    </div>
  </div>

  <div class="split">
    <div class="panel">
      <h3><?= e(t('გვერდები', 'Pages')) ?></h3>
      <?php if (!$pages): ?>
        <p class="muted"><?= e(t('ჯერ გვერდები არ დამატებულა.', 'No pages yet.')) ?></p>
      <?php else: ?>
        <ul>
          <?php foreach ($pages as $p): ?>
            <li><a href="<?= e(url_to('/page-dept/' . $dept['slug'] . '/' . $p['slug'])) ?>"><?= e($p['title']) ?></a></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>

    <div class="panel">
      <h3><?= e(t('ბოლო სიახლეები', 'Latest news')) ?></h3>
      <?php if (!$news): ?>
        <p class="muted"><?= e(t('სიახლეები არ მოიძებნა.', 'No news found.')) ?></p>
      <?php else: ?>
        <ul>
          <?php foreach ($news as $n): ?>
            <li><a href="<?= e(url_to('/news/' . (int)$n['id'])) ?>"><?= e($n['title']) ?></a></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
  </div>
</section>
