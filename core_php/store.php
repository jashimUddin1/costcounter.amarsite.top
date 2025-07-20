<?php
session_start();
include '../db/dbcon.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['trans_date'];
    $day = $_POST['day_name'];
    $description = $_POST['description'];
    $amount = $_POST['amount'];
    $category = $_POST['category'];

    // serial নির্ধারণ
    $serial = 1;
    $check_sql = "SELECT MAX(serial) AS max_serial FROM transactions WHERE trans_date = ?";
    $stmt = $con->prepare($check_sql);
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $row = $result->fetch_assoc()) {
        $serial = $row['max_serial'] + 1;
    }

    // Insert query
    $insert = "INSERT INTO transactions (trans_date, day_name, description, amount, category, serial) 
               VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $con->prepare($insert);
    $stmt->bind_param("sssisi", $date, $day, $description, $amount, $category, $serial);
    if ($stmt->execute()) {
        $_SESSION['status'] = "✅ নতুন খরচ সফলভাবে যুক্ত হয়েছে!";
    } else {
        $_SESSION['error'] = "❌ ইনসার্ট করতে সমস্যা হয়েছে!";
    }
}

header("Location: ../index.php");
exit();
