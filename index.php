<!-- #region -->
<?php //pure PHP code
session_start();
include("db/dbcon.php");

// -----------------------------
// Authentication check
if (!isset($_SESSION['authenticated'])) {
  header("location: login/index.php");
  exit();
}

$user_id = $_SESSION['auth_user']['id'];
$query_string = $_SERVER['QUERY_STRING'];

// English → Bangla digit
function en2bn_number($str)
{
  $eng = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
  $bn = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
  return str_replace($eng, $bn, $str);
}

function bn2en_number($bn_number)
{
  $bn_digits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
  $en_digits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

  // str_replace দিয়ে সব বাংলা সংখ্যা ইংরেজিতে রূপান্তর করা
  $en_number = str_replace($bn_digits, $en_digits, $bn_number);

  return $en_number;
}

// English → Bangla Month
function en2bn_month($engMonth)
{
  $months_bn = [
    'January' => 'জানুয়ারি',
    'February' => 'ফেব্রুয়ারি',
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
  return $months_bn[$engMonth] ?? $engMonth;
}

// English → Bangla Day
function en2bn_dayName($engDay)
{
  $days_bn = [
    'Saturday' => 'শনিবার',
    'Sunday' => 'রবিবার',
    'Monday' => 'সোমবার',
    'Tuesday' => 'মঙ্গলবার',
    'Wednesday' => 'বুধবার',
    'Thursday' => 'বৃহস্পতিবার',
    'Friday' => 'শুক্রবার'
  ];
  return $days_bn[$engDay] ?? $engDay;
}

// Full Bangla Date
function bn_full_date($date_string)
{
  $timestamp = strtotime($date_string);
  $day_num = en2bn_number(date('j', $timestamp));
  $month_bn = en2bn_month(date('F', $timestamp));
  $year_bn = en2bn_number(date('Y', $timestamp));
  $day_bn = en2bn_dayName(date('l', $timestamp));

  return "{$day_num} {$month_bn} {$year_bn} | {$day_bn}";
}


// Common function: fetch all rows into array
function fetchAllAssoc($stmt)
{
  $res = $stmt->get_result();
  $data = [];
  while ($row = $res->fetch_assoc()) {
    $data[] = $row;
  }
  return $data;
}

// Current year & month detect
if (!isset($_GET['year']) || !isset($_GET['month'])) {
  // সর্বশেষ ডেটা থেকে বছর/মাস বের করা
  $latest_sql = "SELECT YEAR(date) AS y, MONTH(date) AS m FROM cost_data WHERE user_id = ? ORDER BY date DESC LIMIT 1";
  $stmt = $con->prepare($latest_sql);
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $latest_res = $stmt->get_result();
  if ($latest_res && $latest_res->num_rows > 0) {
    $latest = $latest_res->fetch_assoc();
    $current_year = $latest['y'];
    $current_month = $latest['m'];
  } else {
    $current_year = date('Y');
    $current_month = date('n');
  }
  $stmt->close();
} else {
  $current_year = intval($_GET['year']);
  $month_input = $_GET['month'];

  // month = নাম হলে number এ কনভার্ট
  if (!is_numeric($month_input)) {
    $time = strtotime("1 " . $month_input . " " . $current_year);
    $current_month = $time ? date('n', $time) : date('n'); // fallback → current month
  } else {
    $current_month = intval($month_input);
  }
}

// -----------------------------
// Year List
$years = [];
$stmtYear = $con->prepare("SELECT DISTINCT YEAR(date) as year FROM cost_data WHERE user_id = ? ORDER BY year DESC");
$stmtYear->bind_param("i", $user_id);
$stmtYear->execute();
$res = $stmtYear->get_result();
while ($row = $res->fetch_assoc()) {
  $years[] = $row['year'];
}
$stmtYear->close();

// -----------------------------
// Month List
$months = [];
$stmtMonth = $con->prepare("SELECT DISTINCT MONTH(date) as m, MONTHNAME(date) as mn  FROM cost_data  WHERE user_id = ? AND YEAR(date) = ?  ORDER BY m ASC");
$stmtMonth->bind_param("ii", $user_id, $current_year);
$stmtMonth->execute();
$res = $stmtMonth->get_result();
while ($row = $res->fetch_assoc()) {
  $months[$row['m']] = $row['mn'];
}
$stmtMonth->close();

// -----------------------------
// Transaction Data
$transQuery = "SELECT id, date, amount, category, description FROM cost_data WHERE user_id = ? AND YEAR(date) = ? AND MONTH(date) = ? ORDER BY date ASC";
$stmtTrans = $con->prepare($transQuery);
$stmtTrans->bind_param("iii", $user_id, $current_year, $current_month);
$stmtTrans->execute();
$transResult = $stmtTrans->get_result();

$total_monthly_cost = 0;
$grouped_data = [];
$excluded_categories = ['প্রাপ্তি', 'প্রদান', 'আয়'];

while ($row = $transResult->fetch_assoc()) {
  $date = date('d-m-Y', strtotime($row['date']));
  $grouped_data[$date][] = $row;
  if (!in_array($row['category'], $excluded_categories)) {
    $total_monthly_cost += $row['amount'];
  }
}
$stmtTrans->close();

// -----------------------------
// Sorting
$sort_order = ($_GET['sort'] ?? 'asc') === 'asc' ? SORT_ASC : SORT_DESC;
($sort_order === SORT_ASC) ? ksort($grouped_data) : krsort($grouped_data);


// -----------------------------
// Categories
$stmt = $con->prepare("SELECT id, category_name, category_keywords FROM categories WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
$categories = [];
while ($row = $res->fetch_assoc()) {
  $categories[$row['category_name']] = $row;
}
$stmt->close();

// -----------------------------
// Category Groups
$stmt = $con->prepare("SELECT * FROM category_groups WHERE user_id = ? ORDER BY id");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
$category_groups = [];
while ($row = $res->fetch_assoc()) {
  $cats = array_map('trim', explode(',', $row['group_category']));
  $category_groups[$row['group_name']] = $cats;
}
$stmt->close();
?>
<!-- #endregion -->

<?php include "index_file/header.php" ?>
<?php include "index_file/header_nav.php" ?>


<div class="container mt-3">

  <?php include "index_file/entry_header.php" ?>

  <!-- Session Deploy -->
  <div class="session_section">
    <?php include "includes/session_modal.php"; ?>
  </div>



  <?php // ✅ Data Entry Form Selector
  $entry_mode = $_SESSION['entry_mode'] ?? 'single';

  if ($entry_mode === 'single') {
    include "index_file/signle_date_multi_entry.php"; // 👉 Single Entry
  } elseif ($entry_mode === 'manual') {
    include "index_file/data_entry.php"; // 👉 Manual Entry
  } elseif ($entry_mode === 'multiple') {
    include "index_file/multi_date_multi_entry.php"; // 👉 Multi date multi Entry
  } elseif ($entry_mode === 'multi_entry_one_page') {
    include "index_file/multi_entry_one_page.php"; // 👉 One Page Entry
  }
  ?>



  <?php if (!empty($_SESSION['enabled_displayed'])): ?>
    <!-- ⚙️ Settings Status Info -->
    <div class="mb-3">

      <span class="badge bg-warning me-2">
        <?= !empty($_SESSION['edit_enabled']) ? '✏️ Edit Entry On ✅ আছে' : "<span style='color:red'>✏️ Edit Entry Off ❌ আছে</span>" ?>
      </span>

      <span class="badge bg-warning me-2">
        <?= !empty($_SESSION['edit_date']) ? '✏️ Edit Date On ✅ আছে' : "<span style='color:red'>✏️ Edit Date Off ❌ আছে</span>" ?>
      </span>

      <span class="badge bg-warning me-2">
        <?= !empty($_SESSION['edit_balance']) ? '✏️ Edit Balance On ✅ আছে' : "<span style='color:red'>✏️ Edit Balance Off ❌ আছে</span>" ?>
      </span>

      <span class="badge bg-warning me-2">
        <?= !empty($_SESSION['delete_enabled']) ? '🗑️ Delete Entry On ✅ আছে' : "<span style='color:red'>🗑️ Delete Entry Off ❌ আছে</span>" ?>
      </span>

      <span class="badge bg-warning me-2">
        <?= !empty($_SESSION['delete_day']) ? '🗑️ Delete Day On ✅ আছে' : "<span style='color:red'>🗑️ Delete Day Off ❌ আছে</span>" ?>
      </span>

      <span class="badge bg-warning me-2">
        <?= !empty($_SESSION['multi_entry_enabled']) ? "<span style='color:white'>➕ Multiple Entry Mode ✅ আছে </span>" : "<span style='color:white'> Single Entry Mode ✅ আছে</span>" ?>
      </span>

      <span class="badge bg-warning me-2">
        <?= !empty($_SESSION['category_enabled']) ? '📂 Category Enable ✅ আছে' : "<span style='color:red'>📂 Category Mode Off ❌ আছে</span>" ?>
      </span>

      <span class="badge bg-warning me-2">
        <?= !empty($_SESSION['category_edit']) ? '📂 Category Edit ✅ আছে' : "<span style='color:red'>📂 Category Edit Mode Off ❌ আছে</span>" ?>
      </span>

      <span class="badge bg-warning me-2">
        <?= !empty($_SESSION['category_delete']) ? '📂 Category Delete Enable ✅ আছে' : "<span style='color:red'>📂 Category Delete Mode Off ❌ আছে</span>" ?>
      </span>

    </div>
  <?php endif; ?>

  <hr>

  <?php include "index_file/body_nav.php" ?>

  <hr>

  <!-- 👇 মাসিক খরচ -->
  <?php include "index_file/final_body.php"; ?>

</div>

<!-- #region modal  -->

<!-- ব্যালেন্স এডিট Modal -->
<?php include "index_file/edit_balance_modal.php" ?>

<!-- Edit Cost data Modal -->
<?php include "index_file/edit_costdata_modal.php" ?>

<!-- Edit Date Modal -->
<?php include "index_file/edit_date_modal.php" ?>

<!-- Set Balance Modal -->
<?php include 'index_file/setBalanceModal.php' ?>

<!-- #endregion modal end -->


<script>
  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.edit-btn').forEach(function (button) {
      button.addEventListener('click', function () {
        document.getElementById('edit-setting-id').value = button.getAttribute('data-id');
        document.getElementById('edit-setting-value').value = button.getAttribute('data-value');
      });
    });

    document.querySelectorAll('.edit-date-btn').forEach(function (button) {
      button.addEventListener('click', function () {
        const date = button.getAttribute('data-date');
        document.getElementById('edit-old-date').value = date;
        document.getElementById('edit-new-date').value = date;
      });
    });

    const transDateInput = document.getElementById('trans_date');
    const dayNameInput = document.getElementById('day_name');
    if (transDateInput) {
      transDateInput.addEventListener('change', function () {
        const date = new Date(this.value);
        const banglaDays = ['রবিবার', 'সোমবার', 'মঙ্গলবার', 'বুধবার', 'বৃহস্পতিবার', 'শুক্রবার', 'শনিবার'];
        dayNameInput.value = banglaDays[date.getDay()];
      });
    }
  });
</script>

<?php include 'includes/footer.php'; ?>