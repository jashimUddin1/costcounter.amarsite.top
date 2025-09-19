<!-- Single Entry Modal -->
<div class="modal fade" id="addSingleModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" novalidate>
        <input type="hidden" name="action" value="add_category_single">
        <input type="hidden" name="category_name" id="category_name_hidden">
        <input type="hidden" name="subcategory_name" id="subcategory_hidden">

        <div class="modal-header">
          <h5 class="modal-title">Add Single Category</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">

          <!-- Category Select -->
          <label class="form-label">ক্যাটাগরি নির্বাচন</label>
          <select class="form-select" id="category_select" required>
            <option value="" disabled selected>ক্যাটাগরি নির্বাচন করুন</option>
            <option value="__add_new__">+ নতুন ক্যাটাগরি</option>
            <?php foreach ($categories as $cat_name => $rows): ?>
              <option value="<?= htmlspecialchars($cat_name, ENT_QUOTES) ?>">
                <?= htmlspecialchars($cat_name) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <div class="invalid-feedback">⚠️ ক্যাটাগরি নির্বাচন করুন অথবা নতুন ক্যাটাগরি লিখুন।</div>

          <!-- New Category Input -->
          <input type="text" class="form-control mt-2" id="new_category_input"
                 placeholder="নতুন ক্যাটাগরি নাম" style="display:none;">
          <div class="invalid-feedback">⚠️ নতুন ক্যাটাগরির নাম লিখুন।</div>

          <!-- Sub-category Area -->
          <div id="subcategory_area" class="mt-3" style="display:none;">
            <label class="form-label">সাব-ক্যাটাগরি</label>
            <select class="form-select" id="subcategory_select">
              <option value="">-- সাব-ক্যাটাগরি (ঐচ্ছিক) --</option>
            </select>
            <div class="invalid-feedback">⚠️ সাব-ক্যাটাগরির নাম দিতে হবে।</div>

            <!-- নতুন সাব-ক্যাটাগরি input -->
            <input type="text" class="form-control mt-2" id="new_subcategory_input"
                   placeholder="নতুন সাব-ক্যাটাগরি নাম" style="display:none;">
            <div class="invalid-feedback">⚠️ নতুন সাব-ক্যাটাগরির নাম লিখুন।</div>
          </div>

          <!-- Keywords Input -->
          <label class="form-label mt-3">কীওয়ার্ড (কমা দিয়ে আলাদা করুন)</label>
          <input type="text" name="category_keywords" id="keywords_input"
                 class="form-control" placeholder="উদাহরণ: মাছ, সবজি, মাংস" required>
          <div class="invalid-feedback">⚠️ অন্তত একটি কীওয়ার্ড দিতে হবে।</div>

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Add</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
var categoriesData = <?= json_encode($categories, JSON_UNESCAPED_UNICODE) ?>;

var catSelect = document.getElementById('category_select');
var subSelect = document.getElementById('subcategory_select');
var newCatInput = document.getElementById('new_category_input');
var newSubInput = document.getElementById('new_subcategory_input');
var categoryNameHidden = document.getElementById('category_name_hidden');
var subcategoryHidden = document.getElementById('subcategory_hidden');
var keywordsInput = document.getElementById('keywords_input');

// Category select change
catSelect.addEventListener('change', function () {
  var subArea = document.getElementById('subcategory_area');

  if (this.value === "__add_new__") {
    newCatInput.style.display = 'block';
    newCatInput.setAttribute("required", "required");

    subArea.style.display = 'block';
    subSelect.innerHTML = `
      <option value="">-- সাব-ক্যাটাগরি (ঐচ্ছিক) --</option>
      <option value="__add_new__">+ নতুন সাব-ক্যাটাগরি (ঐচ্ছিক)</option>
    `;
  } else {
    newCatInput.style.display = 'none';
    newCatInput.removeAttribute("required");

    subArea.style.display = 'block';
    var subs = [];
    if (categoriesData[this.value]) {
      categoriesData[this.value].forEach(function (row) {
        if (row.sub_category && row.sub_category.trim() !== "" && !subs.includes(row.sub_category)) {
          subs.push(row.sub_category);
        }
      });
    }
    subSelect.innerHTML = `<option value="">-- সাব-ক্যাটাগরি (ঐচ্ছিক) --</option>`;
    if (subs.length > 0) {
      subSelect.innerHTML += `<option value="__add_new__">+ নতুন সাব-ক্যাটাগরি</option>`;
      subs.forEach(function (sub) {
        subSelect.innerHTML += `<option value="${sub}">${sub}</option>`;
      });
    } else {
      subSelect.innerHTML += `<option value="__add_new__">+ নতুন সাব-ক্যাটাগরি (ঐচ্ছিক)</option>`;
    }
  }
  newSubInput.style.display = 'none';
  newSubInput.removeAttribute("required");
});

// Subcategory select change
subSelect.addEventListener('change', function () {
  if (this.value === "__add_new__") {
    newSubInput.style.display = 'block';
    newSubInput.setAttribute("required", "required");
  } else {
    newSubInput.style.display = 'none';
    newSubInput.removeAttribute("required");
  }
});

// Form validation on submit
document.querySelector('#addSingleModal form').addEventListener('submit', function(e){
  let valid = true;

  // Category validation
  if(catSelect.value === "" || (catSelect.value === "__add_new__" && newCatInput.value.trim() === "")){
    catSelect.classList.add("is-invalid");
    newCatInput.classList.add("is-invalid");
    valid = false;
  } else {
    catSelect.classList.remove("is-invalid");
    newCatInput.classList.remove("is-invalid");
  }

  // Subcategory validation
  if(subSelect.value === "__add_new__" && newSubInput.value.trim() === ""){
    newSubInput.classList.add("is-invalid");
    valid = false;
  } else {
    newSubInput.classList.remove("is-invalid");
  }

  // Keywords validation
  if(keywordsInput.value.trim() === ""){
    keywordsInput.classList.add("is-invalid");
    valid = false;
  } else {
    keywordsInput.classList.remove("is-invalid");
  }

  if(!valid){
    e.preventDefault();
    e.stopPropagation();
    return false;
  }

  // Hidden category name set
  if(catSelect.value === "__add_new__"){
    categoryNameHidden.value = newCatInput.value.trim();
  } else {
    categoryNameHidden.value = catSelect.value;
  }

  // ✅ Subcategory handling into hidden input
  if(subSelect.value === "__add_new__"){
    subcategoryHidden.value = newSubInput.value.trim();
  } else if(subSelect.value !== ""){
    subcategoryHidden.value = subSelect.value;
  } else {
    subcategoryHidden.value = "none";
  }
});
</script>
