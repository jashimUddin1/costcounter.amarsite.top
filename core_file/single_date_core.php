<?php
// core_file/single_date_core.php

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
    $day_name = date('l', strtotime($date)); // English day name

    // 🔸 ক্যাটাগরি কিওয়ার্ড ম্যাপ
    $category_map = [
        'বাজার' => ['বাজার', 'চাল', 'আলু', 'ডিম', 'মরিচ', 'টমেটো', 'রেহা', 'মুলা', 'পেঁয়াজ', 'রসুন', 'তেল', 'তরকারি'],

        'বাহিরেরখরচ' => ['খাবার', 'ফল', 'রুটি'],
        'মোবাইলখরচ' => ['রিচার্জ', 'ফ্লেক্সিলোড', 'টপআপ'],
        'গাড়িভাড়া' => ['গাড়ি ভাড়া', 'বাস ভাড়া', 'রিক্সা ভাড়া'],
        'বাসাভাড়া' => ['ভাড়া', 'বাড়িভাড়া', 'হাউস রেন্ট'],

        'বিল' => ['বিদ্যুৎ', 'ইন্টারনেট', 'গ্যাস', 'পানি'],
        'গৃহস্থালীজিনিসপত্র' => ['জিনিস', 'বোতল', 'বালতি', 'ব্যাগ'],
        'গৃহস্থালীমেরামত' => ['গৃহস্থালী মেরামত'],
        'মালজিনিস' => ['মাল জিনিস', 'মালজিনিস'],

        'কসমেটিক্স' => ['কসমেটিক্স'],
        'দাওয়াতখরচ' => ['দাওয়াত খরচ', 'দাওয়াতখরচ'],

        'চিকিৎসা' => ['ঔষধ', 'ডাক্তার', 'হাসপাতাল'],
        'কেনাকাটা' => ['পোশাক', 'শার্ট', 'প্যান্ট', 'জুতা', 'কাপড়'],

        'বইখাতা' => ['বইখাতা', 'বই খাতা'],
        'পরিবার' => ['পরিবার'],
        'সাইকেলমেরামত' => ['সাইকেল মেরামত'],

        'প্রাপ্তি' => ['প্রাপ্তি'],
        'প্রদান' => ['প্রদান'],
        'আয়' => ['আয়'],

        'অন্যান্য' => ['অন্যান্য'],
    ];

    // 🔸 ক্যাটাগরি খোঁজার ফাংশন
    function detectCategory($description, $category_map)
    {
        foreach ($category_map as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (mb_strpos($description, $keyword) !== false) {
                    return $category;
                }
            }
        }
        return 'অন্যান্য';
    }

    // 🔸 বিবরণ গুলো আলাদা করো
    $entries = explode(',', $bulk_description);
    $inserted = 0;

    foreach ($entries as $entry) {
        $entry = trim($entry);

        // ধরো: "খাবার ৫০" বা "ফল৫৩০"
        if (preg_match('/^(.+?)[\s]?(\d+(\.\d{1,2})?)$/u', $entry, $matches)) {
            $description = trim($matches[1]);
            $amount = floatval($matches[2]);
            $category = detectCategory($description, $category_map);
            

            $stmt = $con->prepare("INSERT INTO cost_data 
                (user_id, year, month, date, day_name, description, amount, category, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param(
                "iissssdss",
                $user_id,
                $year,
                $month,
                $date,
                $day_name,
                $description,
                $amount,
                $category,
                $created_at
            );

            if ($stmt->execute()) {
                $inserted++;
            }
        }
    }

    // ✅ ফিডব্যাক
    if ($inserted > 0) {
        $_SESSION['success'] = "✅ {$inserted}টি খরচ সফলভাবে যোগ হয়েছে!";
    } else {
        $_SESSION['danger'] = "❌ কোনো খরচ যোগ হয়নি! ফরম্যাট চেক করুন!";
    }

    header("Location: ../index.php?$redirect_query");
    exit();
} else {
    // সরাসরি পেইজে এলে
    header("Location: ../index.php");
    exit();
}
