<?php
$dashboard = $_GET['dashboard'] ?? '1';

// মাস বের করব শুধু তখন যখন All Year না আর dashboard = 3 না
if (!$is_all_year && $dashboard != '3') {
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
  $available_months = []; // dashboard=3 বা All Year হলে মাস দেখাবো না
}

// ✅ এখন Year dropdown এর জন্য শুধু database এ থাকা year নেব
$yr_sql = "SELECT DISTINCT year 
           FROM cost_data 
           WHERE user_id = ? 
           ORDER BY year ASC";
$yr_stmt = $con->prepare($yr_sql);
$yr_stmt->bind_param("i", $user_id);
$yr_stmt->execute();
$yr_res = $yr_stmt->get_result();

$available_years = [];
while ($row = $yr_res->fetch_assoc()) {
    $available_years[] = (int)$row['year'];
}
$yr_stmt->close();
?>

<div class="mb-3">
  <!-- Desktop / Large Device View -->
  <div class="d-none d-lg-flex justify-content-between align-items-center">
    <div>
      <h5 class="mb-0">
        <?php
        if ($is_all_year) {
          echo "সব বছর";
        } elseif ($is_all_month) {
          echo "সকল মাস $year_bn";
        } else {
          echo "{$month_label} {$year_bn}";
        }
        ?>
      </h5>
    </div>

    <!-- Filter Form -->
    <form class="d-flex align-items-center gap-2" method="get">
      <!-- Year -->
      <select name="year" class="form-select" style="width:120px" onchange="this.form.submit()">
        <option value="All" <?= $is_all_year ? 'selected' : '' ?>>সব বছর</option>
        <?php foreach ($available_years as $y): ?>
          <option value="<?= $y ?>" <?= (!$is_all_year && $y == $year) ? 'selected' : '' ?>>
            <?= en2bn_number($y) ?>
          </option>
        <?php endforeach; ?>
      </select>

      <!-- Month (Dashboard One/Two তে থাকবে, কিন্তু Three তে থাকবে না) -->
      <?php if (!$is_all_year && $dashboard != '3'): ?>
        <select name="month" class="form-select" style="width:140px">
          <option value="All" <?= $is_all_month ? 'selected' : '' ?>>সব মাস</option>
          <?php foreach ($available_months as $m_en): ?>
            <option value="<?= $m_en ?>" <?= (!$is_all_month && $m_en == $month) ? 'selected' : '' ?>>
              <?= $month_map[$m_en] ?? $m_en ?>
            </option>
          <?php endforeach; ?>
        </select>
      <?php endif; ?>

      <input type="hidden" name="dashboard" value="<?= $dashboard ?>">
      <button class="btn btn-primary" type="submit">দেখুন</button>
    </form>

    <div>
      <!-- Settings button -->
      <button class="btn btn-outline-secondary" type="button" data-bs-toggle="offcanvas" data-bs-target="#settingsMenu">
        ⚙️
      </button>
    </div>
  </div>

  <!-- Mobile / Small Device View -->
  <div class="d-lg-none">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <h6 class="mb-0">
        <?php
        if ($is_all_year) {
          echo "সব বছর";
        } elseif ($is_all_month) {
          echo "সকল মাস $year_bn";
        } else {
          echo "{$month_label} {$year_bn}";
        }
        ?>
      </h6>
      <!-- Settings button -->
      <button class="btn btn-outline-secondary" type="button" data-bs-toggle="offcanvas" data-bs-target="#settingsMenu">
        ⚙️
      </button>
    </div>

    <!-- Filter Form -->
    <form class="d-flex flex-column gap-2" method="get">
      <!-- Year -->
      <select name="year" class="form-select" onchange="this.form.submit()">
        <option value="All" <?= $is_all_year ? 'selected' : '' ?>>সব বছর</option>
        <?php foreach ($available_years as $y): ?>
          <option value="<?= $y ?>" <?= (!$is_all_year && $y == $year) ? 'selected' : '' ?>>
            <?= en2bn_number($y) ?>
          </option>
        <?php endforeach; ?>
      </select>

      <!-- Month (Dashboard One/Two তে থাকবে, কিন্তু Three তে থাকবে না) -->
      <?php if (!$is_all_year && $dashboard != '3'): ?>
        <select name="month" class="form-select">
          <option value="All" <?= $is_all_month ? 'selected' : '' ?>>সব মাস</option>
          <?php foreach ($available_months as $m_en): ?>
            <option value="<?= $m_en ?>" <?= (!$is_all_month && $m_en == $month) ? 'selected' : '' ?>>
              <?= $month_map[$m_en] ?? $m_en ?>
            </option>
          <?php endforeach; ?>
        </select>
      <?php endif; ?>

      <input type="hidden" name="dashboard" value="<?= $dashboard ?>">
      <button class="btn btn-primary" type="submit">দেখুন</button>
    </form>
  </div>
</div>

<!-- Settings Offcanvas -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="settingsMenu">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title">⚙️ Settings</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body">
    <div class="list-group">
      <a href="index.php?dashboard=1&year=<?= $year ?>&month=<?= htmlspecialchars($month) ?>" 
         class="list-group-item list-group-item-action <?= ($dashboard=='1') ? 'active' : '' ?>">
         Dashboard One
      </a>
      <a href="index.php?dashboard=2&year=<?= $year ?>&month=<?= htmlspecialchars($month) ?>" 
         class="list-group-item list-group-item-action <?= ($dashboard=='2') ? 'active' : '' ?>">
         Dashboard Two
      </a>
      <a href="index.php?dashboard=3&year=<?= $year ?>" 
         class="list-group-item list-group-item-action <?= ($dashboard=='3') ? 'active' : '' ?>">
         Dashboard Three
      </a>
    </div>
  </div>
</div>
