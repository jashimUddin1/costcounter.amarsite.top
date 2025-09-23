<?php
// Database থেকে ওই বছরের available months বের করি (order ঠিকভাবে Jan→Dec)
if (!$is_all_year) {
  $month_sql = "SELECT DISTINCT month 
                FROM cost_data 
                WHERE user_id = ? AND year = ?
                ORDER BY CASE month
                  WHEN 'January' THEN 1
                  WHEN 'February' THEN 2
                  WHEN 'March' THEN 3
                  WHEN 'April' THEN 4
                  WHEN 'May' THEN 5
                  WHEN 'June' THEN 6
                  WHEN 'July' THEN 7
                  WHEN 'August' THEN 8
                  WHEN 'September' THEN 9
                  WHEN 'October' THEN 10
                  WHEN 'November' THEN 11
                  WHEN 'December' THEN 12
                END";
  $month_stmt = $con->prepare($month_sql);
  $month_stmt->bind_param("ii", $user_id, $year);
  $month_stmt->execute();
  $month_res = $month_stmt->get_result();

  $available_months = [];
  while ($row = $month_res->fetch_assoc()) {
      $available_months[] = $row['month']; // English মাস নাম
  }
  $month_stmt->close();
} else {
  $available_months = []; // সব বছর হলে মাস দেখাবো না
}

// Year dropdown এর জন্য min year বের করি
$yr_sql = "SELECT MIN(year) as miny FROM cost_data WHERE user_id = ?";
$yr_stmt = $con->prepare($yr_sql);
$yr_stmt->bind_param("i", $user_id);
$yr_stmt->execute();
$yr_res = $yr_stmt->get_result();
$min_year_row = $yr_res->fetch_assoc();
$min_year = (int) ($min_year_row['miny'] ?? 2023);
$yr_stmt->close();
?>

<div class="mb-3">
  <!-- Desktop / Large Device View -->
  <div class="d-none d-lg-flex justify-content-between align-items-center">
    <div>
      <h5 class="mb-0">
        📊 ড্যাশবোর্ড - 
        <?= $is_all_year 
            ? "সব বছর" 
            : ($is_all_month ? "সকল মাস $year_bn" : "{$month_label} {$year_bn}") ?>
      </h5>
    </div>

    <!-- Filter Form -->
    <form class="d-flex align-items-center gap-2" method="get">
      <!-- Year -->
      <select name="year" class="form-select" style="width:120px" onchange="this.form.submit()">
        <option value="All" <?= $is_all_year ? 'selected' : '' ?>>সব বছর</option>
        <?php for ($y = $min_year; $y <= (int) date('Y'); $y++): ?>
          <option value="<?= $y ?>" <?= (!$is_all_year && $y == $year) ? 'selected' : '' ?>>
            <?= en2bn_number($y) ?>
          </option>
        <?php endfor; ?>
      </select>

      <!-- Month (শুধু তখন দেখাবে যখন year != All) -->
      <?php if (!$is_all_year): ?>
        <select name="month" class="form-select" style="width:140px">
          <option value="All" <?= $is_all_month ? 'selected' : '' ?>>সব মাস</option>
          <?php foreach ($available_months as $m_en): ?>
            <option value="<?= $m_en ?>" <?= (!$is_all_month && $m_en == $month) ? 'selected' : '' ?>>
              <?= $month_map[$m_en] ?? $m_en ?>
            </option>
          <?php endforeach; ?>
        </select>
      <?php endif; ?>

      <button class="btn btn-primary" type="submit">দেখুন</button>
    </form>

    <!-- Back Button -->
    <div>
      <a href="../index.php?year=<?= $year ?>&month=<?= htmlspecialchars($month) ?>" class="btn btn-outline-secondary">
        ← হোমে ফিরে যান
      </a>
    </div>
  </div>

  <!-- Mobile / Small Device View -->
  <div class="d-lg-none">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <h6 class="mb-0">
        📊 ড্যাশবোর্ড - 
        <?= $is_all_year 
            ? "সব বছর" 
            : ($is_all_month ? "সকল মাস $year_bn" : "{$month_label} {$year_bn}") ?>
      </h6>
      <a href="../index.php?year=<?= $year ?>&month=<?= htmlspecialchars($month) ?>" class="btn btn-outline-secondary btn-sm">
        ← হোম
      </a>
    </div>

    <!-- Filter Form -->
    <form class="d-flex flex-column gap-2" method="get">
      <!-- Year -->
      <select name="year" class="form-select" onchange="this.form.submit()">
        <option value="All" <?= $is_all_year ? 'selected' : '' ?>>সব বছর</option>
        <?php for ($y = $min_year; $y <= (int) date('Y'); $y++): ?>
          <option value="<?= $y ?>" <?= (!$is_all_year && $y == $year) ? 'selected' : '' ?>>
            <?= en2bn_number($y) ?>
          </option>
        <?php endfor; ?>
      </select>

      <!-- Month (শুধু তখন দেখাবে যখন year != All) -->
      <?php if (!$is_all_year): ?>
        <select name="month" class="form-select">
          <option value="All" <?= $is_all_month ? 'selected' : '' ?>>সব মাস</option>
          <?php foreach ($available_months as $m_en): ?>
            <option value="<?= $m_en ?>" <?= (!$is_all_month && $m_en == $month) ? 'selected' : '' ?>>
              <?= $month_map[$m_en] ?? $m_en ?>
            </option>
          <?php endforeach; ?>
        </select>
      <?php endif; ?>

      <button class="btn btn-primary" type="submit">দেখুন</button>
    </form>
  </div>
</div>
