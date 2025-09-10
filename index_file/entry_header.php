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
            <?= empty($_SESSION['multi_entry_enabled']) ? 'checked' : '' ?>>
          <label class="form-check-label" for="singleEntry">Single</label>
        </div>

        <div class="form-check">
          <input class="form-check-input" type="radio" name="entry_mode" id="multiEntry" value="multiple"
            <?= !empty($_SESSION['multi_entry_enabled']) ? 'checked' : '' ?>>
          <label class="form-check-label" for="multiEntry">Multiple</label>
        </div>

        <!-- ✅ Only for Multiple Mode -->
        <div id="multiEntryOptions" style="display: <?= !empty($_SESSION['multi_entry_enabled']) ? 'block' : 'none' ?>; margin-left: 1rem;">

          <div class="form-check">
            <input class="form-check-input" type="radio" name="entry_type_select[]" value="single_date"
              <?= in_array('single_date', $_SESSION['entry_type_select'] ?? []) ? 'checked' : '' ?>>
            <label class="form-check-label">Single Date Multiple Entry</label>
          </div>

          <div class="form-check">
            <input class="form-check-input" type="radio" name="entry_type_select[]" value="multi_date"
              <?= in_array('multi_date', $_SESSION['entry_type_select'] ?? []) ? 'checked' : '' ?>>
            <label class="form-check-label">Multi Date Multiple Entry</label>
          </div>

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

        <!-- ✅ Enable Category Selection -->
        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="category_enabled" id="categoryEnabled"
            <?= !empty($_SESSION['category_enabled']) ? 'checked' : '' ?>>
          <label class="form-check-label" for="categoryEnabled">Enable Category Selection</label>
        </div>

        <!-- 🔽 Extra options (edit/delete) only visible if enabled -->
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
      <button type="submit" name="save_setting_btn" class="btn btn-sm btn-primary"> Save Setting</button>
    </form>

    <a href="pages/update_logs.php" class="btn btn-outline-primary mt-3">🔄 View Update Logs</a>
  </div>
</div>

<!-- 🔁 JavaScript to toggle multi options & enforce required -->
<script>
  document.addEventListener("DOMContentLoaded", function () {
    /* ==============================
       🔹 Category Options Toggle
    ============================== */
    const categoryEnabled = document.getElementById("categoryEnabled");
    const categoryExtraOptions = document.getElementById("categoryExtraOptions");

    function toggleCategoryOptions() {
      if (categoryEnabled && categoryExtraOptions) {
        if (categoryEnabled.checked) {
          categoryExtraOptions.style.display = "block";
        } else {
          categoryExtraOptions.style.display = "none";
        }
      }
    }

    if (categoryEnabled) {
      toggleCategoryOptions();
      categoryEnabled.addEventListener("change", toggleCategoryOptions);
    }

    /* ==============================
       🔹 Entry Mode Toggle
    ============================== */
    const singleRadio = document.getElementById("singleEntry");
    const multiRadio = document.getElementById("multiEntry");
    const multiOptions = document.getElementById("multiEntryOptions");

    if (singleRadio && multiRadio && multiOptions) {
      const entryTypeRadios = multiOptions.querySelectorAll('input[name="entry_type_select[]"]');

      function toggleMultiOptions() {
        const isMulti = multiRadio.checked;

        // Show or hide multi options
        multiOptions.style.display = isMulti ? "block" : "none";

        // Set or unset required on sub-options
        entryTypeRadios.forEach(radio => {
          radio.required = isMulti;
        });
      }

      // Initial setup
      toggleMultiOptions();

      // Event listeners
      singleRadio.addEventListener("change", toggleMultiOptions);
      multiRadio.addEventListener("change", toggleMultiOptions);
    }
  });
</script>