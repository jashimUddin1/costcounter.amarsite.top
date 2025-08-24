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