<?php
session_start();
include("db/dbcon.php");

if (!isset($_SESSION['authenticated'])) {
    header("location: login/index.php");
    exit();
}

$user_id = $_SESSION['auth_user']['id'];
$current_year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$current_month = isset($_GET['month']) ? $_GET['month'] : date('F');

// Get available years from cost_data
$yearQuery = "SELECT DISTINCT YEAR(date) as year FROM cost_data WHERE user_id = ? ORDER BY year DESC";
$stmtYear = $con->prepare($yearQuery);
$stmtYear->bind_param("i", $user_id);
$stmtYear->execute();
$yearResult = $stmtYear->get_result();
$years = [];
while ($row = $yearResult->fetch_assoc()) {
    $years[] = $row['year'];
}
$stmtYear->close();

// Get months for selected year from cost_data
$monthQuery = "SELECT DISTINCT MONTH(date) as month_number, MONTHNAME(date) as month_name FROM cost_data WHERE user_id = ? AND YEAR(date) = ? ORDER BY month_number ASC";
$stmtMonth = $con->prepare($monthQuery);
$stmtMonth->bind_param("ii", $user_id, $current_year);
$stmtMonth->execute();
$monthResult = $stmtMonth->get_result();
$months = [];
while ($row = $monthResult->fetch_assoc()) {
    $months[] = $row['month_name'];
}
$stmtMonth->close();

// Get filtered transactions from cost_data
$transQuery = "SELECT * FROM cost_data WHERE user_id = ? AND YEAR(date) = ? AND MONTHNAME(date) = ? ORDER BY date DESC";
$stmtTrans = $con->prepare($transQuery);
$stmtTrans->bind_param("iis", $user_id, $current_year, $current_month);
$stmtTrans->execute();
$transResult = $stmtTrans->get_result();

// Get monthly remaining balance from cost_month
$balanceQuery = "SELECT amount FROM cost_data WHERE user_id = ? AND year = ? AND month = ?";
$stmtBalance = $con->prepare($balanceQuery);
$stmtBalance->bind_param("iis", $user_id, $current_year, $current_month);
$stmtBalance->execute();
$balanceResult = $stmtBalance->get_result();
$monthly_balance = $balanceResult->fetch_assoc()['amount'] ?? 0;
$stmtBalance->close();

$total_monthly_cost = 0;
$grouped_data = [];
while ($row = $transResult->fetch_assoc()) {
    $date = date('d-m-Y', strtotime($row['date']));
    $grouped_data[$date][] = $row;
    $total_monthly_cost += $row['amount'];
}

// Insert transaction if POST request received
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'];
    $description = $_POST['description'];
    $amount = floatval($_POST['amount']);
    $category = $_POST['category'];

    $insertQuery = "INSERT INTO cost_data (user_id, date, description, amount, category) VALUES (?, ?, ?, ?, ?)";
    $stmtInsert = $con->prepare($insertQuery);
    $stmtInsert->bind_param("issds", $user_id, $date, $description, $amount, $category);
    $stmtInsert->execute();
    $stmtInsert->close();

    header("Location: index.php?year={$current_year}&month={$current_month}");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ডেইলি খরচ এন্ট্রি</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .date-card { background: #f8f9fa; border-left: 5px solid orange; padding: 10px; margin-bottom: 10px; }
    .cost-entry { padding-left: 20px; }
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
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
      <li class="nav-item"><a class="nav-link btn btn-danger text-white" href="logout.php">Logout</a></li>
    </ul>
  </div>
</nav>

<div class="container mt-3">
  <h5><span class="badge bg-success">Selected Month: <?= $current_month ?>-<?= $current_year ?></span></h5>

  <div class="dropdown my-3">
    <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">মাস নির্বাচন করুন</button>
    <ul class="dropdown-menu">
      <?php foreach ($months as $month): ?>
        <li><a class="dropdown-item" href="?year=<?= $current_year ?>&month=<?= $month ?>"><?= $month ?></a></li>
      <?php endforeach; ?>
    </ul>
  </div>

  <!-- Entry Form -->
  <form class="row g-3 mb-4" method="POST" action="add_entry.php">
    <div class="col-md-3">
      <input type="date" name="date" class="form-control" required>
    </div>
    <div class="col-md-4">
      <input type="text" name="description" class="form-control" placeholder="খরচের বিবরণ" required>
    </div>
    <div class="col-md-2">
      <input type="number" name="amount" step="0.01" class="form-control" placeholder="পরিমাণ" required>
    </div>
    <div class="col-md-2">
      <input type="text" name="category" class="form-control" placeholder="বিভাগ" required>
    </div>
    <div class="col-md-1">
      <button type="submit" class="btn btn-success">➕ যোগ করুন</button>
    </div>
  </form>

  <h4>🗓️ মাসিক খরচ</h4>
  <?php foreach ($grouped_data as $date => $entries): ?>
    <div class="date-card">
      <strong><?= $date ?> | <?= date('l', strtotime($date)) ?></strong>
      <?php $daily_total = 0; ?>
      <?php foreach ($entries as $i => $row): ?>
        <div class="cost-entry"> <?= ($i+1) . '. ' . $row['description'] . ' ' . number_format($row['amount'], 2) . ' টাকা (' . $row['category'] . ')' ?> </div>
        <?php $daily_total += $row['amount']; ?>
      <?php endforeach; ?>
      <strong>মোট়ৰ়ঃ <?= number_format($daily_total) ?> টাকা</strong>
    </div>
  <?php endforeach; ?>

  <div class="alert alert-success text-center">
    ✅ মোট ব্যয়: <?= number_format($total_monthly_cost) ?> টাকা
    <br> 🧮 অবশিষ্ট: <?= number_format($monthly_balance - $total_monthly_cost, 2) ?> টাকা
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
