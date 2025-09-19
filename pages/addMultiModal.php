<!-- Multi Entry Modal -->
<div class="modal fade" id="addMultiModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="post" novalidate>
        <input type="hidden" name="action" value="add_category_multi">

        <div class="modal-header">
          <h5 class="modal-title">Add Multiple Categories</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <label class="form-label">একসাথে একাধিক ক্যাটাগরি/সাব-ক্যাটাগরি/কীওয়ার্ড যোগ করুন</label>
          
          <textarea name="category_input" id="multi_input" 
            class="form-control" rows="8" required
            placeholder="উদাহরণ:
FirstCat => FirstSub => one, two, three
SecondCat => apple, banana, orange
Market => Vegetable -> carrot, potato
Bills -> electricity, gas"></textarea>
          <div class="invalid-feedback">⚠️ অন্তত একটি ক্যাটাগরি/কীওয়ার্ড লিখতে হবে।</div>

          <small class="text-muted d-block mt-2">
            📌 <strong>Input Format:</strong><br>
            <code>ক্যাটাগরি => সাব-ক্যাটাগরি => কীওয়ার্ড, কীওয়ার্ড</code><br>
            <code>ক্যাটাগরি => সাব-ক্যাটাগরি -> কীওয়ার্ড, কীওয়ার্ড</code><br>
            <code>ক্যাটাগরি -> কীওয়ার্ড, কীওয়ার্ড</code><br>
            <code>ক্যাটাগরি => কীওয়ার্ড, কীওয়ার্ড</code><br>
          </small>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">Add All</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// ✅ Form validation
document.querySelector('#addMultiModal form').addEventListener('submit', function(e){
  let input = document.getElementById('multi_input');
  if(input.value.trim() === ""){
    e.preventDefault();
    e.stopPropagation();
    input.classList.add("is-invalid");
  } else {
    input.classList.remove("is-invalid");
  }
});
</script>
