 <div class="costDetails"> <!-- old_body -->
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

      <?php
      // --- Balance বের করা ---
      $query = "SELECT id, amount FROM balancesheet WHERE user_id = '$user_id' AND date LIKE '$current_year-$current_month-%' AND balance_type = 'balance_bd'
          ORDER BY date DESC
          LIMIT 1";


      $result = mysqli_query($con, $query);

      if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $balance_id = $row['id']; 
        $amount = $row['amount'];
        $has_balance_bd = true;
      } else {
        $amount = 0; // কোনো data না থাকলে default
        $has_balance_bd = false;
      }
      ?>

      <div class="d-flex">
        <h4 class="mb-0">অবশিষ্ট <span id="balanceAmount"><?= eng_to_bn($amount) ?></span> টাকা </h4>

        <?php if (!empty($_SESSION['edit_balance'])): ?>

          <?php if ($has_balance_bd): ?>
            <!-- Edit Balance Button -->
            <button class="btn btn-sm btn-outline-secondary edit-btn" data-bs-toggle="modal"
              data-bs-target="#editBalanceModal" data-id="<?= $balance_id ?>" data-value="<?= $amount ?>"
              data-year="<?= $current_year ?>" data-month="<?= $current_month ?>">
              ✏️
            </button>
          <?php else: ?>
            <!-- Set Balance Button -->
            <button class="btn btn-sm btn-outline-primary edit-btn" data-bs-toggle="modal"
              data-bs-target="#setBalanceModal" data-id="<?= $user_id ?>" data-year="<?= $current_year ?>"
              data-month="<?= $current_month ?>">
              ✏️
            </button>
          <?php endif; ?>

        <?php endif; ?>
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