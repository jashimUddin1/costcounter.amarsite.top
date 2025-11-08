<!-- index_file/single_date_multi_entry.php -->
<div class="">
  <form class="row g-3 mb-4" method="POST" action="core_file/single_date_core.php">
    <!-- Hidden Query Parameters -->
    <input type="hidden" name="redirect_query" value="<?= htmlspecialchars($query_string) ?>">

    <div class="col-md-2">
      <div class="d-flex justify-content-between align-items-center">
        <label class="form-label mb-0">তারিখ দিন</label>
        <select class="form-select form-select-sm w-auto" name="date_type" id="dateTypeSelect">
          <option value="single" selected>Single</option>
          <option value="multi">Range</option>
        </select>
      </div>

      <div id="dateContainer">
        <input type="date" name="date" required class="form-control mt-2" value="<?= date('Y-m-d') ?>">
      </div>
    </div>

    <div class="col-md-8">
      <label class="form-label d-flex align-items-center">
        বিবরণ ও পরিমাণ (কমা দিয়ে দিন)
        <span tabindex="0" class="ms-2 text-primary" data-bs-toggle="tooltip"
          title="প্রতিটি খরচ কমা দিয়ে আলাদা করুন এবং কমার আগে পরিমাণ দিন।" style="cursor: pointer;">ℹ️</span>
      </label>
      <input type="text" name="bulk_description" class="form-control" required
        placeholder="যেমন: খাবার 50, ফল 530, বাজার 25">
    </div>

    <div class="col-md-2">
      <label class="form-label">ক্লিক করুন</label>
      <button type="submit" class="btn btn-success w-100">যোগ করুন</button>
    </div>
  </form>
</div>

<!-- 🔁 Tooltip + Dynamic Date Input Script -->
<script>
  document.addEventListener("DOMContentLoaded", function () {
    // Bootstrap tooltip init
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function (tooltipTriggerEl) {
      new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Date type toggle (single/multi)
    const dateTypeSelect = document.getElementById("dateTypeSelect");
    const dateContainer = document.getElementById("dateContainer");

    dateTypeSelect.addEventListener("change", function () {
      if (this.value === "multi") {
        dateContainer.innerHTML = `
          <div class="d-flex">
          <input style='min-width: 20px;' type="date" name="date" required class="form-control mt-2" value="<?= date('Y-m-d') ?>">
          <small class="text-center d-block my-1 align-center"> TO </small>
          <input style='min-width: 20px;' type="date" name="to_date" required class="form-control mt-2" value="<?= date('Y-m-d') ?>">
          </div>
        `;
      } else {
        dateContainer.innerHTML = `
          <input type="date" name="date" required class="form-control mt-2" value="<?= date('Y-m-d') ?>">
        `;
      }
    });
  });
</script>
