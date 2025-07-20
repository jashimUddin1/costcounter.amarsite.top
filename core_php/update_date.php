<?php
session_start();
include '../db/dbcon.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $old_date = $_POST['old_date'];
    $new_date = $_POST['new_date'];

    $day_name = ['রবিবার', 'সোমবার', 'মঙ্গলবার', 'বুধবার', 'বৃহস্পতিবার', 'শুক্রবার', 'শনিবার'];
    $new_day = $day_name[date('w', strtotime($new_date))];

    // তারিখ আপডেট করা
    $update = "UPDATE transactions SET trans_date = ?, day_name = ? WHERE trans_date = ?";
    $stmt = $con->prepare($update);
    $stmt->bind_param("sss", $new_date, $new_day, $old_date);

    if ($stmt->execute()) {
        $_SESSION['status'] = "✅ তারিখ সফলভাবে পরিবর্তন হয়েছে!";
    } else {
        $_SESSION['error'] = "❌ তারিখ পরিবর্তনে সমস্যা হয়েছে!";
    }
}

header("Location: ../index.php");
exit();
