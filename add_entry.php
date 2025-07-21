<?php
// add_entry.php

session_start();
include("db/dbcon.php");

// ইউজার লগইন না করলে রিডাইরেক্ট করো
if (!isset($_SESSION['authenticated'])) {
    header("Location: login/index.php");
    exit();
}

$user_id = $_SESSION['auth_user']['id'] ?? null;

// ফর্ম সাবমিট চেক
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'] ?? '';
    $description = $_POST['description'] ?? '';
    $amount = $_POST['amount'] ?? '';
    $category = $_POST['category'] ?? '';

    // ফিল্ড ভ্যালিডেশন
    if (!empty($date) && !empty($description) && is_numeric($amount) && !empty($category)) {
        $stmt = $con->prepare("INSERT INTO cost_data (user_id, date, description, amount, category) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issds", $user_id, $date, $description, $amount, $category);

        if ($stmt->execute()) {
            $_SESSION['message'] = "খরচ সফলভাবে যোগ হয়েছে!";
        } else {
            $_SESSION['message'] = "ডেটাবেসে প্রবেশে সমস্যা হয়েছে!";
        }

        $stmt->close();
    } else {
        $_SESSION['message'] = "সব ফিল্ড সঠিকভাবে পূরণ করুন!";
    }

    header("Location: index.php");
    exit();
} else {
    // সরাসরি এই পেইজে আসলে রিডাইরেক্ট করে দাও
    header("Location: index.php");
    exit();
}
?>
