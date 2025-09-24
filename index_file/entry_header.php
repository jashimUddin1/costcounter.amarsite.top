<!-- index_file/entry_header.php -->
<div class="d-flex justify-content-between align-items-center mb-3 entry-header">

  <!-- Selected Month Badge -->
  <h5 class="mb-0">
    <span class="badge bg-success">
      Selected Month: <?= date("F", mktime(0, 0, 0, $current_month, 1)) ?> - <?= $current_year ?>
    </span>
  </h5>

  <div class="selectedWrapper d-flex">

    <!-- Language Switch -->
    <div class="dropdown me-2">
      <form method="" class="d-inline-block ms-3">
        <select name="" class="form-select form-select-sm d-inline-block w-auto">
          <option>বাংলা</option>
          <option>English</option>
        </select>
      </form>
    </div>

    <!-- Settings Toggle Button -->
    <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="offcanvas"
      data-bs-target="#settingsPanel">
      ⚙️
    </button>
  </div>
</div>

<!-- ⚙️ Offcanvas Settings Panel -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="settingsPanel" aria-labelledby="settingsPanelLabel">
  <div class="offcanvas-header">
    <h5 id="settingsPanelLabel">⚙️ সেটিংস</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>

  <div class="offcanvas-body">
    <form action="core_file/settings_handler.php" method="POST">

      <!-- ➕ Entry Mode -->
      <div class="mb-4">
        <h6>🧾 Entry Mode</h6>

        <div class="form-check">
          <input class="form-check-input" type="radio" name="entry_mode" id="singleEntry" value="single"
            <?= ($_SESSION['entry_mode'] ?? 'single') === 'single' ? 'checked' : '' ?>>
          <label class="form-check-label" for="singleEntry">Single</label>
        </div>

        <div class="form-check">
          <input class="form-check-input" type="radio" name="entry_mode" id="manualEntry" value="manual"
            <?= ($_SESSION['entry_mode'] ?? '') === 'manual' ? 'checked' : '' ?>>
          <label class="form-check-label" for="manualEntry">Manual</label>
        </div>

        <div class="form-check">
          <input class="form-check-input" type="radio" name="entry_mode" id="multipleEntry" value="multiple"
            <?= ($_SESSION['entry_mode'] ?? '') === 'multiple' ? 'checked' : '' ?>>
          <label class="form-check-label" for="multipleEntry">Multiple</label>
        </div>

        <div class="form-check">
          <input class="form-check-input" type="radio" name="entry_mode" id="multiEntryOnePage"
            value="multi_entry_one_page" <?= ($_SESSION['entry_mode'] ?? '') === 'multi_entry_one_page' ? 'checked' : '' ?>>
          <label class="form-check-label" for="multiEntryOnePage">Multi Entry One Page</label>
        </div>
      </div>

      <!-- ✅ Edit Settings -->
      <div class="mb-4">
        <h6>✏️ Edit Options</h6>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="edit_enabled" id="editEnabled"
            <?= !empty($_SESSION['edit_enabled']) ? 'checked' : '' ?>>
          <label class="form-check-label" for="editEnabled">Edit Entry</label>
        </div>

        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="edit_date" id="editDate"
            <?= !empty($_SESSION['edit_date']) ? 'checked' : '' ?>>
          <label class="form-check-label" for="editDate">Edit date</label>
        </div>

        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="edit_balance" id="editBalance"
            <?= !empty($_SESSION['edit_balance']) ? 'checked' : '' ?>>
          <label class="form-check-label" for="editBalance">Edit Balance</label>
        </div>
      </div>

      <!-- 🗑️ Delete Settings -->
      <div class="mb-4">
        <h6>🗑️ Delete Options</h6>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="delete_enabled" id="deleteEnabled"
            <?= !empty($_SESSION['delete_enabled']) ? 'checked' : '' ?>>
          <label class="form-check-label" for="deleteEnabled">Delete Entry</label>
        </div>

        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="delete_day" id="delete_day"
            <?= !empty($_SESSION['delete_day']) ? 'checked' : '' ?>>
          <label class="form-check-label" for="delete_day">Delete All Entry in a day</label>
        </div>
      </div>

      <!-- 📂 Category Options -->
      <div class="mb-4">
        <h6>📂 Category Options</h6>

        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="category_enabled" id="categoryEnabled"
            <?= !empty($_SESSION['category_enabled']) ? 'checked' : '' ?>>
          <label class="form-check-label" for="categoryEnabled">Enable Category Selection</label>
        </div>

        <div id="categoryExtraOptions" style="<?= empty($_SESSION['category_enabled']) ? 'display:none;' : '' ?>">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="category_edit" id="categoryEdit"
              <?= !empty($_SESSION['category_edit']) ? 'checked' : '' ?>>
            <label class="form-check-label" for="categoryEdit">Allow Category Editing</label>
          </div>

          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="category_delete" id="categoryDelete"
              <?= !empty($_SESSION['category_delete']) ? 'checked' : '' ?>>
            <label class="form-check-label" for="categoryDelete">Allow Category Delete</label>
          </div>
        </div>
      </div>

      <div class="mb-4">
        <h6>⚙️ সেটিংস স্ট্যাটাস প্রদর্শন</h6>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="enabled_displayed" id="enabledDisplay"
            <?= !empty($_SESSION['enabled_displayed']) ? 'checked' : '' ?>>
          <label class="form-check-label" for="enabledDisplay">দেখুন</label>
        </div>
      </div>

      <!-- Submit Button ✅ -->
      <button type="submit" name="save_setting_btn" class="btn btn-sm btn-primary">Save Setting</button>
    </form>

    <a href="pages/update_logs.php" class="btn btn-outline-primary mt-3">🔄 View Update Logs</a>
  </div>
</div>

<!-- 🔁 JavaScript to toggle category extra options -->
<script>
  document.addEventListener("DOMContentLoaded", function () {
    const categoryEnabled = document.getElementById("categoryEnabled");
    const categoryExtraOptions = document.getElementById("categoryExtraOptions");

    function toggleCategoryOptions() {
      if (categoryEnabled && categoryExtraOptions) {
        categoryExtraOptions.style.display = categoryEnabled.checked ? "block" : "none";
      }
    }

    if (categoryEnabled) {
      toggleCategoryOptions();
      categoryEnabled.addEventListener("change", toggleCategoryOptions);
    }
  });
</script>
