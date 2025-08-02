<?php
session_start();
include("../db/dbcon.php");

// ইউজার চেক
if (!isset($_SESSION['authenticated'])) {
    header("Location: ../login/index.php");
    exit();
}

$user_id = $_SESSION['auth_user']['id'] ?? null;

// আগের পেজের query string গুলো (যেমন year, month, sort) ধরে রাখো
$query_string = $_SERVER['HTTP_REFERER'] ?? '';

// ডিফল্ট fallback
$redirect_url = "../index.php";

// যদি আগের পেজে index.php?year=... ইত্যাদি থাকে, তাহলে সেটা ধরে রাখো
if (!empty($query_string)) {
    $parsed_url = parse_url($query_string);
    if (isset($parsed_url['query'])) {
        $redirect_url .= '?' . $parsed_url['query'];
    }
}

// ডিলিট প্রসেস
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];

    $stmt = $con->prepare("DELETE FROM cost_data WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $user_id);

    if ($stmt->execute()) {
        $_SESSION['warning'] = "✅ এন্ট্রি সফলভাবে ডিলিট হয়েছে!";
    } else {
        $_SESSION['danger'] = "❌ ডিলিট করতে সমস্যা হয়েছে!";
    }

    $stmt->close();
} else {
    $_SESSION['warning'] = "⚠️ বৈধ আইডি পাওয়া যায়নি!";
}

// redirect with parameters intact
header("Location: $redirect_url");
exit();
