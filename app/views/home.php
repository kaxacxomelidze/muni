<?php $headline = $news[0]['title'] ?? t('მოგესალმებით ფოთის მუნიციპალიტეტის ოფიციალურ პორტალზე.', 'Welcome to the official municipality portal.'); ?>
<section class="ticker">
  <div class="wrap ticker-row">
    <span class="ticker-label"><?= e(t('სიახლეები', 'News')) ?></span>
    <span class="ticker-text"><?= e($headline) ?></span>
    <span class="ticker-time"><?= e(date('H:i:s')) ?></span>
  </div>
</section>

<section class="wrap main-grid">
  <div class="content-main">
    <div class="gallery-strip">
      <?php foreach (array_slice($news, 0, 6) as $thumb): ?>
        <div class="gallery-thumb">
          <?php if (!empty($thumb['cover'])): ?>
            <img src="<?= e($thumb['cover']) ?>" alt="">
          <?php else: ?>
            <div class="thumb-ph"></div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>

    <?php $feature = $news[0] ?? null; ?>
    <div class="feature-card">
      <div class="feature-media">
        <?php if (!empty($feature['cover'])): ?>
          <img src="<?= e($feature['cover']) ?>" alt="">
        <?php else: ?>
          <div class="ph"></div>
        <?php endif; ?>
      </div>
      <div class="feature-body">
        <h2><?= e($feature['title'] ?? t('მთავარი სიახლე', 'Main story')) ?></h2>
        <p><?= e(t('პირდაპირი სიახლეები და ოფიციალური განცხადებები ერთ სივრცეში.', 'Latest news and official announcements in one place.')) ?></p>
        <?php if ($feature): ?>
          <a class="feature-link" href="<?= e(url_to('/news/' . (int)$feature['id'])) ?>"><?= e(t('ვრცლად →', 'Read more →')) ?></a>
        <?php endif; ?>
      </div>
    </div>

    <div class="section-head">
      <h2><?= e(t('ბოლო სიახლეები', 'Latest news')) ?></h2>
      <a class="link" href="<?= e(url_to('/news')) ?>"><?= e(t('ყველა სიახლე', 'All news')) ?> →</a>
    </div>

    <div class="news-list">
      <?php foreach ($news as $n): ?>
        <a class="news-row" href="<?= e(url_to('/news/' . (int)$n['id'])) ?>">
          <div class="news-thumb">
            <?php if (!empty($n['cover'])): ?>
              <img src="<?= e($n['cover']) ?>" alt="">
            <?php else: ?>
              <div class="thumb-ph"></div>
            <?php endif; ?>
          </div>
          <div class="news-info">
            <div class="news-title"><?= e($n['title']) ?></div>
            <div class="news-meta"><?= e($n['published_at'] ?: '') ?></div>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>

  <aside class="content-side">
    <div class="side-card">
      <h3><?= e(t('მუნიციპალური სერვისები', 'Municipal services')) ?></h3>
      <a href="#" class="side-link">📄 <?= e(t('ონლაინ განაცხადი', 'Online application')) ?></a>
      <a href="#" class="side-link">🏛️ <?= e(t('მერის მისაღები', 'Mayor reception')) ?></a>
      <a href="#" class="side-link">📚 <?= e(t('საჯარო ინფორმაცია', 'Public information')) ?></a>
      <a href="#" class="side-link">🚌 <?= e(t('ტრანსპორტი', 'Transport')) ?></a>
      <a href="#" class="side-link">💧 <?= e(t('კომუნალური სერვისები', 'Utilities')) ?></a>
    </div>

    <div class="side-card">
      <h3><?= e(t('სწრაფი ბმულები', 'Quick links')) ?></h3>
      <a href="<?= e(url_to('/page/about')) ?>" class="side-link">ℹ️ <?= e(t('ჩვენს შესახებ', 'About')) ?></a>
      <a href="<?= e(url_to('/departments')) ?>" class="side-link">🏢 <?= e(t('დეპარტამენტები', 'Departments')) ?></a>
      <a href="<?= e(url_to('/news')) ?>" class="side-link">📰 <?= e(t('სიახლეები', 'News')) ?></a>
      <a href="<?= e(base_path()) ?>/admin/" class="side-link">🔐 <?= e(t('ადმინისტრაცია', 'Admin')) ?></a>
    </div>
  </aside>
</section>

<section class="wrap section">
  <div class="section-head">
    <h2><?= e(t('დეპარტამენტები', 'Departments')) ?></h2>
    <a class="link" href="<?= e(url_to('/departments')) ?>"><?= e(t('ყველა დეპარტამენტი', 'All departments')) ?> →</a>
  </div>

  <div class="dept-grid">
    <?php foreach ($depts as $d): ?>
      <a class="dept" href="<?= e(url_to('/departments/' . $d['slug'])) ?>">
        <div class="dept-icon">🏛️</div>
        <div>
          <b><?= e($d['name']) ?></b>
          <p><?= e(mb_substr((string)$d['description'], 0, 90)) ?>...</p>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</section>
