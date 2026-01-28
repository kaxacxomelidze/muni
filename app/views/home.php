<section class="hero">
  <div class="wrap hero-grid">
    <div class="hero-card">
      <h1><?= e(t('მუნიციპალიტეტის საინფორმაციო პორტალი', 'Municipality Information Portal')) ?></h1>
      <p><?= e(t('სიახლეები, დეპარტამენტები, ოფიციალური ინფორმაცია და სერვისები ერთ სივრცეში.',
                'News, departments, official information and services in one place.')) ?></p>
      <div class="hero-actions">
        <a class="btn primary" href="<?= e(url_to('/news')) ?>"><?= e(t('ნახე სიახლეები', 'View news')) ?></a>
        <a class="btn ghost" href="<?= e(url_to('/departments')) ?>"><?= e(t('დეპარტამენტები', 'Departments')) ?></a>
      </div>
    </div>

    <div class="hero-stats">
      <div class="stat">
        <b>12</b><span><?= e(t('დეპარტამენტი', 'Departments')) ?></span>
      </div>
      <div class="stat">
        <b><?= e((string)count($news)) ?></b><span><?= e(t('ახალი სიახლე', 'Latest news')) ?></span>
      </div>
      <div class="stat">
        <b>24/7</b><span><?= e(t('ინფორმაცია', 'Information')) ?></span>
      </div>
    </div>
  </div>
</section>

<section class="wrap section">
  <div class="section-head">
    <h2><?= e(t('სწრაფი ბმულები', 'Quick links')) ?></h2>
  </div>
  <div class="cards">
    <a class="card" href="<?= e(url_to('/page/about')) ?>"><b><?= e(t('ჩვენს შესახებ', 'About')) ?></b><span><?= e(t('მთავარი ინფორმაცია', 'Main info')) ?></span></a>
    <a class="card" href="<?= e(url_to('/departments')) ?>"><b><?= e(t('დეპარტამენტები', 'Departments')) ?></b><span><?= e(t('სტრუქტურა და კონტაქტი', 'Structure & contact')) ?></span></a>
    <a class="card" href="<?= e(url_to('/news')) ?>"><b><?= e(t('სიახლეები', 'News')) ?></b><span><?= e(t('განახლებები და განცხადებები', 'Updates & announcements')) ?></span></a>
    <a class="card" href="<?= e(url_to('/page/about')) ?>"><b><?= e(t('დოკუმენტები', 'Documents')) ?></b><span><?= e(t('კონცეფცია/ბიუჯეტი/სტრატეგია', 'Concept/Budget/Strategy')) ?></span></a>
  </div>
</section>

<section class="wrap section">
  <div class="section-head">
    <h2><?= e(t('ბოლო სიახლეები', 'Latest news')) ?></h2>
    <a class="link" href="<?= e(url_to('/news')) ?>"><?= e(t('ყველა სიახლე', 'All news')) ?> →</a>
  </div>

  <div class="news-grid">
    <?php foreach ($news as $n): ?>
      <a class="news-card" href="<?= e(url_to('/news/' . (int)$n['id'])) ?>">
        <div class="news-cover">
          <?php if (!empty($n['cover'])): ?>
            <img src="<?= e($n['cover']) ?>" alt="">
          <?php else: ?>
            <div class="ph"></div>
          <?php endif; ?>
        </div>
        <div class="news-body">
          <div class="news-meta"><?= e($n['published_at'] ?: '') ?></div>
          <b class="news-title"><?= e($n['title']) ?></b>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
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
