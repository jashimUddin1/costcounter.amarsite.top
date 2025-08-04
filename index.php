<!-- #region start block php code-->
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
$current_year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$current_month = isset($_GET['month']) ? $_GET['month'] : date('F');

// 🔢 বছরের তালিকা আনো
$yearQuery = "SELECT DISTINCT YEAR(date) as year FROM cost_data WHERE user_id = ? ORDER BY year DESC";
$stmtYear = $con->prepare($yearQuery);
$stmtYear->bind_param("i", $user_id);
$stmtYear->execute();
$yearResult = $stmtYear->get_result();
$years = [];
while ($row = $yearResult->fetch_assoc()) {
  $years[] = $row['year'];
}
$stmtYear->close();

// 📆 মাসের তালিকা আনো
$monthQuery = "SELECT DISTINCT MONTH(date) as month_number, MONTHNAME(date) as month_name FROM cost_data WHERE user_id = ? AND YEAR(date) = ? ORDER BY month_number ASC";
$stmtMonth = $con->prepare($monthQuery);
$stmtMonth->bind_param("ii", $user_id, $current_year);
$stmtMonth->execute();
$monthResult = $stmtMonth->get_result();
$months = [];
while ($row = $monthResult->fetch_assoc()) {
  $months[] = $row['month_name'];
}
$stmtMonth->close();

// 📋 ট্রান্সেকশন ডাটা আনো
$transQuery = "SELECT * FROM cost_data WHERE user_id = ? AND YEAR(date) = ? AND MONTHNAME(date) = ? ORDER BY date ASC";
$stmtTrans = $con->prepare($transQuery);
$stmtTrans->bind_param("iis", $user_id, $current_year, $current_month);
$stmtTrans->execute();
$transResult = $stmtTrans->get_result();

// 🔢 ব্যালেন্স আনো (settings টেবিল থেকে)
$balance = 0;
$setting_query = "SELECT * FROM settings WHERE `key` = 'balance' LIMIT 1";
$setting_result = mysqli_query($con, $setting_query);
if ($setting_result && mysqli_num_rows($setting_result) > 0) {
  $row = mysqli_fetch_assoc($setting_result);
  $balance = intval($row['value']); // ✅ দশমিক ছাড়া দেখানোর জন্য intval
}

// 💰 মাসিক ব্যালেন্স (যদি আলাদা থাকে)
$monthly_balance = 0;
$balanceQuery = "SELECT amount FROM cost_data WHERE user_id = ? AND year = ? AND month = ?";
$stmtBalance = $con->prepare($balanceQuery);
$stmtBalance->bind_param("iis", $user_id, $current_year, $current_month);
$stmtBalance->execute();
$balanceResult = $stmtBalance->get_result();
if ($row = $balanceResult->fetch_assoc()) {
  $monthly_balance = $row['amount'];
}
$stmtBalance->close();

// 🔄 ডেট অনুযায়ী ট্রান্সেকশন গ্রুপ করো
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

$sort_order = $_GET['sort'] ?? 'asc'; // default DESC

if ($sort_order === 'asc') {
  ksort($grouped_data); // পুরাতন আগে
} else {
  krsort($grouped_data); // নতুন আগে
}


// ✅ ফর্ম সাবমিট করলে নতুন ডাটা ইনসার্ট করো
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $date = $_POST['date'];
  $description = $_POST['description'];
  $amount = floatval($_POST['amount']);
  $category = $_POST['category'];

  $insertQuery = "INSERT INTO cost_data (user_id, date, description, amount, category) VALUES (?, ?, ?, ?, ?)";
  $stmtInsert = $con->prepare($insertQuery);
  $stmtInsert->bind_param("issds", $user_id, $date, $description, $amount, $category);
  $stmtInsert->execute();
  $stmtInsert->close();

  header("Location: index.php?year={$current_year}&month={$current_month}");
  exit();
}
//
?>
<!-- #endregion php code end-->


<?php include "index_file/header.php" ?>

<?php include "index_file/header_nav.php" ?>



<div class="container mt-3">

  <?php include "index_file/entry_header.php" ?>

  <!-- Session Deploy -->
  <div class="session_section">
    <?php include "includes/session.php"; ?>
  </div>

  <!-- Data Entry Form -->
  <?php include "index_file/data_entry.php" ?>

  <hr>

  <?php include "index_file/body_nav.php" ?>

  <hr>

  <!-- 👇 মাসিক খরচ শুরু -->
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
        <h4 class="mb-0">অবশিষ্ট <span id="balanceAmount"><?= number_format($balance, 0) ?></span> টাকা</h4>
        <button class="btn btn-sm btn-outline-secondary edit-btn" data-bs-toggle="modal"
          data-bs-target="#editBalanceModal" data-id="<?= $balance_id ?? '' ?>" data-value="<?= $balance ?? '' ?>">
          ✏️
        </button>
      </div>
    </div>

    <?php
    function eng_to_bn($str)
    {
      $eng = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
      $bn = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
      return str_replace($eng, $bn, $str);
    }

function bn_full_date($date_string) {
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

      return eng_to_bn($day_num) . ' ' . $months_bn[$month] . ' ' . eng_to_bn($year) . ' | ' . $days_bn[$day_eng] ;
    }
    ?>


    <?php foreach ($grouped_data as $date => $records): ?>
      <div class="card mb-3">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
          <div>
            <strong><?= bn_full_date($date) ?></strong>

          </div>

          <button class="btn btn-sm btn-outline-secondary edit-date-btn" data-bs-toggle="modal"
            data-bs-target="#editDateModal" data-date="<?= date('Y-m-d', strtotime($date)) ?>">
            ✏️ তারিখ পরিবর্তন
          </button>
        </div>

        <div class="card-body">
          <?php $total = 0;
          $i = 1;
          echo '<ul class="list-group list-group-flush">'; ?>
          <?php foreach ($records as $txn): ?>
            <?php if (!in_array($txn['category'], $excluded_categories)) {
              $total += $txn['amount'];
            } ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <div>
                <?= eng_to_bn($i) ?>. <?= $txn['description'] ?>     <?= eng_to_bn($txn['amount']) ?> টাকা
                (<?= $txn['category'] ?>)
              </div>
              <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary rounded-pill"><?= eng_to_bn($txn['amount']) ?>৳</span>

                <!-- Edit Button -->
                <button class="btn btn-sm btn-outline-warning edit-btn" data-id="<?= $txn['id'] ?>"
                  data-description="<?= htmlspecialchars($txn['description']) ?>" data-amount="<?= $txn['amount'] ?>"
                  data-category="<?= htmlspecialchars($txn['category']) ?>" data-bs-toggle="modal"
                  data-bs-target="#editCostDataModal">
                  ✏️
                </button>

                <!-- Delete Button -->
                <a href="core_file/delete_entry.php?id=<?= $txn['id'] ?>" class="btn btn-sm btn-outline-danger"
                  onclick="return confirm('তুমি কি এই এন্ট্রিটি মুছে ফেলতে চাও?')">🗑️</a>
              </div>
            </li>
            <?php $i++; endforeach; ?>
          </ul>
          <div class="mt-2 fw-bold">🔸 মোট: <?= eng_to_bn($total) ?> টাকা</div>
        </div>
      </div>
    <?php endforeach; ?>



    <div class="alert alert-success text-center fs-5">
      ✅ মোট ব্যয়: <strong><?= $total_monthly_cost ?> টাকা</strong>
    </div>
  </div>

</div>

<!-- #region modal  -->

<!-- ব্যালেন্স এডিট Modal -->
<?php include "index_file/edit_balance_modal.php" ?>

<!-- Edit Cost data Modal -->
<?php include "index_file/edit_costdata_modal.php" ?>

<!-- Edit Date Modal -->
<?php include "index_file/edit_date_modal.php" ?>

<!-- এডিট মোডাল -->
<?php include "index_file/edit_entry_modal.php" ?>

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

<?php include('includes/footer.php'); ?>