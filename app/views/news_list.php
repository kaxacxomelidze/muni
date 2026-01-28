<section class="wrap section">
  <div class="section-head">
    <h2><?= e(t('სიახლეები', 'News')) ?></h2>
  </div>

  <div class="news-list">
    <?php foreach ($items as $n): ?>
      <a class="news-row" href="<?= e(url_to('/news/' . (int)$n['id'])) ?>">
        <div class="news-thumb">
          <?php if (!empty($n['cover'])): ?><img src="<?= e($n['cover']) ?>" alt=""><?php else: ?><div class="thumb-ph"></div><?php endif; ?>
        </div>
        <div class="news-info">
          <div class="news-title"><?= e($n['title']) ?></div>
          <div class="news-meta"><?= e($n['published_at'] ?: '') ?></div>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</section>
