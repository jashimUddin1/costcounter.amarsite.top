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
echo "<table id='dataTable' class='table table-bordered table-striped'>
        <thead>
            <tr>
                <th colspan='8'>$month-$year</th>
            </tr>
            <tr>
                <th>Date</th>
                <th>Day Name</th>
                <th>Description</th>
                <th>Amount</th>

                <th>Category</th>
                <th>Created At</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id='data-table-body'>";

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $row_id = $row['id'];
        echo "<tr>
                <td data-label='Date'>{$row['date']}</td>
                <td data-label='Received'>{$row['day_name']}</td>
                <td data-label='cancel'>{$row['description']}</td>
                <td data-label='Reschedule'>{$row['amount']}</td>
    
                <td data-label='rate'>{$row['category']}</td>
                <td data-label='Total'>{$row['created_at']}</td>
                <td data-label='Action'>
                    <a href='edit_data.php?id=$row_id&year=$year&month=$month' class='btn btn-success btn-sm'>Edit</a>"
                    ?>
                    <form action="delete_data.php" method="POST" style="display:inline;">
                        <input type="hidden" name="data_id" value="<?= $row['id']; ?>">
                        <input type="hidden" name="year" value="<?= htmlspecialchars($year); ?>">
                        <input type="hidden" name="month" value="<?= htmlspecialchars($month); ?>">
                        <button type="submit" name="delete_count" class="btn btn-danger btn-sm" onclick="return confirmDelete()">Delete</button>
                    </form>
                    <?php echo"
                </td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='8' class='text-center'>No data available for $month-$year</td></tr>";
}

echo "</tbody></table>";
$con->close();
?>
<script>
function confirmDelete() {
    return confirm("Are you sure?");
}
</script>
