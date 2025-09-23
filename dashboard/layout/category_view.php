
<!-- layout/category_view.php -->
<div class="card mb-3">
  <div class="card-header fw-bold d-flex justify-content-between align-items-center">
    🧾 ক্যাটেগরি ভিত্তিক খরচ
    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#categoryFullView">
      ⛶ Full View
    </button>
  </div>
  <div class="card-body" style="max-height:500px">
    <canvas id="categoryChart" height="250"></canvas>
  </div>
</div>

<div class="card mb-3">
  <div class="card-header bg-light fw-bold">
    📋 <?php 
      if ($is_all_year) {
        echo "বছর ভিত্তিক তালিকা";
      } else {
        echo "ক্যাটেগরি ভিত্তিক তালিকা";
      }
    ?>
  </div>
  <ul class="list-group list-group-flush">
    <?php if ($is_all_year): ?>
      <?php $sn=1; foreach ($category_data as $year_label => $amount): ?>
        <li class="list-group-item d-flex justify-content-between">
          <span><?= en2bn_number($sn++) ?>. <?= htmlspecialchars($year_label) ?></span>
          <span><?= format_currency_bn($amount) ?></span>
        </li>
      <?php endforeach; ?>
    <?php else: ?>
      <?php $sn=1; foreach ($category_data as $cat => $amount): ?>
        <li class="list-group-item d-flex justify-content-between">
          <span><?= en2bn_number($sn++) ?>. <?= htmlspecialchars($cat) ?></span>
          <span><?= format_currency_bn($amount) ?></span>
        </li>
      <?php endforeach; ?>
    <?php endif; ?>
  </ul>
  <div class="card-footer bg-white text-center">
    <span class="fw-bold">💰 মোট খরচ: <?= format_currency_bn($total_expense) ?></span>
  </div>
</div>
