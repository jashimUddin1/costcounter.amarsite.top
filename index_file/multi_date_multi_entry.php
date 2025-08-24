<?php if (!empty($_SESSION['multi_entry_enabled'])): ?>
  <!-- একাধিক এন্ট্রি ফর্ম -->
  <div class="">
    <div class="card p-3 mb-4">
      <form method="POST" action="core_file/add_multiple_entries.php">
        <input type="hidden" name="redirect_query" value="<?= htmlspecialchars($query_string) ?>">
        <div id="multi-entry-container">
          <!-- Default row -->
          <div class="row g-2 mb-2">
            <div class="col-md-2">
              <input type="date" name="entries[0][date]" class="form-control" required value="<?= date('Y-m-d') ?>">
            </div>
            <div class="col-md-4">
              <input type="text" name="entries[0][description]" class="form-control" placeholder="বিবরণ" required>
            </div>
            <div class="col-md-2">
              <input type="number" name="entries[0][amount]" class="form-control" placeholder="৳" required>
            </div>
            <div class="col-md-3">
              <select name="category" class="form-select" required>

                <option value="" disabled selected>ক্যাটাগরি দিন</option>
                <?php
                foreach ($category_groups as $group_name => $cats) {
                  if (!empty($cats)) {
                    echo "<optgroup label='" . htmlspecialchars($group_name, ENT_QUOTES) . "'>";
                    foreach ($cats as $cat) {
                      if (isset($categories[$cat])) {
                        echo "<option value='" . htmlspecialchars($cat, ENT_QUOTES) . "'>" . htmlspecialchars($cat) . "</option>";
                      }
                    }
                    echo "</optgroup>";
                  }
                }

                // Show categories not in any group
                foreach ($categories as $cat_name => $row) {
                  $in_group = false;
                  foreach ($category_groups as $group_cats) {
                    if (in_array($cat_name, $group_cats)) {
                      $in_group = true;
                      break;
                    }
                  }
                  if (!$in_group) {
                    echo "<option value='" . htmlspecialchars($cat_name, ENT_QUOTES) . "'>" . htmlspecialchars($cat_name) . "</option>";
                  }
                }
                ?>
              </select>

            </div>
            <div class="col-md-1">
              <button type="button" class="btn btn-danger remove-entry">✖</button>
            </div>
          </div>
        </div>
        <button type="button" id="addMoreEntry" class="btn btn-secondary mb-2">+ আরও</button>
        <button type="submit" class="btn btn-success">✅ সবগুলো যুক্ত করুন</button>
      </form>
    </div>
  </div>

<?php else: ?>
  <!-- একক এন্ট্রি ফর্ম -->
  <div class="">
    <form class="row g-3 mb-4" method="POST" action="core_file/add_entry.php">
      <input type="hidden" name="redirect_query" value="<?= htmlspecialchars($query_string) ?>">

      <div class="col-md-2">
        <label class="form-label">তারিখ দিন</label>
        <input type="date" name="date" id="trans_date" class="form-control" required value="<?= date('Y-m-d') ?>">
      </div>

      <div class="col-md-4">
        <label class="form-label">খরচের বিবরণ</label>
        <input type="text" name="description" placeholder="সংক্ষিপ্ত বিবরণ দিন" class="form-control" required>
      </div>

      <div class="col-md-2">
        <label class="form-label">পরিমাণ (৳)</label>
        <input type="number" name="amount" step="0.01" placeholder="টাকার পরিমাণ" class="form-control" required>
      </div>

      <div class="col-md-2">
        <label class="form-label">নির্বাচন করুন</label>
        <select name="category" class="form-select" required>
          <option value="" disabled selected>ক্যাটাগরি দিন</option>
          <optgroup label="দৈনন্দিন খরচ">
            <option value="বাজার">বাজার</option>
            <option value="বাহিরেরখরচ">বাহিরের খরচ</option>
            <option value="মোবাইলখরচ">মোবাইল খরচ</option>
            <option value="গাড়িভাড়া">গাড়ি ভাড়া</option>
            <option value="ঘোরাঘুরি">ঘোরাঘুরি</option>
            <option value="কেনাকাটা">কেনাকাটা</option>
          </optgroup>
          <optgroup label="বাড়ি সংক্রান্ত">
            <option value="বাসাভাড়া">বাসা ভাড়া</option>
            <option value="গৃহস্থালীজিনিসপত্র">গৃহস্থালী জিনিসপত্র</option>
            <option value="গৃহস্থালীমেরামত">গৃহস্থালী মেরামত</option>
          </optgroup>
          <optgroup label="ব্যক্তিগত">
            <option value="মালজিনিস">মাল জিনিস</option>
            <option value="কসমেটিক্স">কসমেটিক্স</option>
            <option value="দাওয়াতখরচ">দাওয়াতখরচ</option>
            <option value="বইখাতা">বইখাতা</option>
            <option value="ঔষধ">ঔষধ</option>
            <option value="পরিবার">পরিবার</option>
            <option value="সাইকেলমেরামত">সাইকেল মেরামত</option>
          </optgroup>
          <optgroup label="আর্থিক">
            <option value="প্রাপ্তি">প্রাপ্তি</option>
            <option value="প্রদান">প্রদান</option>
            <option value="আয়">আয়</option>
          </optgroup>
          <option value="অন্যান্য">অন্যান্য</option>
        </select>
      </div>

      <div class="col-md-2">
        <label class="form-label"> ক্লিক করুন</label>
        <button type="submit" class="form-control btn btn-success">✅ যুক্ত করুন</button>
      </div>
    </form>
  </div>
<?php endif; ?>

<!-- JS for Multi Entry -->
<script>
  let entryIndex = 1;

  document.getElementById('addMoreEntry')?.addEventListener('click', () => {
    const container = document.getElementById('multi-entry-container');
    const row = document.createElement('div');
    row.className = 'row g-2 mb-2';
    row.innerHTML = `
    <div class="col-md-2">
      <input type="date" name="entries[${entryIndex}][date]" class="form-control" required value="<?= date('Y-m-d') ?>">
    </div>
    <div class="col-md-4">
      <input type="text" name="entries[${entryIndex}][description]" class="form-control" placeholder="বিবরণ" required>
    </div>
    <div class="col-md-2">
      <input type="number" name="entries[${entryIndex}][amount]" class="form-control" placeholder="৳" required>
    </div>
    <div class="col-md-3">
      <select name="entries[${entryIndex}][category]" class="form-select" required>
        <option disabled selected>ক্যাটাগরি</option>
        <optgroup label="দৈনন্দিন খরচ">
          <option value="বাজার">বাজার</option>
          <option value="বাহিরেরখরচ">বাহিরের খরচ</option>
          <option value="মোবাইলখরচ">মোবাইল খরচ</option>
          <option value="গাড়িভাড়া">গাড়ি ভাড়া</option>
          <option value="ঘোরাঘুরি">ঘোরাঘুরি</option>
          <option value="কেনাকাটা">কেনাকাটা</option>
        </optgroup>
        <optgroup label="বাড়ি সংক্রান্ত">
          <option value="বাসাভাড়া">বাসা ভাড়া</option>
          <option value="গৃহস্থালীজিনিসপত্র">গৃহস্থালী জিনিসপত্র</option>
          <option value="গৃহস্থালীমেরামত">গৃহস্থালী মেরামত</option>
        </optgroup>
        <optgroup label="ব্যক্তিগত">
          <option value="মালজিনিস">মাল জিনিস</option>
          <option value="কসমেটিক্স">কসমেটিক্স</option>
          <option value="দাওয়াতখরচ">দাওয়াতখরচ</option>
          <option value="বইখাতা">বইখাতা</option>
          <option value="ঔষধ">ঔষধ</option>
          <option value="পরিবার">পরিবার</option>
          <option value="সাইকেলমেরামত">সাইকেল মেরামত</option>
        </optgroup>
        <optgroup label="আর্থিক">
          <option value="প্রাপ্তি">প্রাপ্তি</option>
          <option value="প্রদান">প্রদান</option>
          <option value="আয়">আয়</option>
        </optgroup>
        <option value="অন্যান্য">অন্যান্য</option>
      </select>
    </div>
    <div class="col-md-1">
      <button type="button" class="btn btn-danger remove-entry">✖</button>
    </div>
  `;
    container.appendChild(row);
    entryIndex++;
  });

  document.addEventListener('click', function (e) {
    if (e.target && e.target.classList.contains('remove-entry')) {
      e.target.closest('.row').remove();
    }
  });
</script>