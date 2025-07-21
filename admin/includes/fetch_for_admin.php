<!-- fetch_for_admin.php -->
<?php
include("../db/dbcon.php");

$monthOrder = "'December', 'November', 'October', 'September', 'August', 'July', 'June', 'May', 'April', 'March', 'February', 'January'";

$sqlLatest = "SELECT `year`, `month` FROM `cost_month` WHERE user_id='$user_id' ORDER BY `year` DESC, FIELD(`month`, $monthOrder) LIMIT 1";
$resultLatest = $con->query($sqlLatest);
$latestYear = null;
$latestMonth = null;

if ($resultLatest->num_rows > 0) {
    $rowLatest = $resultLatest->fetch_assoc();
    $latestYear = $rowLatest['year'];
    $latestMonth = $rowLatest['month'];
}

$year = isset($_GET['year']) ? $_GET['year'] : $latestYear;
$month = isset($_GET['month']) ? $_GET['month'] : $latestMonth;

$sql = "SELECT * FROM `cost_data` WHERE `year` = '$year' AND `month` = '$month' AND user_id='$user_id'";
$result = $con->query($sql);

echo"<div class='container selectTable'>Selected Month: <span>$month-$year</span></div>";

$con->close();
?>
<script>
function confirmDelete() {
    return confirm("Are you sure?");
}
</script>
