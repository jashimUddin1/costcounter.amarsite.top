
<!-- Edit Balance Modal -->
<div class="modal fade" id="editBalanceModal" tabindex="-1" aria-labelledby="editBalanceModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form action="core_file/balance_core.php" method="POST">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editBalanceModalLabel">Edit Balance</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <!-- Hidden inputs -->
          <input type="hidden" name="id" id="edit-balance-id">
          <input type="hidden" name="balance_type" value="balance_bd">
          <input type="hidden" name="year" id="edit-balance-year">
          <input type="hidden" name="month" id="edit-balance-month">

          <!-- Balance amount -->
          <div class="mb-3">
            <label for="edit-balance-value" class="form-label">Balance</label>
            <input type="number" class="form-control" name="balance_bd" id="edit-balance-value" required>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">বাতিল</button>
          <button type="submit" name="balance_bd_btn" class="btn btn-primary">Save changes</button>
        </div>
      </div>
    </form>
  </div>
</div>


<script>
  document.addEventListener("DOMContentLoaded", function () {
  const editButtons = document.querySelectorAll('[data-bs-target="#editBalanceModal"]');

  editButtons.forEach(button => {
    button.addEventListener("click", function () {
      document.getElementById("edit-balance-id").value = this.dataset.id;
      document.getElementById("edit-balance-value").value = this.dataset.value;
      document.getElementById("edit-balance-year").value = this.dataset.year;
      document.getElementById("edit-balance-month").value = this.dataset.month;
    });
  });
});

</script>