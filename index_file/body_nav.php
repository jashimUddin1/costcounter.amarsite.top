<style>
  @media (max-width: 576px) {
    .monthSelect {
      display: none;
    }
  }
</style>
<nav class="navbar navbar-expand bg-light">
  <div class="container selectionMenu">
    <ul class="navbar-nav">

      <!-- মাস নির্বাচন করুন -->
      <li class="nav-item monthSelect me-2 dropdown">
        <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
          মাস নির্বাচন করুন
        </button>
        <ul class="dropdown-menu">
          <?php foreach ($months as $num => $name): ?>
            <li>
              <a class="dropdown-item <?= ($num == $current_month) ? 'active' : '' ?>" 
                 href="?year=<?= $current_year ?>&month=<?= $num ?>">
                <?= $name ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </li>

      <!-- বছর নির্বাচন করুন -->
      <li class="nav-item dropdown me-2">
        <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
          <?= en2bn_number($current_year) ?>
        </button>
        <ul class="dropdown-menu">
          <?php foreach ($years as $year): ?>
            <li>
              <a class="dropdown-item <?= ($year == $current_year) ? 'active' : '' ?>" 
                 href="?year=<?= $year ?>&month=<?= $current_month ?>">
                <?= en2bn_number($year) ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </li>

      <!-- বর্তমান মাস -->
      <?php if (!empty($months)): ?>
        <li class="nav-item dropdown" >
          <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
            <?= en2bn_month($months[$current_month]) ?> 
          </button>
          <ul class="dropdown-menu">
            <?php foreach ($months as $num => $name): ?>
              <li>
                <a class="dropdown-item <?= ($num == $current_month) ? 'active' : '' ?>" 
                   href="?year=<?= $current_year ?>&month=<?= $num ?>">
                  <?= en2bn_month($name) ?>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        </li>
      <?php endif; ?>
    </ul>

    <!-- Dashboard button -->
    <div class="ms-auto">
      <a href="dashboard.php?year=<?= $current_year ?>&month=<?= $current_month ?>" 
         class="btn btn-primary">Dashboard</a>
    </div>
  </div>
</nav>
