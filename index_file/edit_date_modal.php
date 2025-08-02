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