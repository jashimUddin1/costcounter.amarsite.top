<?php // core_file/single_date_core.php
session_start();
include("../db/dbcon.php");

// ইউজার লগইন না করলে রিডাইরেক্ট করো
if (!isset($_SESSION['authenticated'])) {
    header("Location: ../login/index.php");
    exit();
}

$user_id = $_SESSION['auth_user']['id'] ?? null;

// ফর্ম সাবমিট চেক
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'] ?? '';
    $to_date = $_POST['to_date'] ?? null;
    $bulk_description = $_POST['bulk_description'] ?? '';
    $redirect_query = $_POST['redirect_query'] ?? '';
    $created_at = date('Y-m-d H:i:s');

    // ফিল্ড ভ্যালিডেশন
    if (empty($date) || empty($bulk_description)) {
        $_SESSION['warning'] = "❌ ইনপুট ফাঁকা রাখা যাবে না!";
        header("Location: ../index.php?$redirect_query");
        exit();
    }

    // তারিখ থেকে year, month, day_name বের করো
    $year = date('Y', strtotime($date));
    $month = date('F', strtotime($date));
    $day_name = date('l', strtotime($date));

    // 🔸 ইংরেজি ↔ বাংলা সংখ্যা রূপান্তর
    function en2bn_number($str)
    {
        $eng = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $bn = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
        return str_replace($eng, $bn, $str);
    }
    function bn2en_number($str)
    {
        $bn = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
        $eng = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        return str_replace($bn, $eng, $str);
    }

    // 🔸 ক্যাটাগরি কিওয়ার্ড ম্যাপ (nested category → sub_category → keywords)
    $category_map = [];
    $stmt = $con->prepare("SELECT category_name, sub_category, category_keywords 
                           FROM categories 
                           WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {
        $cat_name = trim($row['category_name']);
        $sub_cat = trim($row['sub_category']);
        if ($sub_cat === '' || strtolower($sub_cat) === 'none') {
            $sub_cat = 'none';
        }

        $cats = trim($row['category_keywords']);
        if ($cats !== '') {
            $keywords = array_map('trim', explode(',', $cats));
        } else {
            $keywords = [];
        }

        if (!isset($category_map[$cat_name])) {
            $category_map[$cat_name] = [];
        }
        $category_map[$cat_name][$sub_cat] = $keywords;
    }
    $stmt->close();

    // 🔸 detectCategory function
    function detectCategory($description, $category_map)
    {
        $desc_lower = mb_strtolower(trim($description));
        $best_match = [
            'category' => 'অন্যান্য',
            'keyword' => ''
        ];
        $best_length = 0;

        foreach ($category_map as $category => $subcats) {
            foreach ($subcats as $sub_cat => $keywords) {
                foreach ($keywords as $keyword) {
                    $kw = mb_strtolower(trim($keyword));
                    if ($kw === '')
                        continue;

                    if (mb_strpos($desc_lower, $kw) !== false) {
                        if (mb_strlen($kw) > $best_length) {
                            $best_match['category'] = $category;
                            $best_match['keyword'] = $keyword;
                            $best_length = mb_strlen($kw);
                        }
                    }
                }
            }
        }
        return $best_match;
    }

    // ======================
    // Entry processing
    // ======================
    $entries = explode(',', $bulk_description);
    $inserted = 0;

    // ওই তারিখে সর্বশেষ serial খুঁজে বের করো
    $serial_query = $con->prepare("SELECT MAX(serial) as max_serial 
                                   FROM cost_data 
                                   WHERE user_id = ? AND date = ?");
    $serial_query->bind_param("is", $user_id, $date);
    $serial_query->execute();
    $serial_result = $serial_query->get_result()->fetch_assoc();
    $serial = ($serial_result['max_serial'] ?? 0) + 1;
    $serial_query->close();

    foreach ($entries as $entry) {
        $entry = trim($entry);
        $entry = bn2en_number($entry); // 
        // serial no remove
        $entry = preg_replace('/^\d+\.\s*/u', '', $entry);
        // tk remove
        $entry = str_replace([' টাকা', 'টাকা', ' tk', 'tk'], '', $entry);

        if (preg_match('/^(.+?)\s*([\d\+\.\s]+)$/u', $entry, $matches)) {
            $description = trim($matches[1]);
            $amount_str = trim($matches[2]);

            // প্লাস দিয়ে আলাদা করে যোগফল
            $parts = explode('+', $amount_str);
            $total_amount = 0;
            foreach ($parts as $p) {
                $total_amount += floatval(trim($p));
            }

            $result = detectCategory($description, $category_map);
            $category = $result['category'];
            $match_keyword = $result['keyword'];

            $stmt = $con->prepare("INSERT INTO cost_data (user_id, year, month, date, to_date, day_name, description, amount, match_keyword, category, serial, created_at)  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param(
                "iisssssissis",
                $user_id,
                $year,
                $month,
                $date,
                $to_date,
                $day_name,
                $description,
                $total_amount,
                $match_keyword,
                $category,
                $serial,
                $created_at
            );


            if ($stmt->execute()) {
                $inserted++;
                $serial++;
            }
        }
    }

    // ======================
    // Session Message
    // ======================
    if ($inserted > 0) {
        $_SESSION['success'] = "✅ " . en2bn_number($inserted) . "টি এন্টি সফলভাবে যোগ হয়েছে!";
    } else {
        $_SESSION['danger'] = "❌ কোনো এন্টি যোগ হয়নি! ফরম্যাট চেক করুন!";
    }

    header("Location: ../index.php?$redirect_query");
    exit();

} else {
    header("Location: ../index.php");
    exit();
}
