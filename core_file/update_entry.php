<?php
session_start();
include("../db/dbcon.php");

if (!isset($_SESSION['authenticated'])) {
  header("location: ../login/index.php");
  exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $description = $_POST['description'];
    $amount = $_POST['amount'];
    $category = $_POST['category'];
    $date = $_POST['date'];

    $day_name_eng = date("l", strtotime($date));
    $day_name = $day_name_eng;

    $updateQuery = "UPDATE cost_data SET description = ?, amount = ?, category = ?, date = ?, day_name = ? WHERE id = ?";
    $stmt = $con->prepare($updateQuery);
    $stmt->bind_param("sdsssi", $description, $amount, $category, $date, $day_name, $id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "✅ খরচ সফলভাবে আপডেট হয়েছে!";
    } else {
        $_SESSION['danger'] = "❌ আপডেট করতে সমস্যা হয়েছে!";
    }

    $stmt->close();
} else {
    $_SESSION['warning'] = "⚠️ অনুপযুক্ত অনুরোধ!";
}

// আগের query string ধরে রাখো (যেমন ?year=2025&month=7&sort=desc)
$query_string = $_SERVER['HTTP_REFERER'] ?? '';
$redirect_url = "../index.php";

if (!empty($query_string)) {
    $parsed_url = parse_url($query_string);
    if (isset($parsed_url['query'])) {
        $redirect_url .= '?' . $parsed_url['query'];
    }
}

// Redirect
header("Location: $redirect_url");
exit();
