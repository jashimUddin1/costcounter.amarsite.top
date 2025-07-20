<?php
session_start();
include 'db/dbcon.php';
$page_title = "Home";
include 'includes/header.php';
include 'includes/navbar.php';



// ব্যালেন্স বের করা
$balance = 0;
$setting_query = "SELECT * FROM settings WHERE `key` = 'balance' LIMIT 1";
$setting_result = mysqli_query($con, $setting_query);
if ($setting_result && mysqli_num_rows($setting_result) > 0) {
    $row = mysqli_fetch_assoc($setting_result);
    $balance = $row['value'];
    $balance_id = $row['id'];
}

// ট্রান্সেকশন লোড করা
$transactions = [];
$query = "SELECT * FROM transactions ORDER BY trans_date DESC, serial ASC";
$result = mysqli_query($con, $query);
while ($row = mysqli_fetch_assoc($result)) {
    $transactions[$row['trans_date']][] = $row;
}
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0 mt-3">📋 ডেইলি খরচ এন্ট্রি</h2>
        <a href="summary" class="btn btn-sm btn-primary">Dashboard</a>
    </div>

    <form action="core_php/store.php" method="POST" class="row g-3 mb-4">
        <div class="col-md-3">
            <label class="form-label">তারিখ</label>
            <input type="date" name="trans_date" id="trans_date" class="form-control" required>
        </div>

        <input type="hidden" name="day_name" id="day_name">

        <div class="col-md-3">
            <label class="form-label">খরচের বিবরণ</label>
            <input type="text" name="description" class="form-control" required>
        </div>

        <div class="col-md-2">
            <label class="form-label">পরিমাণ (৳)</label>
            <input type="number" name="amount" class="form-control" required>
        </div>

        <div class="col-md-2">
            <label class="form-label">-- নির্বাচন করুন --</label>
            <select name="category" class="form-select">
                <?php
                $categories = ['বাজার','বাহিরেরখরচ','মোবাইল','বাসা ভাড়া','গাড়িভাড়া ',
                'মালজিনিস','কসমেটিক্স','বইখাতা','ঘোরাঘুরি','কেনাকাটা','ঔষধ',
                'পরিবার','সাইকেলমেরামত','গৃহস্থালী জিনিসপত্র','গৃহস্থালী মেরামত',
                'অন্যান্য','প্রাপ্তি','প্রদান','আয়'];

                foreach ($categories as $cat) {
                    echo "<option value=\"$cat\">$cat</option>";
                }
                ?>
            </select>
        </div>

        <div class="col-md-2 d-grid">
            <label class="form-label invisible">Add</label>
            <button type="submit" class="btn btn-primary">➕ যোগ করুন</button>
        </div>
    </form>
<hr>

<hr>
    <div class="d-flex justify-content-between align-items-center mb-3 mt-3">
        <h4 class="mb-0">🗓️ প্রতিদিনের লেনদেন</h4>
        <div class="d-flex ">
            <h4 class="mb-0">অবশিষ্ট <span id="balanceAmount"><?= $balance ?></span> টাকা</h4>

            <button 
                class="btn btn-sm btn-outline-secondary edit-btn" 
                data-bs-toggle="modal" 
                data-bs-target="#editBalanceModal"
                data-id="<?= $balance_id ?? '' ?>"
                data-value="<?= $balance ?? '' ?>"
            >
                ✏️
            </button>
        </div>
    </div>

    <!-- ব্যালেন্স এডিট Modal -->
    <div class="modal fade" id="editBalanceModal" tabindex="-1" aria-labelledby="editBalanceModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <form action="core_php/update_balance.php" method="POST">
            <input type="hidden" name="id" id="edit-setting-id">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Edit Balance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                  <div class="mb-3">
                      <label for="value" class="form-label">Balance</label>
                      <input type="number" class="form-control" name="value" id="edit-setting-value" required>
                  </div>
              </div>
              <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Save changes</button>
              </div>
            </div>
        </form>
      </div>
    </div>

    <?php
    $grand_total = 0;
    foreach ($transactions as $date => $records):
    ?>
        <div class="card mb-3">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <div>
                    <strong><?= date("d-m-Y", strtotime($date)) ?></strong> | <?= $records[0]['day_name'] ?? '' ?>
                </div>
                <button 
                    class="btn btn-sm btn-outline-secondary edit-date-btn" 
                    data-bs-toggle="modal" 
                    data-bs-target="#editDateModal"
                    data-date="<?= $date ?>"
                >
                    ✏️ তারিখ পরিবর্তন
                </button>
            </div>

            <div class="card-body">
                <?php
                $total = 0;
                echo '<ul class="list-group list-group-flush">';
                foreach ($records as $txn):
                    $total += $txn['amount'];
                ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?= $txn['serial'] ?>. <?= $txn['description'] ?> <?= $txn['amount'] ?> টাকা (<?= $txn['category'] ?>)
                        <span class="badge bg-primary rounded-pill"><?= $txn['amount'] ?>৳</span>
                    </li>
                <?php endforeach; ?>
                </ul>
                <div class="mt-2 fw-bold">🔸 মোট: <?= $total ?> টাকা</div>
                <?php $grand_total += $total; ?>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="alert alert-success text-center fs-5">
        ✅ মোট ব্যয়: <strong><?= $grand_total ?> টাকা</strong>
    </div>
</div>

<!-- Edit Date Modal -->
<div class="modal fade" id="editDateModal" tabindex="-1" aria-labelledby="editDateModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form action="core_php/update_date.php" method="POST">
        <input type="hidden" name="old_date" id="edit-old-date">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">🔧 তারিখ পরিবর্তন করুন</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
              <div class="mb-3">
                  <label for="new_date" class="form-label">নতুন তারিখ</label>
                  <input type="date" class="form-control" name="new_date" id="edit-new-date" required>
              </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">✅ পরিবর্তন করুন</button>
          </div>
        </div>
    </form>
  </div>
</div>

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
