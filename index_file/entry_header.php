 <div class="d-flex justify-content-between align-items-center mb-3 entry-header">
  
  <!-- Selected Month Badge -->
  <h5 class="mb-0">
    <span class="badge bg-success">
      Selected Month: <?= $current_month ?>-<?= $current_year ?>
    </span>
  </h5>

  <div class="selectedWrapper d-flex">

       <!-- Settings Dropdown -->
  <div class="dropdown me-2">
    <form method="" class="d-inline-block ms-3">
        <select name="" class="form-select form-select-sm d-inline-block w-auto">
            <option >বাংলা</option>
            <option >English</option>
        </select>
    </form>
  </div>

   <!-- Settings Dropdown -->
  <div class="dropdown">
    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
      ⚙️
    </button>
    <div class="dropdown-menu dropdown-menu-end p-3" style="min-width: 200px;">
      <form method="post" action="">
        <div class="form-check mb-2">
          <input class="form-check-input" type="checkbox" name="edit_option" id="editOption">
          <label class="form-check-label" for="editOption">Edit</label>
        </div>
        <div class="form-check mb-3">
          <input class="form-check-input" type="checkbox" name="delete_option" id="deleteOption">
          <label class="form-check-label" for="deleteOption">Delete</label>
        </div>
        <button type="submit" class="btn btn-sm btn-primary">Apply</button>
      </form>
    </div>
  </div>

  </div>


</div>