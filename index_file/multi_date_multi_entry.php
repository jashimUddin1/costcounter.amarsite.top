
<?php //merged_multi_date_single_entry.php

  // Get user categories for auto detect + dropdown
  $category_map = [];
  $categories = [];
  $category_groups = [];

  $stmt = $con->prepare("SELECT category_name, category_keywords FROM categories WHERE user_id = ?");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $res = $stmt->get_result();
  while ($row = $res->fetch_assoc()) {
    $cat_name = trim($row['category_name']);
    $cats = trim($row['category_keywords']);
    $categories[$cat_name] = $row;
    $category_map[$cat_name] = $cats !== '' ? array_map('trim', explode(',', $cats)) : [];
    $category_groups['Default'][] = $cat_name; // simple group
  }
  $stmt->close();

  // Bengali to English number converter
  function bn2en_number($string)
  {
    $bn = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
    $en = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    return str_replace($bn, $en, $string);
  }

  // detect category by description
  function detectCategory($description, $category_map)
  {
    $desc_lower = mb_strtolower(trim($description));
    $best_match = 'অন্যান্য';
    $best_length = 0;
    foreach ($category_map as $cat => $keywords) {
      foreach ($keywords as $kw) {
        $kw = mb_strtolower(trim($kw));
        if ($kw == '')
          continue;
        if (mb_strpos($desc_lower, $kw) !== false && mb_strlen($kw) > $best_length) {
          $best_match = $cat;
          $best_length = mb_strlen($kw);
        }
      }
    }
    return $best_match;
  }
?>

<!-- UI Form -->
<div class="card p-3 mb-4">
  <form method="POST" action="core_file/multi_date_multi_core.php">
    <input type="hidden" name="redirect_query" value="<?= htmlspecialchars($_SERVER['QUERY_STRING'] ?? '') ?>">

    <div id="multi-date-container">
      <!-- Default date row -->
      <div class="row g-2 mb-2 date-entry">
        <div class="col-md-2">
          <label class="form-label">তারিখ দিন</label>
          <input type="date" name="entries[0][date]" class="form-control" required value="<?= date('Y-m-d') ?>">
        </div>
        <div class="col-md-10">
          <label class="form-label d-flex align-items-center">
            বিবরণ ও পরিমাণ (কমা দিয়ে দিন)
            <span tabindex="0" class="ms-2 text-primary" data-bs-toggle="tooltip"
              title="প্রতিটি খরচ কমা দিয়ে আলাদা করুন এবং শেষে পরিমাণ দিন।" style="cursor: pointer;">ℹ️</span>
          </label>
          <input type="text" name="entries[0][bulk_description]" class="form-control"
            placeholder="যেমন: খাবার 50, ফল 530" required>
        </div>
      </div>
    </div>

    <button type="button" id="addMoreDate" class="btn btn-secondary mb-2">+ নতুন তারিখ</button>
    <button type="submit" class="btn btn-success">✅ সবগুলো যুক্ত করুন</button>
  </form>
</div>

<!-- JS for Multi Entry -->
<script>
  let dateIndex = 1;
  document.getElementById('addMoreDate')?.addEventListener('click', () => {
    const container = document.getElementById('multi-date-container');
    const row = document.createElement('div');
    row.className = 'row g-2 mb-2 date-entry';
    row.innerHTML = `
    <div class="col-md-2">
      <label class="form-label">তারিখ দিন</label>
      <input type="date" name="entries[${dateIndex}][date]" class="form-control" required value="<?= date('Y-m-d') ?>">
    </div>
    <div class="col-md-9">
      <label class="form-label">বিবরণ ও পরিমাণ (কমা দিয়ে দিন)</label>
      <input type="text" name="entries[${dateIndex}][bulk_description]" class="form-control" placeholder="যেমন: খাবার 50, ফল 530" required>
    </div>
    <div class="col-md-1 text-center">
      <button type="button" class="btn btn-danger remove-date-entry mt-2">✖</button>
    </div>`;
    container.appendChild(row);
    dateIndex++;
  });

  document.addEventListener('click', function (e) {
    if (e.target && e.target.classList.contains('remove-date-entry')) {
      e.target.closest('.date-entry').remove();
    }
  });
</script>
