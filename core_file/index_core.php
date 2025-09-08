<!-- #region start block php code-->
<?php


$current_year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$current_month = isset($_GET['month']) ? $_GET['month'] : date('F');


// 🔢 বছরের তালিকা আনো
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

// 📆 মাসের তালিকা আনো
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

// 📋 ট্রান্সেকশন ডাটা আনো
$transQuery = "SELECT * FROM cost_data WHERE user_id = ? AND YEAR(date) = ? AND MONTHNAME(date) = ? ORDER BY date ASC";
$stmtTrans = $con->prepare($transQuery);
$stmtTrans->bind_param("iis", $user_id, $current_year, $current_month);
$stmtTrans->execute();
$transResult = $stmtTrans->get_result();

// 💰 মাসিক ব্যালেন্স (যদি আলাদা থাকে)
$monthly_balance = 0;
$balanceQuery = "SELECT amount FROM cost_data WHERE user_id = ? AND year = ? AND month = ?";
$stmtBalance = $con->prepare($balanceQuery);
$stmtBalance->bind_param("iis", $user_id, $current_year, $current_month);
$stmtBalance->execute();
$balanceResult = $stmtBalance->get_result();
if ($row = $balanceResult->fetch_assoc()) {
  $monthly_balance = $row['amount'];
}
$stmtBalance->close();

// 🔄 ডেট অনুযায়ী ট্রান্সেকশন গ্রুপ করো
$total_monthly_cost = 0;
$grouped_data = [];
$excluded_categories = ['প্রাপ্তি', 'প্রদান', 'আয়'];

while ($row = $transResult->fetch_assoc()) {
  $date = date('d-m-Y', strtotime($row['date']));
  $grouped_data[$date][] = $row;

  if (!in_array($row['category'], $excluded_categories)) {
    $total_monthly_cost += $row['amount'];
  }
}

$sort_order = $_GET['sort'] ?? 'asc'; // default DESC

if ($sort_order === 'asc') {
  ksort($grouped_data); // পুরাতন আগে
} else {
  krsort($grouped_data); // নতুন আগে
}


// ✅ ফর্ম সাবমিট করলে নতুন ডাটা ইনসার্ট করো
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




// for category
$categories = [];
$stmt = $con->prepare("SELECT id, category_name, category_keywords FROM categories WHERE user_id = ? ");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
  $categories[$row['category_name']] = $row;
}
$stmt->close();

// --- Fetch category groups ---
$category_groups = [];
$stmt = $con->prepare("SELECT * FROM category_groups WHERE user_id = ? ORDER BY id");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
  // explode categories string into array
  $cats = array_map('trim', explode(',', $row['group_category']));
  $category_groups[$row['group_name']] = $cats;
}
$stmt->close();
?>
<!-- #endregion php code end-->
