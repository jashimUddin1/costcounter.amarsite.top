  <nav class="navbar navbar-expand bg-light">
    <div class="container selectionMenu">
      <ul class="navbar-nav">

        <!-- মাস নির্বাচন করুন -->
        <li class="nav-item me-2">
          <button class="btn btn-primary" type="button" data-bs-toggle="dropdown">
            মাস নির্বাচন করুন
          </button>
          <ul class="dropdown-menu">
            <?php foreach ($months as $month): ?>
              <li><a class="dropdown-item" href="?year=<?= $current_year ?>&month=<?= $month ?>"><?= $month ?></a></li>
            <?php endforeach; ?>
          </ul>
        </li>

        <!-- বছর নির্বাচন করুন -->
        <li class="nav-item dropdown me-2">
          <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
            <?= $current_year ?>
          </button>
          <ul class="dropdown-menu">
            <?php foreach ($years as $year): ?>
              <li>
                <a class="dropdown-item <?= ($year == $current_year) ? 'active' : '' ?>" 
                  href="?year=<?= $year ?>">
                  <?= $year ?>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        </li>


        <!-- বর্তমান মাস -->
        <?php if (!empty($months)): ?>
          <li class="nav-item dropdown">
            <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
              <?= $current_month ?> 
            </button>
            <ul class="dropdown-menu">
              <?php foreach ($months as $month): ?>
                <li>
                  <a class="dropdown-item <?= ($month == $current_month) ? 'active' : '' ?>" 
                    href="?year=<?= $current_year ?>&month=<?= $month ?>">
                    <?= $month ?>
                  </a>
                </li>
              <?php endforeach; ?>
            </ul>
          </li>
        <?php endif; ?>
      </ul>

      <!-- ✅ Dashboard button: ডান পাশে যাবে -->
      <div class="ms-auto">
        <a href="dashboard.php" class="btn  btn-primary">Dashboard</a>
      </div>
    </div>
  </nav>