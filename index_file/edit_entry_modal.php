<div class="modal fade" id="editEntryModal" tabindex="-1" aria-labelledby="editEntryModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form action="edit_entry2.php" method="POST">
      <input type="hidden" name="id" id="edit-entry-id">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editEntryModalLabel">✏️ খরচ এডিট করুন</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">

          <div class="mb-3">
            <label class="form-label">তারিখ</label>
            <input type="date" class="form-control" name="date" id="edit-entry-date" required>
          </div>

          <div class="mb-3">
            <label class="form-label">বিবরণ</label>
            <input type="text" class="form-control" name="description" id="edit-entry-description" required>
          </div>

          <div class="mb-3">
            <label class="form-label">পরিমাণ</label>
            <input type="number" step="0.01" class="form-control" name="amount" id="edit-entry-amount" required>
          </div>

          <div class="mb-3">
            <label class="form-label">ক্যাটাগরি</label>
            <input type="text" class="form-control" name="category" id="edit-entry-category" required>
          </div>

        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">✅ আপডেট করুন</button>
        </div>
      </div>
    </form>
  </div>
</div>