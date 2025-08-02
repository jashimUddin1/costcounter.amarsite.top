<nav class="navbar navbar-expand navbar-dark bg-dark">
  <div class="container">
    <span class="navbar-brand">Developer Jasim</span>
    <ul class="navbar-nav ms-auto">
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown"><?= $current_year ?></a>
        <ul class="dropdown-menu">
          <?php foreach ($years as $year): ?>
            <li><a class="dropdown-item" href="?year=<?= $year ?>"><?= $year ?></a></li>
          <?php endforeach; ?>
        </ul>
      </li>
      <li class="nav-item"><a class="nav-link" href="#">Profile</a></li>
      <li class="nav-item"><a class="nav-link btn btn-danger text-white"  href="logout.php">Logout</a></li>
    </ul>
  </div>
</nav>