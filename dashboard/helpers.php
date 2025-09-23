<?php
// English → Bangla
$month_map = [
    'January'   => 'জানুয়ারি',
    'February'  => 'ফেব্রুয়ারি',
    'March'     => 'মার্চ',
    'April'     => 'এপ্রিল',
    'May'       => 'মে',
    'June'      => 'জুন',
    'July'      => 'জুলাই',
    'August'    => 'আগস্ট',
    'September' => 'সেপ্টেম্বর',
    'October'   => 'অক্টোবর',
    'November'  => 'নভেম্বর',
    'December'  => 'ডিসেম্বর'
];

// Month Number → English
$month_num_to_en = [
    1  => 'January',
    2  => 'February',
    3  => 'March',
    4  => 'April',
    5  => 'May',
    6  => 'June',
    7  => 'July',
    8  => 'August',
    9  => 'September',
    10 => 'October',
    11 => 'November',
    12 => 'December'
];

$months_en = array_keys($month_map);

/* --------------------------
   Helper Functions
---------------------------*/
function en2bn_number($number) {
    $en = ['0','1','2','3','4','5','6','7','8','9'];
    $bn = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
    return str_replace($en, $bn, (string)$number);
}

function format_currency_bn($amount) {
    return en2bn_number(number_format((float)$amount, 0)) . ' টাকা';
}

/* --------------------------
   Inputs
---------------------------*/
$year  = $_GET['year']  ?? date('Y');
$month = $_GET['month'] ?? date('F');

if (is_numeric($month)) {
    $month_num = (int)$month;
    if (isset($month_num_to_en[$month_num])) {
        $month = $month_num_to_en[$month_num];
    }
}

$is_all_year  = (strtolower($year) === 'all');
$is_all_month = (strtolower($month) === 'all');

if ($is_all_year) {
    $month_label = "সকল মাস";
    $year_bn     = "সব বছর";
} else {
    $month_label = $is_all_month ? 'সকল মাস' : ($month_map[$month] ?? $month);
    $year_bn     = en2bn_number($year);
}

$excluded_categories = ['প্রাপ্তি', 'প্রদান', 'আয়'];
