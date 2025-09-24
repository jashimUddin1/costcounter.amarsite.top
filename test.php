<?php
include "db/dbcon.php"; // তোমার ডাটাবেজ কানেকশন ফাইল

$user_id = 54; 
$year = 2025;
$month = "September";

// 🔹 ইংরেজি থেকে বাংলা নাম্বার কনভার্ট
function en2bn_number($number) {
    $en = ["0","1","2","3","4","5","6","7","8","9"];
    $bn = ["০","১","২","৩","৪","৫","৬","৭","৮","৯"];
    return str_replace($en, $bn, $number);
}

// 🔹 মাসের নাম ম্যাপিং
$month_map = [
    "January" => "জানুয়ারি",
    "February" => "ফেব্রুয়ারি",
    "March" => "মার্চ",
    "April" => "এপ্রিল",
    "May" => "মে",
    "June" => "জুন",
    "July" => "জুলাই",
    "August" => "আগস্ট",
    "September" => "সেপ্টেম্বর",
    "October" => "অক্টোবর",
    "November" => "নভেম্বর",
    "December" => "ডিসেম্বর"
];

// 🔹 বাংলা তারিখ ফরম্যাট
function format_date_bn($date_str) {
    global $month_map;
    $time = strtotime($date_str);
    $day = en2bn_number(date("j", $time));
    $month_en = date("F", $time);
    $month_bn = $month_map[$month_en];
    $year = en2bn_number(date("Y", $time));
    return $day . " " . $month_bn . " " . $year;
}

// কুয়েরি
$query = "SELECT date, serial, description, amount 
          FROM cost_data 
          WHERE user_id = '$user_id' 
            AND year = '$year' 
            AND month = '$month'
          ORDER BY date ASC, serial ASC";

$result = mysqli_query($con, $query);

$current_date = "";
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $date = $row['date'];
        $serial = en2bn_number($row['serial']);
        $desc = $row['description'];
        $amount = en2bn_number($row['amount']);

        // নতুন তারিখ হলে হেডার দেখাও
        if ($current_date != $date) {
            if ($current_date != "") {
                echo "<br>"; // আগের দিনের লিস্ট শেষ
            }
            echo "<h5><b>" . format_date_bn($date) . "</b></h5>";
            $current_date = $date;
        }

        // লিস্ট আইটেম
        echo $serial . ". " . $desc . " " . $amount . " টাকা<br>";
    }
} else {
    echo "কোনো ডাটা পাওয়া যায়নি!";
}
?>
