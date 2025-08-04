<?php
session_start();
include("../db/dbcon.php");

// 🔐 সেশন চেক
if (!isset($_SESSION['authenticated']) || !isset($_SESSION['auth_user']['id'])) {
    $_SESSION['warning'] = "❌ অননুমোদিত অনুরোধ!";
    header("Location: ../login/index.php");
    exit();
}

$user_id = $_SESSION['auth_user']['id'];

if (isset($_GET['date'])) {
    $raw_date = $_GET['date']; // Expected: d-m-Y
    $date = date('Y-m-d', strtotime($raw_date)); // convert to DB format

    // 🔍 Debug
    // echo "User ID: $user_id<br>Date: $date"; exit;

    $stmt = $con->prepare("DELETE FROM cost_data WHERE user_id = ? AND date = ?");
    $stmt->bind_param("is", $user_id, $date);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $_SESSION['success'] = "✅ ".date('d-m-Y', strtotime($date))." তারিখের সব এন্ট্রি মুছে ফেলা হয়েছে!";
        } else {
            $_SESSION['warning'] = "⚠️ ঐ তারিখে কোন এন্ট্রি পাওয়া যায়নি!";
        }
    } else {
        $_SESSION['danger'] = "❌ ডিলিট করতে সমস্যা হয়েছে!";
    }

    $stmt->close();
} else {
    $_SESSION['warning'] = "⚠️ অনুপযুক্ত অনুরোধ!";
}

// 🔁 ফিরিয়ে দাও আগের পেইজে
$query_string = $_SERVER['HTTP_REFERER'] ?? '../index.php';
header("Location: $query_string");
exit();
