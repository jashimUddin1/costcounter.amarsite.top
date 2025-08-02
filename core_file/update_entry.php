<?php
session_start();
include("../db/dbcon.php");

// ইউজার অথেনটিকেশন চেক করতে চাইলে এখানে কোড দিতে পারো

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $description = $_POST['description'];
    $amount = $_POST['amount'];
    $category = $_POST['category'];

    $updateQuery = "UPDATE cost_data SET description = ?, amount = ?, category = ? WHERE id = ?";
    $stmt = $con->prepare($updateQuery);
    $stmt->bind_param("sdsi", $description, $amount, $category, $id);

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
