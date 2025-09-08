<!-- Set Balance Modal -->
<div class="modal fade" id="setBalanceModal" tabindex="-1" aria-labelledby="setBalanceModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-3 shadow">
      <div class="modal-header">
        <h5 class="modal-title" id="setBalanceModalLabel">নতুন অবশিষ্ট টাকা সেট করুন</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form action="core_file/balance_core.php" method="POST">
        <div class="modal-body">

          <!-- Hidden inputs -->
          <input type="hidden" id="setBalanceUserId" name="user_id">
          <input type="hidden" id="setBalanceYear" name="year">
          <input type="hidden" id="setBalanceMonth" name="month">

          <!-- Balance amount -->
          <div class="mb-3">
            <label for="setBalanceAmount" class="form-label">অবশিষ্ট টাকা</label>
            <input type="number" class="form-control" id="setBalanceAmount" name="amount" required>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">বাতিল</button>
          <button type="submit" name="set_balance_btn" class="btn btn-primary">সংরক্ষণ</button>
        </div>
      </form>
    </div>
  </div>
</div>


<script>
    document.addEventListener("DOMContentLoaded", function () {
  const setBalanceBtns = document.querySelectorAll('[data-bs-target="#setBalanceModal"]');
  
  setBalanceBtns.forEach(btn => {
    btn.addEventListener("click", function () {
      document.getElementById("setBalanceUserId").value = this.dataset.id;
      document.getElementById("setBalanceYear").value = this.dataset.year;
      document.getElementById("setBalanceMonth").value = this.dataset.month;
    });
  });
});

</script>