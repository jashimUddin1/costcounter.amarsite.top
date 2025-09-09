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

    function en2bn_number($str)
    {
        $eng = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $bn = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
        return str_replace($eng, $bn, $str);
    }

    // 🔸 বাংলা সংখ্যা থেকে ইংরেজি রূপান্তর ফাংশন
    function bn2en_number($string)
    {
        $bn = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
        $en = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        return str_replace($bn, $en, $string);
    }


    // 🔸 ক্যাটাগরি কিওয়ার্ড ম্যাপ
    $category_map = [];  // খালি array

    $stmt = $con->prepare("SELECT category_name, category_keywords FROM categories WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {
        $cat_name = trim($row['category_name']);
        $cats = trim($row['category_keywords']);

        if ($cats !== '') {
            $keywords = array_map('trim', explode(',', $cats));
            $category_map[$cat_name] = $keywords;
        } else {
            $category_map[$cat_name] = []; // কীওয়ার্ড না থাকলে খালি array
        }
    }

    $stmt->close();



    // 🔸 ক্যাটাগরি খোঁজার ফাংশন --> old
    // function detectCategory($description, $category_map)
    // {
    //     $desc_lower = mb_strtolower($description);
    //     foreach ($category_map as $category => $keywords) {
    //         foreach ($keywords as $keyword) {
    //             if (mb_strpos($desc_lower, mb_strtolower($keyword)) !== false) {
    //                 return $category;
    //             }
    //         }
    //     }
    //     return 'অন্যান্য';
    // }


    function detectCategory($description, $category_map)    // category found for best keyword function
    {
        $desc_lower = mb_strtolower(trim($description));
        $best_match = 'অন্যান্য';
        $best_length = 0;

        foreach ($category_map as $category => $keywords) {
            foreach ($keywords as $keyword) {
                $kw = mb_strtolower(trim($keyword));
                if ($kw === '')
                    continue;

                // মিল খুঁজো
                if (mb_strpos($desc_lower, $kw) !== false) {
                    // লম্বা keyword হলে ওটাকেই প্রাধান্য দাও
                    if (mb_strlen($kw) > $best_length) {
                        $best_match = $category;
                        $best_length = mb_strlen($kw);
                    }
                }
            }
        }

        return $best_match;
    }




    // function detectCategory($description, $category_map)
    // {
    //     $desc_lower = mb_strtolower(trim($description));

    //     foreach ($category_map as $category => $keywords) {
    //         foreach ($keywords as $keyword) {
    //             $kw = mb_strtolower(trim($keyword));

    //             if ($kw === '')
    //                 continue; // ফাঁকা বাদ

    //             // শব্দ ম্যাচ (পুরো শব্দ মিলবে, আংশিক নয়)
    //             if (preg_match('/\b' . preg_quote($kw, '/') . '\b/u', $desc_lower)) {
    //                 return $category;
    //             }
    //         }
    //     }

    //     return 'অন্যান্য';
    // }


    $entries = explode(',', $bulk_description);
    $inserted = 0;

    // ওই তারিখে সর্বশেষ serial খুঁজে বের করো
    $serial_query = $con->prepare("SELECT MAX(serial) as max_serial FROM cost_data WHERE user_id = ? AND date = ?");
    $serial_query->bind_param("is", $user_id, $date);
    $serial_query->execute();
    $serial_result = $serial_query->get_result()->fetch_assoc();
    $serial = ($serial_result['max_serial'] ?? 0) + 1;
    $serial_query->close();



    foreach ($entries as $entry) {
        $entry = trim($entry);

        // বাংলা সংখ্যা ইংরেজি করো
        $entry = bn2en_number($entry);

        // "১. " বা "1. " এই ধরণের সিরিয়াল রিমুভ করো
        $entry = preg_replace('/^\d+\.\s*/u', '', $entry);

        // "টাকা" শব্দ রিমুভ করো
        $entry = str_replace([' টাকা', 'টাকা', ' tk', 'tk'], '', $entry);

        // যদি পরিমাণে প্লাস থাকে (যেমন: 20+20+10)
        if (preg_match('/^(.+?)\s*([\d\+\.\s]+)$/u', $entry, $matches)) {

            $description = trim($matches[1]);
            $amount_str = trim($matches[2]);

            // প্লাস দিয়ে ভাগ করে যোগফল বের করো
            $parts = explode('+', $amount_str);
            $total_amount = 0;
            foreach ($parts as $p) {
                $total_amount += floatval(trim($p));
            }

            $category = detectCategory($description, $category_map);

            $stmt = $con->prepare("INSERT INTO cost_data 
                (user_id, year, month, date, day_name, description, amount, category, serial, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param(
                "iissssdsis",
                $user_id,
                $year,
                $month,
                $date,
                $day_name,
                $description,
                $total_amount,
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