<section class="wrap section">
  <a class="link" href="<?= e(url_to('/news')) ?>">← <?= e(t('უკან სიახლეებზე', 'Back to news')) ?></a>

  <article class="article">
    <h1><?= e($item['title']) ?></h1>
    <div class="article-meta"><?= e($item['published_at'] ?: '') ?></div>

    <?php if (!empty($item['cover'])): ?>
      <img class="article-cover" src="<?= e($item['cover']) ?>" alt="">
    <?php endif; ?>

    <div class="article-body">
      <?= $item['body'] /* trusted HTML from admin */ ?>
    </div>
  </article>
</section>
