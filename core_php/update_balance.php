<?php
session_start();
include '../db/dbcon.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $value = $_POST['value'];

    $query = "UPDATE settings SET value = ? WHERE id = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("di", $value, $id);

    if ($stmt->execute()) {
        $_SESSION['status'] = "✅ ব্যালেন্স সফলভাবে আপডেট হয়েছে!";
    } else {
        $_SESSION['error'] = "❌ ব্যালেন্স আপডেট করতে সমস্যা হয়েছে!";
    }
}

header("Location: ../index.php");
exit();
