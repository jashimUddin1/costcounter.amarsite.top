<!-- 🛠️ Edit Modal -->
<div class="modal fade" id="editCostDataModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="core_file/update_entry.php">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">🔧 খরচ সম্পাদনা করুন</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <input type="hidden" name="id" id="edit-id">

          <div class="mb-3">
            <label for="edit-date" class="form-label">তারিখ</label>
            <input type="date" class="form-control" name="date" id="edit-date" required>
          </div>

          <div class="mb-3">
            <label for="edit-description" class="form-label">বিবরণ</label>
            <input type="text" class="form-control" name="description" id="edit-description" required>
          </div>

          <div class="mb-3">
            <label for="edit-amount" class="form-label">টাকার পরিমাণ</label>
            <input type="number" class="form-control" name="amount" id="edit-amount" required>
          </div>

          <div class="mb-3">
            <label class="form-label">নির্বাচন করুন</label>
            <select name="category" id="edit-category" class="form-select" required>

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

        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">💾 আপডেট করো</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>

  function en2bn_Number(str) {
  const eng = ['0','1','2','3','4','5','6','7','8','9'];
  const bn  = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
  return str.replace(/[0-9]/g, d => bn[d]);
}

document.addEventListener('DOMContentLoaded', function () {
  const editButtons = document.querySelectorAll('.edit-btn');

  editButtons.forEach(function (btn) {
    btn.addEventListener('click', function () {
      const id = btn.getAttribute('data-id');
      const date = btn.getAttribute('data-date');
      const description = btn.getAttribute('data-description');
      const amount = btn.getAttribute('data-amount');
      const category = btn.getAttribute('data-category');

      document.getElementById('edit-id').value = id;
      document.getElementById('edit-date').value = date;
      document.getElementById('edit-description').value = en2bn_Number(description);
      document.getElementById('edit-amount').value = amount;
      document.getElementById('edit-category').value = category;
    });
  });
});

</script>
