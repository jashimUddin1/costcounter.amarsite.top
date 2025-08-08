<?php
// core_file/add_entry_multi.php

session_start();
require_once '../db/dbcon.php';

// ইউজার লগইন চেক
$user_id = $_SESSION['auth_user']['id'] ?? null;

if (!$user_id) {
    header("Location: ../login/index.php");
    exit();
}

$redirect_query = $_POST['redirect_query'] ?? '';

// রিকোয়েস্ট মেথড চেক
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php?$redirect_query");
    exit();
}

// ডেটা চেক
if (empty($_POST['entries']) || !is_array($_POST['entries'])) {
    $_SESSION['warning'] = "❌ কিছু তথ্য অনুপস্থিত!";
    header("Location: ../index.php?$redirect_query");
    exit();
}

$inserted = 0;
foreach ($_POST['entries'] as $entry) {
    $date = $entry['date'] ?? '';
    $description = trim($entry['description'] ?? '');
    $amount = floatval($entry['amount'] ?? 0);
    $category = $entry['category'] ?? '';

    if (!$date || !$description || !$amount || !$category) {
        continue; // স্কিপ ইনকমপ্লিট এন্ট্রি
    }

    // তারিখ থেকে year/month/day_name বের করো
    $timestamp = strtotime($date);
    $year = date('Y', $timestamp);
    $month = date('F', $timestamp);
    $day_name = date('l', $timestamp);

    $created_at = date('Y-m-d H:i:s');
    $serial = rand(100000, 999999);

    // ইনসার্ট
    $stmt = $con->prepare("INSERT INTO cost_data 
        (user_id, year, month, date, day_name, description, amount, category, serial, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param(
        "iissssdsss",
        $user_id,
        $year,
        $month,
        $date,
        $day_name,
        $description,
        $amount,
        $category,
        $serial,
        $created_at
    );

    if ($stmt->execute()) {
        $inserted++;
    }
}

// ✅ ফিডব্যাক
if ($inserted > 0) {
    $_SESSION['success'] = "✅ মোট {$inserted}টি এন্ট্রি সফলভাবে যোগ হয়েছে!";
} else {
    $_SESSION['danger'] = "❌ কোনো এন্ট্রি যোগ হয়নি! ফরম্যাট চেক করুন!";
}

header("Location: ../index.php?$redirect_query");
exit();
