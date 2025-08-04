<?php
// core_file/add_entry.php

session_start();
include("../db/dbcon.php");

// ইউজার লগইন না করলে রিডাইরেক্ট করো
if (!isset($_SESSION['authenticated'])) {
    header("Location: ../login/index.php");
    exit();
}

$user_id = $_SESSION['auth_user']['id'] ?? null;

// ফর্ম সাবমিট চেক
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'] ?? '';
    $description = $_POST['description'] ?? '';
    $amount = $_POST['amount'] ?? '';
    $category = $_POST['category'] ?? '';
    $redirect_query = $_POST['redirect_query'];

    // তারিখ থেকে year, month, day_name বের করো
    $year = date('Y', strtotime($date));
    $month = date('F', strtotime($date)); // eg. July
    $day_name = date('l', strtotime($date)); // eg. Wednesday

    // ফিল্ড ভ্যালিডেশন
    if (!empty($date) && !empty($description) && is_numeric($amount) && !empty($category)) {
        $stmt = $con->prepare("INSERT INTO cost_data (user_id, date, year, month, day_name, description, amount, category) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssds", $user_id, $date, $year, $month, $day_name, $description, $amount, $category);

        if ($stmt->execute()) {
            $_SESSION['success'] = "খরচ সফলভাবে যোগ হয়েছে!";
        } else {
            $_SESSION['danger'] = "ডেটাবেসে প্রবেশে সমস্যা হয়েছে!";
        }

        $stmt->close();
    } else {
        $_SESSION['warning'] = "সব ফিল্ড সঠিকভাবে পূরণ করুন!";
    }

    header("Location: ../index.php?$redirect_query");
    exit();
} else {
    // সরাসরি এই পেইজে আসলে রিডাইরেক্ট করে দাও
    header("Location: ../index.php?$redirect_query");
    exit();
}
?>
