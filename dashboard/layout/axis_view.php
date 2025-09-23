


<!-- axis_view.php -->
<div class="card mb-3">
  <div class="card-header fw-bold d-flex justify-content-between align-items-center">
    <?php 
      if ($is_all_year) {
        echo "📅 প্রতিবছরের খরচ";
      } elseif ($is_all_month) {
        echo "📅 প্রতিমাসের খরচ";
      } else {
        echo "📅 প্রতিদিনের খরচ";
      }
    ?>
    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#axisFullView">
      ⛶ Full View
    </button>
  </div>
  <div class="card-body" style="max-height:500px">
    <canvas id="axisChart" height="250"></canvas>
  </div>
</div>

<div class="card mb-3">
  <div class="card-header bg-light fw-bold">
    <?php 
      if ($is_all_year) {
        echo "📅 প্রতিবছরের তালিকা";
      } elseif ($is_all_month) {
        echo "📅 প্রতিমাসের তালিকা";
      } else {
        echo "📅 প্রতিদিনের তালিকা";
      }
    ?>
  </div>
  <ul class="list-group list-group-flush">
    <?php if ($is_all_year): ?>
      <?php foreach ($axis_labels as $idx => $year_label): ?>
        <li class="list-group-item d-flex justify-content-between">
          <span><?= $year_label ?> সাল</span>
          <span><?= format_currency_bn($axis_data[$idx] ?? 0) ?></span>
        </li>
      <?php endforeach; ?>
    <?php elseif ($is_all_month): ?>
      <?php foreach ($months_en as $idx => $m_en): ?>
        <li class="list-group-item d-flex justify-content-between">
          <span><?= $month_map[$m_en] ?></span>
          <span><?= format_currency_bn($axis_data[$idx] ?? 0) ?></span>
        </li>
      <?php endforeach; ?>
    <?php else: ?>
      <?php foreach ($axis_data as $d => $val): ?>
        <li class="list-group-item d-flex justify-content-between">
          <span><?= en2bn_number($d + 1) ?> তারিখ</span>
          <span><?= format_currency_bn($val) ?></span>
        </li>
      <?php endforeach; ?>
    <?php endif; ?>
  </ul>
  <div class="card-footer bg-white text-center">
    <span class="fw-bold">💰 মোট খরচ: <?= format_currency_bn(array_sum($axis_data)) ?></span>
  </div>
</div>

