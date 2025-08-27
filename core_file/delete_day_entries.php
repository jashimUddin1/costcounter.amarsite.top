<?php //delete_day_entries.php 
session_start();
include("../db/dbcon.php");

// 🔐 সেশন চেক
if (!isset($_SESSION['authenticated']) || !isset($_SESSION['auth_user']['id'])) {
    $_SESSION['warning'] = "❌ অননুমোদিত অনুরোধ!";
    header("Location: ../login/index.php");
    exit();
}

$user_id = $_SESSION['auth_user']['id'];

// 🔸 ইংরেজি → বাংলা সংখ্যা রূপান্তর
function en2bn_number($number) {
    $en = ['0','1','2','3','4','5','6','7','8','9'];
    $bn = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
    return str_replace($en, $bn, $number);
}

// 🔸 মাসের নাম বাংলায়
function bn_month_name($month_en) {
    $months = [
        'January' => 'জানুয়ারি',
        'February' => 'ফেব্রুয়ারি',
        'March' => 'মার্চ',
        'April' => 'এপ্রিল',
        'May' => 'মে',
        'June' => 'জুন',
        'July' => 'জুলাই',
        'August' => 'আগস্ট',
        'September' => 'সেপ্টেম্বর',
        'October' => 'অক্টোবর',
        'November' => 'নভেম্বর',
        'December' => 'ডিসেম্বর'
    ];
    return $months[$month_en] ?? $month_en;
}

if (isset($_GET['date'])) {
    $raw_date = $_GET['date']; // Expected: d-m-Y
    $date = date('Y-m-d', strtotime($raw_date)); // convert to DB format

    $stmt = $con->prepare("DELETE FROM cost_data WHERE user_id = ? AND date = ?");
    $stmt->bind_param("is", $user_id, $date);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            // ✅ বাংলা তারিখ বানানো
            $day_bn   = en2bn_number(date('j', strtotime($date)));
            $month_bn = bn_month_name(date('F', strtotime($date)));
            $year_bn  = en2bn_number(date('Y', strtotime($date)));

            $bangla_date = $day_bn . " " . $month_bn . " " . $year_bn;

            $_SESSION['warning'] = "<strong>{$bangla_date}</strong>  তারিখের সব এন্ট্রি মুছে ফেলা হয়েছে!";
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
