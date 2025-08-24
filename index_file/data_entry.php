<?php // --- Fetch categories ---
$categories = [];
$stmt = $con->prepare("SELECT id, category_name, category_keywords FROM categories WHERE user_id = ? ");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
  $categories[$row['category_name']] = $row;
}
$stmt->close();

// --- Fetch category groups ---
$category_groups = [];
$stmt = $con->prepare("SELECT * FROM category_groups WHERE user_id = ? ORDER BY id");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
  // explode categories string into array
  $cats = array_map('trim', explode(',', $row['group_category']));
  $category_groups[$row['group_name']] = $cats;
}
$stmt->close();
?>

<div class="">
  <!-- Entry Form -->
  <form class="row g-3 mb-4" method="POST" action="core_file/add_entry.php">

    <!-- Hidden Query Parameters -->
    <input type="hidden" name="redirect_query" value="<?= htmlspecialchars($query_string) ?>">

    <div class="col-md-2">
      <label class="form-label">তারিখ দিন</label>
      <input type="date" name="date" id="trans_date" placeholder="তারিখ" class="form-control" required
        value="<?= date('Y-m-d') ?>">
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

    <div class="col-md-2">
      <label class="form-label">
        ক্লিক করুন
      </label>
      <button type="submit" class="form-control btn btn-success">যোগ করুন</button>
    </div>


  </form>
</div>