<?php
session_start();
include "../db/dbcon.php";

// ইউজার চেক
if (!isset($_SESSION['auth_user'])) {
    header("Location: ../login/index.php");
    exit();
}

$user_id = $_SESSION['auth_user']['id'];

// লগ ডাটা আনা
$query = "SELECT * FROM update_logs WHERE user_id = ? ORDER BY updated_at DESC";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="bn">
<head>
  <meta charset="UTF-8">
  <link rel="icon" type="image/png" href="../images/update_icon.png">
  <title> Update Logs</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container my-4">
  <div class="card shadow">
    <div class="card-header d-flex justify-content-between bg-primary text-white">
      <h5 class="mb-0">Update Logs</h5>
      <a href="../index.php" class="btn btn-light btn-sm"> Back</a>
    </div>
    <div class="card-body">

      <?php if ($result->num_rows > 0) { ?>
        <div class="table-responsive">
          <table class="table table-bordered table-hover text-center">
            <thead class="table-dark">
              <tr>
                <th>ID</th>
                <th>For</th>
                <th>Update Type</th>
                <th>Previous Value</th>
                <th>Updated Value</th>
                <th>Updated At</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                  <td><?= $row['id'] ?></td>
                  <td><?= date("Y-F", strtotime($row['date'])) ?></td>
                  <td><?= htmlspecialchars($row['update_type']) ?></td>
                  <td class="text-danger"><?= number_format($row['previous_value'], 0) ?></td>
                  <td class="text-success"><?= number_format($row['updated_value'], 0) ?></td>
                  <td><?= $row['updated_at'] ?></td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      <?php } else { ?>
        <div class="alert alert-info">⚠️ কোনো লগ ডাটা পাওয়া যায়নি।</div>
      <?php } ?>

    </div>
  </div>
</div>

</body>
</html>
