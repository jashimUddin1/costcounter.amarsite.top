<!-- 🛠️ Edit Modal -->
<div class="modal fade" id="editCostDataModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="core_file/update_entry.php">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">🔧 খরচ এডিট করো</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <input type="hidden" name="id" id="edit-id">

          <div class="mb-3">
            <label for="edit-description" class="form-label">বিবরণ</label>
            <input type="text" class="form-control" name="description" id="edit-description" required>
          </div>

          <div class="mb-3">
            <label for="edit-amount" class="form-label">টাকার পরিমাণ</label>
            <input type="number" class="form-control" name="amount" id="edit-amount" required>
          </div>

          <div class="mb-3">
            <label for="edit-category" class="form-label">ক্যাটাগরি</label>
            <select name="category" id="edit-category" class="form-select" required>

              <optgroup label="দৈনন্দিন খরচ">
                <option value="বাজার">বাজার</option>
                <option value="বাহিরেরখরচ">বাহিরের খরচ</option>
                <option value="মোবাইলখরচ">মোবাইল খরচ</option>
                <option value="গাড়িভাড়া">গাড়ি ভাড়া</option>
                <option value="ঘোরাঘুরি">ঘোরাঘুরি</option>
                <option value="কেনাকাটা">কেনাকাটা</option>
              </optgroup>

              <optgroup label="বাড়ি সংক্রান্ত">
                <option value="বাসাভাড়া">বাসা ভাড়া</option>
                <option value="গৃহস্থালীজিনিসপত্র">গৃহস্থালী জিনিসপত্র</option>
                <option value="গৃহস্থালীমেরামত">গৃহস্থালী মেরামত</option>
              </optgroup>

              <optgroup label="ব্যক্তিগত">
                <option value="মালজিনিস">মাল জিনিস</option>
                <option value="কসমেটিক্স">কসমেটিক্স</option>
                <option value="দাওয়াতখরচ">দাওয়াতখরচ</option>
                <option value="বইখাতা">বইখাতা</option>
                <option value="ঔষধ">ঔষধ</option>
                <option value="পরিবার">পরিবার</option>
                <option value="সাইকেলমেরামত">সাইকেল মেরামত</option>
              </optgroup>

              <optgroup label="আর্থিক">
                <option value="প্রাপ্তি">প্রাপ্তি</option>
                <option value="প্রদান">প্রদান</option>
                <option value="আয়">আয়</option>
              </optgroup>

              <option value="অন্যান্য">অন্যান্য</option>
            </select>
          </div>

        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">💾 আপডেট করো</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const editButtons = document.querySelectorAll('.edit-btn');

    editButtons.forEach(function (btn) {
      btn.addEventListener('click', function () {
        const id = btn.getAttribute('data-id');
        const description = btn.getAttribute('data-description');
        const amount = btn.getAttribute('data-amount');
        const category = btn.getAttribute('data-category');

        document.getElementById('edit-id').value = id;
        document.getElementById('edit-description').value = description;
        document.getElementById('edit-amount').value = amount;
        document.getElementById('edit-category').value = category;
      });
    });
  });
</script>
