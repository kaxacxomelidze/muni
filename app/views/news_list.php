<section class="wrap section">
  <div class="section-head">
    <h2><?= e(t('სიახლეები', 'News')) ?></h2>
  </div>

  <div class="news-grid">
    <?php foreach ($items as $n): ?>
      <a class="news-card" href="<?= e(url_to('/news/' . (int)$n['id'])) ?>">
        <div class="news-cover">
          <?php if (!empty($n['cover'])): ?><img src="<?= e($n['cover']) ?>" alt=""><?php else: ?><div class="ph"></div><?php endif; ?>
        </div>
        <div class="news-body">
          <div class="news-meta"><?= e($n['published_at'] ?: '') ?></div>
          <b class="news-title"><?= e($n['title']) ?></b>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</section>
