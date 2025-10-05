

<?php //index.php
session_start();
include "../db/dbcon.php";

if (!isset($_SESSION['authenticated'])) {
    header("Location: ../login/index.php");
    exit();
}
$user_id = $_SESSION['auth_user']['id'];

$page_title = "ড্যাশবোর্ড";
$path = "../";
$css_link = "../css/dashboard.css";
include "../master_layout/header.php";

//helpers and all input here
require_once "helpers.php";

//data container
$total_expense = 0;
$category_data = [];
$axis_data     = [];
$axis_labels   = [];

require "queries.php";
?>

<body>
<?php include "layout/top_header.php"; ?>

<div class="container">
  <?php include "layout/header_section.php"; ?>
  <div class="session_section mb-3"><?php include "../includes/session_modal.php"; ?></div>

  <?php if (($_GET['dashboard'] ?? '1') == '3'): ?>
    <?php include "layout/dashboard_three.php"; ?>
  <?php else: ?>
    <div class="row g-4">
      <div class="col-lg-6"><?php include "layout/axis_view.php"; ?></div>
      <div class="col-lg-6"><?php include "layout/category_view.php"; ?></div>
    </div>
  <?php endif; ?>
</div>


<?php include "layout/modals.php"; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>


<!-- send JS- chart -->
<script>
  var axisLabels = <?= json_encode($axis_labels, JSON_UNESCAPED_UNICODE) ?>;
  var axisData   = <?= json_encode(array_values($axis_data)) ?>;

  // এখানে নতুন লজিক (All Year / All Month / Single Month)
  var axisLabelTitle = <?= json_encode(
    $is_all_year 
      ? 'প্রতিবছরের খরচ' 
      : ($is_all_month ? 'প্রতিমাসের খরচ' : 'প্রতিদিনের খরচ'),
    JSON_UNESCAPED_UNICODE
  ) ?>;

  var categoryLabels = <?= json_encode(array_keys($category_data), JSON_UNESCAPED_UNICODE) ?>;
  var categoryData   = <?= json_encode(array_values($category_data)) ?>;
</script>

<script src="js/dashboard_charts.js"></script>

<?php include "../master_layout/footer.php"; ?>
