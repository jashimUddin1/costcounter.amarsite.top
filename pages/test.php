<?php // pages/test.php
session_start();
include("../db/dbcon.php");

// ইউজার লগইন চেক
if (!isset($_SESSION['authenticated'])) {
  header("location: login/index.php");
  exit();
}

$user_id = $_SESSION['auth_user']['id'];
$query_string = $_SERVER['QUERY_STRING'];

// --- বর্তমান বছর এবং মাস বের করা ---
if (!isset($_GET['year']) || !isset($_GET['month'])) {
  // database থেকে সর্বশেষ তারিখ বের করা
  $latest_sql = "SELECT DATE_FORMAT(date, '%Y') AS y, DATE_FORMAT(date, '%m') AS m
                 FROM cost_data
                 WHERE user_id = '$user_id'
                 ORDER BY date DESC
                 LIMIT 1";
  $latest_res = mysqli_query($con, $latest_sql);

  if ($latest_res && mysqli_num_rows($latest_res) > 0) {
    $latest = mysqli_fetch_assoc($latest_res);
    $current_year = $latest['y'];
    $current_month = $latest['m'];
  } else {
    // fallback → যদি কোনো data না থাকে
    $current_year = date('Y');
    $current_month = date('m');
  }
} else {
  $current_year = intval($_GET['year']);
  $month_input = trim($_GET['month']); // এখানে July আসবে

  // মাসের নাম → সংখ্যা mapping
  $month_map = [
    "January" => "01", "February" => "02", "March" => "03",
    "April" => "04", "May" => "05", "June" => "06",
    "July" => "07", "August" => "08", "September" => "09",
    "October" => "10", "November" => "11", "December" => "12"
  ];

  // প্রথম অক্ষর capitalize করে চেক করব
  $month_input = ucfirst(strtolower($month_input));

  if (isset($month_map[$month_input])) {
    $current_month = $month_map[$month_input];
  } else {
    // fallback → যদি সংখ্যা পাঠানো হয়
    $current_month = str_pad($month_input, 2, '0', STR_PAD_LEFT);
  }
}


// --- English থেকে Bangla রূপান্তর ---
function eng_to_bn($str)
{
  $eng = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
  $bn = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
  return str_replace($eng, $bn, $str);
}

// --- বাংলায় পুরো তারিখ দেখানো ---
function bn_full_date($date)
{
  $months = [
    "January" => "জানুয়ারি", "February" => "ফেব্রুয়ারি", "March" => "মার্চ",
    "April" => "এপ্রিল", "May" => "মে", "June" => "জুন",
    "July" => "জুলাই", "August" => "আগস্ট", "September" => "সেপ্টেম্বর",
    "October" => "অক্টোবর", "November" => "নভেম্বর", "December" => "ডিসেম্বর"
  ];
  $days = [
    "Saturday" => "শনিবার", "Sunday" => "রবিবার", "Monday" => "সোমবার",
    "Tuesday" => "মঙ্গলবার", "Wednesday" => "বুধবার", "Thursday" => "বৃহস্পতিবার",
    "Friday" => "শুক্রবার"
  ];

  $eng_date = date("j F Y, l", strtotime($date));
  $eng_to_bn = ['0','1','2','3','4','5','6','7','8','9'];
  $bn_digits = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
  $eng_date = str_replace(array_keys($months), array_values($months), $eng_date);
  $eng_date = str_replace(array_keys($days), array_values($days), $eng_date);
  $eng_date = str_replace($eng_to_bn, $bn_digits, $eng_date);

  return $eng_date;
}

// --- মাসের শুরুতে Balance (balance_bd) ---
$query = "SELECT id, amount 
          FROM balancesheet 
          WHERE user_id = '$user_id' 
          AND DATE_FORMAT(date, '%Y-%m') = '$current_year-$current_month'
          AND balance_type = 'balance_bd'
          ORDER BY date ASC 
          LIMIT 1";

$result = mysqli_query($con, $query);

if ($result && mysqli_num_rows($result) > 0) {
  $row = mysqli_fetch_assoc($result);
  $balance_id = $row['id'];
  $current_balance = $row['amount']; // running balance শুরু
  $has_balance_bd = true;
} else {
  $current_balance = 0;
  $has_balance_bd = false;
}

// --- মাসের সব cost_data থেকে এন্ট্রি নিয়ে আসা ---
$txn_query = "SELECT id, date, description, amount, category 
              FROM cost_data 
              WHERE user_id = '$user_id' 
              AND DATE_FORMAT(date, '%Y-%m') = '$current_year-$current_month'
              ORDER BY date ASC";

$txn_result = mysqli_query($con, $txn_query);

$grouped_data = [];
$total_monthly_cost = 0;
$total_monthly_income = 0;

if ($txn_result && mysqli_num_rows($txn_result) > 0) {
  while ($txn = mysqli_fetch_assoc($txn_result)) {
    $date = date('Y-m-d', strtotime($txn['date']));

    // --- Balance Update ---
    if ($txn['category'] === 'আয়' || $txn['category'] === 'প্রাপ্তি') {
      $current_balance += $txn['amount']; 
      $total_monthly_income += $txn['amount']; 
    } else {
      $current_balance -= $txn['amount']; 
      $total_monthly_cost += $txn['amount']; 
    }

    // --- running balance যুক্ত করা ---
    $txn['running_balance'] = $current_balance;

    // --- প্রতিদিনের group বানানো ---
    $grouped_data[$date][] = $txn;
  }
}
?>

<!-- 👇 মাসিক খরচ -->
<div class="costDetails">
  <div class="d-flex justify-content-between align-items-center mb-3 mt-3 monthly-cost-header">
    <h4 class="mb-0">🗓️ মাসের খরচ</h4>

    <form method="GET" class="d-inline-block ms-3">
      <input type="hidden" name="year" value="<?= $current_year ?>">
      <input type="hidden" name="month" value="<?= $current_month ?>">
      <select name="sort" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">
        <option value="asc" <?= ($_GET['sort'] ?? '') === 'asc' ? 'selected' : '' ?>>পুরাতন আগে</option>
        <option value="desc" <?= ($_GET['sort'] ?? '') === 'desc' ? 'selected' : '' ?>>নতুন আগে</option>
      </select>
    </form>

    <div class="d-flex">
      <h4 class="mb-0">অবশিষ্ট <span id="balanceAmount"><?= eng_to_bn($current_balance) ?></span> টাকা </h4>
    </div>
  </div>

  <?php foreach ($grouped_data as $date => $records): ?>
    <div class="card mb-3">
      <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <div>
          <strong><?= bn_full_date($date) ?></strong>
        </div>
      </div>

      <div class="card-body">
        <?php $total = 0; $i = 1; echo '<ul class="list-group list-group-flush">'; ?>
        <?php foreach ($records as $txn): ?>
          <?php if ($txn['category'] !== 'আয়' && $txn['category'] !== 'প্রাপ্তি') {
            $total += $txn['amount'];
          } ?>
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <div>
              <?= eng_to_bn($i) ?>. <?= eng_to_bn($txn['description']) ?>  
              <?= eng_to_bn($txn['amount']) ?> টাকা (<?= $txn['category'] ?>)  
              👉 <strong><?= eng_to_bn($txn['running_balance']) ?>৳</strong>
            </div>
          </li>
          <?php $i++; endforeach; ?>
        </ul>
        <div class="mt-2 fw-bold">🔸 মোট: <?= eng_to_bn($total) ?> টাকা</div>
      </div>
    </div>
  <?php endforeach; ?>

  <div class="mb-5 mt-5"><hr></div>

  <div class="container rounded-3 alert alert-success text-center fs-5 fixed-bottom mb-0">
    ✅ মোট আয়: <strong><?= eng_to_bn($total_monthly_income) ?> টাকা</strong> |
    ✅ মোট ব্যয়: <strong><?= eng_to_bn($total_monthly_cost) ?> টাকা</strong>
  </div>
</div>


<?php // --- পুরাতন ফাইলের কোড ---2nd 
// --- বর্তমান বছর এবং মাস বের করা ---
if (!isset($_GET['year']) || !isset($_GET['month'])) {
  $latest_sql = "SELECT DATE_FORMAT(date, '%Y') AS y, DATE_FORMAT(date, '%m') AS m
                 FROM cost_data
                 WHERE user_id = '$user_id'
                 ORDER BY date DESC
                 LIMIT 1";
  $latest_res = mysqli_query($con, $latest_sql);

  if ($latest_res && mysqli_num_rows($latest_res) > 0) {
    $latest = mysqli_fetch_assoc($latest_res);
    $current_year = $latest['y'];
    $current_month = $latest['m'];
  } else {
    $current_year = date('Y');
    $current_month = date('m');
  }
} else {
  $current_year = intval($_GET['year']);
  $month_input = trim($_GET['month']);

  $month_map = [
    "January" => "01", "February" => "02", "March" => "03",
    "April" => "04", "May" => "05", "June" => "06",
    "July" => "07", "August" => "08", "September" => "09",
    "October" => "10", "November" => "11", "December" => "12"
  ];

  $month_input = ucfirst(strtolower($month_input));
  if (isset($month_map[$month_input])) {
    $current_month = $month_map[$month_input];
  } else {
    $current_month = str_pad($month_input, 2, '0', STR_PAD_LEFT);
  }
}

// --- মাসের শুরুতে Balance ---
$query = "SELECT id, amount 
          FROM balancesheet 
          WHERE user_id = '$user_id' 
          AND YEAR(date) = $current_year AND MONTH(date) = $current_month
          AND balance_type = 'balance_bd'
          ORDER BY date ASC 
          LIMIT 1";

$result = mysqli_query($con, $query);
if ($result && mysqli_num_rows($result) > 0) {
  $row = mysqli_fetch_assoc($result);
  $balance_id = $row['id'];
  $current_balance = $row['amount'];
  $has_balance_bd = true;
} else {
  $current_balance = 0;
  $has_balance_bd = false;
}

// --- Sort control ---
$sort = ($_GET['sort'] ?? 'asc') === 'desc' ? 'DESC' : 'ASC';

// --- মাসের সব ট্রান্স্যাকশন ---
$txn_query = "SELECT id, date, description, amount, category 
              FROM cost_data 
              WHERE user_id = '$user_id' 
              AND YEAR(date) = $current_year AND MONTH(date) = $current_month
              ORDER BY date $sort";

$txn_result = mysqli_query($con, $txn_query);

$grouped_data = [];
$total_monthly_cost = 0;
$total_monthly_income = 0;

if ($txn_result && mysqli_num_rows($txn_result) > 0) {
  while ($txn = mysqli_fetch_assoc($txn_result)) {
    $date = date('Y-m-d', strtotime($txn['date']));

    if ($txn['category'] === 'আয়' || $txn['category'] === 'প্রাপ্তি') {
      $current_balance += $txn['amount'];
      $total_monthly_income += $txn['amount'];
    } else {
      $current_balance -= $txn['amount'];
      $total_monthly_cost += $txn['amount'];
    }

    $txn['running_balance'] = $current_balance;
    $grouped_data[$date][] = $txn;
  }
}
?>

<!-- ================= UI PART ================= -->
<div class="costDetails">
  <div class="d-flex justify-content-between align-items-center mb-3 mt-3 monthly-cost-header">
    <h4 class="mb-0">🗓️ মাসের খরচ</h4>

    <form method="GET" class="d-inline-block ms-3">
      <input type="hidden" name="year" value="<?= $current_year ?>">
      <input type="hidden" name="month" value="<?= $current_month ?>">
      <select name="sort" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">
        <option value="asc" <?= ($_GET['sort'] ?? '') === 'asc' ? 'selected' : '' ?>>পুরাতন আগে</option>
        <option value="desc" <?= ($_GET['sort'] ?? '') === 'desc' ? 'selected' : '' ?>>নতুন আগে</option>
      </select>
    </form>

    <div class="d-flex">
      <h4 class="mb-0">অবশিষ্ট <span id="balanceAmount"><?= eng_to_bn($current_balance) ?></span> টাকা </h4>
    </div>
  </div>

  <?php foreach ($grouped_data as $date => $records): ?>
    <div class="card mb-3">
      <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <div>
          <strong><?= bn_full_date($date) ?></strong>
        </div>

        <div class="rightEditDelete">
          <?php if (!empty($_SESSION['edit_date'])): ?>
            <button class="btn btn-sm btn-outline-secondary edit-date-btn" data-bs-toggle="modal"
              data-bs-target="#editDateModal" data-date="<?= date('Y-m-d', strtotime($date)) ?>">
              ✏️ তারিখ
            </button>
          <?php endif; ?>

          <?php if (!empty($_SESSION['delete_day'])): ?>
            <a href="core_file/delete_day_entries.php?date=<?= date('d-m-Y', strtotime($date)) ?>"
              class="btn btn-sm btn-outline-danger"
              onclick="return confirm('🔴 আপনি কি নিশ্চিত যে, <?= bn_full_date($date) ?> তারিখের সব এন্ট্রি মুছে ফেলতে চান?')">
              🗑️
            </a>
          <?php endif; ?>
        </div>
      </div>

      <div class="card-body">
        <?php $total = 0; $i = 1; echo '<ul class="list-group list-group-flush">'; ?>
        <?php foreach ($records as $txn): ?>
          <?php if ($txn['category'] !== 'আয়' && $txn['category'] !== 'প্রাপ্তি') {
            $total += $txn['amount'];
          } ?>
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <div>
              <?= eng_to_bn($i) ?>. <?= eng_to_bn($txn['description']) ?>  
              <?= eng_to_bn($txn['amount']) ?> টাকা (<?= $txn['category'] ?>)
            </div>
            <div class="d-flex align-items-center gap-2">
         
              <span class="badge bg-primary rounded-pill"> <?= eng_to_bn($txn['running_balance']) ?> ৳</span> <!-- 💰 -->

              <?php if (!empty($_SESSION['edit_enabled'])): ?>
                <button class="btn btn-sm btn-outline-warning edit-btn" 
                  data-id="<?= $txn['id'] ?>"
                  data-date="<?= date('Y-m-d', strtotime($txn['date'])) ?>"
                  data-description="<?= htmlspecialchars($txn['description']) ?>" 
                  data-amount="<?= $txn['amount'] ?>"
                  data-category="<?= htmlspecialchars($txn['category']) ?>" 
                  data-bs-toggle="modal"
                  data-bs-target="#editCostDataModal">
                  ✏️
                </button>
              <?php endif; ?>

              <?php if (!empty($_SESSION['delete_enabled'])): ?>
                <a href="core_file/delete_entry.php?id=<?= $txn['id'] ?>" class="btn btn-sm btn-outline-danger"
                  onclick="return confirm('তুমি কি এই এন্ট্রিটি মুছে ফেলতে চাও?')">🗑️</a>
              <?php endif; ?>
            </div>
          </li>
          <?php $i++; endforeach; ?>
        </ul>
        <div class="mt-2 fw-bold">🔸 মোট: <?= eng_to_bn($total) ?> টাকা</div>
      </div>
    </div>
  <?php endforeach; ?>

  <div class="mb-5 mt-5"><hr></div>

  <div class="container rounded-3 alert alert-success text-center fs-5 fixed-bottom mb-0">
    ✅ মোট আয়: <strong><?= eng_to_bn($total_monthly_income) ?> টাকা</strong> |
    ✅ মোট ব্যয়: <strong><?= eng_to_bn($total_monthly_cost) ?> টাকা</strong>
  </div>
</div>
