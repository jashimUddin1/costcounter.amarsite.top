<?php
session_start();
include "includes/header.php";
include "db/dbcon.php";

if (!isset($_SESSION['authenticated'])) {
    header("Location: login/index.php");
    exit();
}

$user_id = $_SESSION['auth_user']['id'];

$year = $_GET['year'] ?? date('Y');
$month = $_GET['month'] ?? date('F'); // এখানে এখন F মানে English মাসের নাম
$view = $_GET['view'] ?? 'graph';

// English → Bangla মাস ম্যাপ
$month_map = [
    'January' => 'জানুয়ারি',
    'February' => 'ফেব্রুয়ারি',
    'March' => 'মার্চ',
    'April' => 'এপ্রিল',
    'May' => 'মে',
    'June' => 'জুন',
    'July' => 'জুলাই',
    'August' => 'আগস্ট',
    'September' => 'সেপ্টেম্বর',
    'October' => 'অক্টোবর',
    'November' => 'নভেম্বর',
    'December' => 'ডিসেম্বর'
];

function en2bn_number($number) {
    $en = ['0','1','2','3','4','5','6','7','8','9'];
    $bn = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
    return str_replace($en, $bn, $number);
}

// ফিল্টার ড্রপডাউনের জন্য English মাস লিস্ট
$months_en = array_keys($month_map);


// বাংলায় মাসের নাম
$month_name = $month_map[$month] ?? $month;
$year_bn = en2bn_number($year);


$total_expense = 0;
$category_data = [];
$daily_data = [];

// ক্যাটেগরি অনুযায়ী খরচ
$sql = "SELECT category, SUM(amount) as total 
        FROM cost_data 
        WHERE user_id = ? AND year = ? AND month = ?
        GROUP BY category";
$stmt = $con->prepare($sql);
$stmt->bind_param("iss", $user_id, $year, $month); // month string
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $category_data[$row['category']] = $row['total'];
    $total_expense += $row['total'];
}

// প্রতিদিনের খরচ
$sql2 = "SELECT DAY(date) as day, SUM(amount) as total 
         FROM cost_data 
         WHERE user_id = ? AND year = ? AND month = ?
         GROUP BY date";
$stmt2 = $con->prepare($sql2);
$stmt2->bind_param("iss", $user_id, $year, $month);
$stmt2->execute();
$res2 = $stmt2->get_result();
while ($row = $res2->fetch_assoc()) {
    $daily_data[$row['day']] = $row['total'];
}
?>

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4>📊 ড্যাশবোর্ড - <?= $month_name ?> <?= $year_bn ?></h4>
    <a href="index.php" class="btn btn-outline-secondary">← হোমে ফিরে যান</a>
  </div>

  <!-- ফিল্টার ফর্ম -->
  <form class="row g-2 mb-4" method="get">
    <div class="col-md-3">
      <select name="month" class="form-select">
        <?php foreach ($months_en as $m_en): ?>
          <option value="<?= $m_en ?>" <?= ($m_en == $month) ? 'selected' : '' ?>>
            <?= $month_map[$m_en] ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-3">
    <select name="year" class="form-select">
      <?php for ($y = 2023; $y <= date('Y'); $y++): ?>
        <option value="<?= $y ?>" <?= ($y == $year) ? 'selected' : '' ?>>
          <?= en2bn_number($y) ?>
        </option>
      <?php endfor; ?>
    </select>
  </div>
    <div class="col-md-3">
      <select name="view" class="form-select">
        <option value="graph" <?= ($view == 'graph') ? 'selected' : '' ?>>গ্রাফ</option>
        <option value="simple" <?= ($view == 'simple') ? 'selected' : '' ?>>সহজ তালিকা</option>
      </select>
    </div>
    <div class="col-md-3">
      <button class="btn btn-primary w-100">প্রদর্শন করুন</button>
    </div>
  </form>

  <div class="alert alert-info">
    <h5>💰 মোট খরচ: <?= number_format($total_expense, 2) ?> টাকা</h5>
  </div>

<?php if ($view == 'graph'): ?>
  <!-- গ্রাফ ভিউ -->
  <div class="row justify-content-between mb-4">
    <div class="col-md-4">
      <h5>🧾 ক্যাটেগরি ভিত্তিক খরচ</h5>
      <canvas id="categoryChart" height="200"></canvas>
    </div>

    <div class="col-md-6">
      <h5>📅 প্রতিদিনের খরচ</h5>
      <canvas id="dailyChart" height="200"></canvas>
    </div>
  </div>

<?php else: ?>
  <!-- সহজ তালিকা ভিউ -->
  <div class="card">
    <div class="card-header bg-light fw-bold">📋 ক্যাটেগরি ভিত্তিক তালিকা</div>
    <ul class="list-group list-group-flush">
      <?php foreach ($category_data as $cat => $amount): ?>
        <li class="list-group-item d-flex justify-content-between">
          <span><?= $cat ?></span>
          <span><?= number_format($amount, 0) ?> টাকা</span>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>

  <div class="card mt-4">
    <div class="card-header bg-light fw-bold">📅 প্রতিদিনের তালিকা</div>
    <ul class="list-group list-group-flush">
      <?php foreach ($daily_data as $day => $amount): ?>
        <li class="list-group-item d-flex justify-content-between">
          <span><?= $day ?> তারিখ</span>
          <span><?= number_format($amount, 0) ?> টাকা</span>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>
</div>

<?php if ($view == 'graph'): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const categoryCtx = document.getElementById('categoryChart').getContext('2d');
  const dailyCtx = document.getElementById('dailyChart').getContext('2d');

  new Chart(categoryCtx, {
    type: 'doughnut',
    data: {
      labels: <?= json_encode(array_keys($category_data), JSON_UNESCAPED_UNICODE) ?>,
      datasets: [{
        label: 'টাকা',
        data: <?= json_encode(array_values($category_data)) ?>,
        borderWidth: 1
      }]
    },
    options: {
      responsive: true
    }
  });

  new Chart(dailyCtx, {
    type: 'bar',
    data: {
      labels: <?= json_encode(array_keys($daily_data)) ?>,
      datasets: [{
        label: 'প্রতিদিনের খরচ',
        data: <?= json_encode(array_values($daily_data)) ?>,
        backgroundColor: 'rgba(54, 162, 235, 0.5)',
        borderColor: 'rgba(54, 162, 235, 1)',
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      scales: {
        y: { beginAtZero: true }
      }
    }
  });
</script>
<?php endif; ?>

<?php include "includes/footer.php"; ?>
