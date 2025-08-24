<nav class="navbar navbar-expand navbar-dark bg-dark">
  <div class="container">
    <span class="navbar-brand">Developer Jasim</span>
    <ul class="navbar-nav ms-auto">
      <!-- Profile button -->
      <li class="nav-item">
        <a class="nav-link text-white" href="#" data-bs-toggle="offcanvas" data-bs-target="#profileMenu" aria-controls="profileMenu">
          Profile
        </a>
      </li>
      <li class="nav-item"><a class="nav-link text-white" href="pages/help.php">Help</a></li>
      <?php if (!empty($_SESSION['category_enabled'])): ?>
        <li class="nav-item"><a class="nav-link text-white bg-success rounded" href="pages/manage_categories.php">Categories</a></li>
      <?php endif; ?>
    </ul>
  </div>
</nav>

<!-- Offcanvas Profile Menu -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="profileMenu" aria-labelledby="profileMenuLabel">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="profileMenuLabel">👤 প্রোফাইল মেনু</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body">
    <ul class="list-group">
      <li class="list-group-item"><a href="#">আমার প্রোফাইল</a></li>
      <li class="list-group-item"><a href="#">সেটিংস</a></li>
      <li class="list-group-item"><a href="login/logout.php" class="text-danger">লগআউট</a></li>
    </ul>
  </div>
</div>
