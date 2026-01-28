<section class="wrap section">
  <div class="section-head">
    <h2><?= e(t('დეპარტამენტები', 'Departments')) ?></h2>
  </div>

  <div class="dept-grid">
    <?php foreach ($items as $d): ?>
      <a class="dept" href="<?= e(url_to('/departments/' . $d['slug'])) ?>">
        <div class="dept-icon">🏢</div>
        <div>
          <b><?= e($d['name']) ?></b>
          <p><?= e(mb_substr((string)$d['description'], 0, 120)) ?>...</p>
          <div class="small"><?= e($d['phone'] ?? '') ?> • <?= e($d['email'] ?? '') ?></div>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</section>
