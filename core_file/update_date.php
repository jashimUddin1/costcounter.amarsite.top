<?php
session_start();
include("../db/dbcon.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old_date = $_POST['old_date'];
    $new_date = $_POST['new_date'];

    // দিন নাম বের করা (বাংলা)
    $days_bn = [
        'Saturday' => 'শনিবার',
        'Sunday' => 'রবিবার',
        'Monday' => 'সোমবার',
        'Tuesday' => 'মঙ্গলবার',
        'Wednesday' => 'বুধবার',
        'Thursday' => 'বৃহস্পতিবার',
        'Friday' => 'শুক্রবার'
    ];
    $day_name_eng = date("l", strtotime($new_date));
    $day_name = $days_bn[$day_name_eng] ?? $day_name_eng;
    
    $updateQuery = "UPDATE cost_data SET date = ?, day_name = ? WHERE date = ?";
    $stmt = $con->prepare($updateQuery);
    $stmt->bind_param("sss", $new_date, $day_name, $old_date);

    if ($stmt->execute()) {
        $_SESSION['success'] = "✅ তারিখ সফলভাবে পরিবর্তন হয়েছে!";
    } else {
        $_SESSION['danger'] = "❌ তারিখ পরিবর্তন ব্যর্থ হয়েছে!";
    }

    $stmt->close();
} else {
    $_SESSION['warning'] = "⚠️ অনুপযুক্ত অনুরোধ!";
}

// আগের query string সহ redirect
$query_string = $_SERVER['HTTP_REFERER'] ?? '';
$redirect_url = "../index.php";

if (!empty($query_string)) {
    $parsed_url = parse_url($query_string);
    if (isset($parsed_url['query'])) {
        $redirect_url .= '?' . $parsed_url['query'];
    }
}

header("Location: $redirect_url");
exit();
?>
