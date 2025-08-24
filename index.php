<?php include "core_file/index_core.php" ?>

<?php include "index_file/header.php" ?>

<?php include "index_file/header_nav.php" ?>



<div class="container mt-3">

  <?php include "index_file/entry_header.php" ?>

  <!-- Session Deploy -->
  <div class="session_section">
    <?php include "includes/session.php"; ?>
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


  <!-- <pre><?php print_r($_SESSION); ?></pre> -->

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
        <?= !empty($_SESSION['multi_entry_enabled']) ? "<span style='color:red'>➕ Multiple Entry Mode ✅ আছে </span>" : "<span style='color:white'> Single Entry Mode ✅ আছে</span>" ?>
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

        <?php if (!empty($_SESSION['edit_balance'])): ?>
          <button class="btn btn-sm btn-outline-secondary edit-btn" data-bs-toggle="modal"
            data-bs-target="#editBalanceModal" data-id="<?= $balance_id ?? '' ?>" data-value="<?= $balance ?? '' ?>">
            ✏️
          </button>
        <?php endif; ?>

      </div>
    </div>

    <?php
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
              <!-- Delete All Entries of This Date -->
              <a href="core_file/delete_day_entries.php?date=<?= date('d-m-Y', strtotime($date)) ?>"
                class="btn btn-sm btn-outline-danger"
                onclick="return confirm('🔴 আপনি কি নিশ্চিত যে, <?= date('d/m/Y', strtotime($date)) ?> তারিখের সব এন্ট্রি মুছে ফেলতে চান?')">
                🗑️
              </a>
            <?php endif; ?>

          </div>
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
                <?= eng_to_bn($i) ?>. <?= eng_to_bn($txn['description']) ?>     <?= eng_to_bn($txn['amount']) ?> টাকা
                (<?= $txn['category'] ?>)
              </div>
              <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary rounded-pill"><?= eng_to_bn($txn['amount']) ?>৳</span>

                <!-- Edit Button -->
                <?php if (!empty($_SESSION['edit_enabled'])): ?>
                  <button class="btn btn-sm btn-outline-warning edit-btn" data-id="<?= $txn['id'] ?>"
                    data-description="<?= htmlspecialchars($txn['description']) ?>" data-amount="<?= $txn['amount'] ?>"
                    data-category="<?= htmlspecialchars($txn['category']) ?>" data-bs-toggle="modal"
                    data-bs-target="#editCostDataModal">
                    ✏️
                  </button>
                <?php endif; ?>

                <!-- Delete Button -->
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

    <div class="mb-5 mt-5">
      <hr>
    </div>

    <div class="container rounded-3 alert alert-success text-center fs-5 fixed-bottom mb-0">
      ✅ মোট ব্যয়: <strong><?= eng_to_bn($total_monthly_cost) ?> টাকা</strong>
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