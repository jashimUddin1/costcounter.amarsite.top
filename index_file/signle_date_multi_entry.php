<!-- single_date_multi_entry.php -->
<div class="">
  <form class="row g-3 mb-4" method="POST" action="core_file/single_date_core.php">
    <div class="col-md-2">
      <label class="form-label">তারিখ দিন</label>
      <input type="date" name="date" required class="form-control" value="<?= date('Y-m-d') ?>">
    </div>

    <div class="col-md-8">
      <label class="form-label d-flex align-items-center">
        বিবরণ ও পরিমাণ (কমা দিয়ে দিন)
        <span tabindex="0" class="ms-2 text-primary" data-bs-toggle="tooltip"
          title="প্রতিটি খরচ কমা দিয়ে আলাদা করুন এবং শেষে পরিমাণ দিন।"
          style="cursor: pointer;">ℹ️</span>
      </label>
      <input type="text" name="bulk_description" class="form-control" required
        placeholder="যেমন: খাবার 50, ফল 530, বাজার 25">
    </div>

    <div class="col-md-2">
      <label class="form-label"> ক্লিক করুন</label>
      <button type="submit" class="btn btn-success w-100">যোগ করুন</button>
    </div>
  </form>
</div>

<!-- 🔁 Tooltip JS init (Bootstrap 5) -->
<script>
  document.addEventListener("DOMContentLoaded", function () {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function (tooltipTriggerEl) {
      new bootstrap.Tooltip(tooltipTriggerEl);
    });
  });
</script>
