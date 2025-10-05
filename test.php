<?php
include "db/dbcon.php"; // তোমার ডাটাবেজ কানেকশন ফাইল

$user_id = 54;
$year = 2025;
$month = "September";

// 🔹 ইংরেজি থেকে বাংলা নাম্বার কনভার্ট
function en2bn_number($number)
{
    $en = ["0", "1", "2", "3", "4", "5", "6", "7", "8", "9"];
    $bn = ["০", "১", "২", "৩", "৪", "৫", "৬", "৭", "৮", "৯"];
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
function format_date_bn($date_str)
{
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



example:
cell a ase 490
title a details ta dekhabe
3 dine 7 entry te 40+50+30+150+50+70+100=490


<tr>
    <td><?= en2bn_number($d) ?></td>
    <?php foreach ($active_cats as $cat): ?>
        <?php if ($cat === 'মোট ব্যয়'): ?>
            <td class="fw-bold bg-light"><?= $daily_total ? en2bn_number($daily_total) : '' ?></td>
        <?php else:
            $val = $days[$d][$cat] ?? 0;
            $vals = $dashboard_three_breakdown[$month][$d][$cat] ?? [];

            // ✅ Tooltip তৈরি করা
            if ($vals) {
                $entry_count = count($vals);
                $joined = implode(' + ', array_map('en2bn_number', $vals));
                $sum_bn = en2bn_number(array_sum($vals));
                $title = "{$entry_count} এন্ট্রি: {$joined} = {$sum_bn}";
            } else {
                $title = '';
            }
            ?>
            <td title="<?= htmlspecialchars($title) ?>">
                <?= $val ? en2bn_number($val) : '' ?>
            </td>
        <?php endif; ?>
    <?php endforeach; ?>
</tr>



tar cheye aivabe korte parba ki ?
title a a thakbe = date. amount + amount = sum, date. amount + amount = sum
example: 
2. 50+20 = 70, 5. 10+5 = 15, 8. 5, 20. 55+5 =60



daw tobe ar aktu correction kore 

২. ৫০+২০ = ৭০  
৫. ১০+৫ = ১৫  
৮. ৫  
২০. ৫৫+৫+১০+২০ = ৯০
৪ দিনে মোট ১৮০




header = $year
তাং =  মাস
category thik e thakbe 
tariq ar data ar jaygay month name thakbe 

example:

    ২০২৫
মাস	বাজার	বাহিরেরখরচ	দাওয়াতখরচ	চিকিৎসা	মোবাইলখরচ	গাড়িভাড়া	বাসাভাড়া	গৃহস্থালীজিনিসপত্র	অন্যান্য	মোট ব্যয়	আয়
জুলাই	৩০০	৫০০								৮০	                                                                20000                              
আগস্ট	৫০০	১১৬০								১২১০	                                                    30000
সেপ্টেম্বর	২৫০	৫৪০	১০০৫							১৬১০	                                                25000

মোট	১০৫০	২২০০	১০০৫							২৯০০	                                                 75000   
মোট আয়: ৭৫০০০ টাকা
মোট ব্যয়: 76000 টাকা
দায়: ১,০০০ টাকা


























year = all hole -- sei kaj akhono kora hoy nai 
seta kore daw