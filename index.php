<?php

session_start();
include("db/dbcon.php");

// ইউজার লগইন চেক
if (!isset($_SESSION['authenticated'])) {
  header("location: login/index.php");
  exit();
}

$user_id = $_SESSION['auth_user']['id'];
$query_string = $_SERVER['QUERY_STRING'];

//for current month and year
// if (!isset($_GET['year']) || !isset($_GET['month'])) {
//   $latest_sql = "SELECT DATE_FORMAT(date, '%Y') AS y, DATE_FORMAT(date, '%m') AS m
//                  FROM cost_data
//                  WHERE user_id = '$user_id'
//                  ORDER BY date DESC
//                  LIMIT 1";
//   $latest_res = mysqli_query($con, $latest_sql);

//   if ($latest_res && mysqli_num_rows($latest_res) > 0) {
//     $latest = mysqli_fetch_assoc($latest_res);
//     $current_year = $latest['y'];
//     $current_month = $latest['m'];
//   } else {
//     $current_year = date('Y');
//     $current_month = date('m');
//   }
// } else {
//   $current_year = intval($_GET['year']);
//   $month_input = trim($_GET['month']);

//   $month_map = [
//     "January" => "January", "February" => "February", "March" => "March",
//     "April" => "April", "May" => "May", "June" => "June",
//     "July" => "July", "August" => "August", "September" => "September",
//     "October" => "October", "November" => "November", "December" => "December"
//   ];

//   $month_input = ucfirst(strtolower($month_input));
//   if (isset($month_map[$month_input])) {
//     $current_month = $month_map[$month_input];
//   } else {
//     $current_month = str_pad($month_input, 2, '0', STR_PAD_LEFT);
//   }
// }




//for current month and year
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
}







function eng_to_bn($str)
{
  $eng = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
  $bn = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
  return str_replace($eng, $bn, $str);
}

function bn_full_date($date_string)
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

  $days_bn = [
    'Saturday' => 'শনিবার',
    'Sunday' => 'রবিবার',
    'Monday' => 'সোমবার',
    'Tuesday' => 'মঙ্গলবার',
    'Wednesday' => 'বুধবার',
    'Thursday' => 'বৃহস্পতিবার',
    'Friday' => 'শুক্রবার'
  ];

  $timestamp = strtotime($date_string);
  $day_num = date('j', $timestamp); // 1-31 without leading zero
  $month = date('F', $timestamp); // Full month name
  $year = date('Y', $timestamp);
  $day_eng = date('l', $timestamp);

  return eng_to_bn($day_num) . ' ' . $months_bn[$month] . ' ' . eng_to_bn($year) . ' | ' . $days_bn[$day_eng];
}
?>

<?php include "core_file/index_core.php" ?>

<?php include "index_file/header.php" ?>

<?php include "index_file/header_nav.php" ?>



<div class="container mt-3">

  <?php include "index_file/entry_header.php" ?>

  <!-- Session Deploy -->
  <div class="session_section">
    <?php include "includes/session_modal.php"; ?>
  </div>

  <!-- ✅ Data Entry Form Selector -->
  <?php
  // Default fallback: single entry form
  if (empty($_SESSION['multi_entry_enabled'])) {
    include "index_file/data_entry.php"; // 👉 Single Entry Mode
  }
  // Multiple Entry Mode
  else {
    $entryTypes = $_SESSION['entry_type_select'] ?? [];

    if (in_array('single_date', $entryTypes)) {
      include "index_file/signle_date_multi_entry.php"; // 👉 Single Date Multiple Entry
    } elseif (in_array('multi_date', $entryTypes)) {
      include "index_file/multi_date_multi_entry.php"; // 👉 Multi Date Multiple Entry
    } else {
      // fallback if no valid entry_type selected
      $_SESSION['warning'] = '⚠️ অনুগ্রহ করে Data Entry Options নির্বাচন করুন।';
    }
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
    <?php include "index_file/new_body.php"; ?>

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