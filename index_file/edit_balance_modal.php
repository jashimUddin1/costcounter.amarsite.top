<div class="modal fade" id="editBalanceModal" tabindex="-1" aria-labelledby="editBalanceModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form action="core_php/update_balance.php" method="POST">
        <input type="hidden" name="id" id="edit-setting-id">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Edit Balance</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
              <div class="mb-3">
                  <label for="value" class="form-label">Balance</label>
                  <input type="number" class="form-control" name="value" id="edit-setting-value" required>
              </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Save changes</button>
          </div>
        </div>
    </form>
  </div>
</div>