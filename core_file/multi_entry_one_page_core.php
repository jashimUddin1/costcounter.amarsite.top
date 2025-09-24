<?php
// core_file/multi_entry_one_page_core.php
session_start();
include("../db/dbcon.php");

// ইউজার লগইন চেক
if (!isset($_SESSION['authenticated'])) {
    header("Location: ../login/index.php");
    exit();
}

$user_id = $_SESSION['auth_user']['id'] ?? null;
if (!$user_id) {
    $_SESSION['danger'] = "❌ User ID missing!";
    header("Location: ../index.php");
    exit();
}

// Redirect query
$redirect_query = $_POST['redirect_query'] ?? '';

// Input
$bulk_description = trim($_POST['bulk_description'] ?? '');
if ($bulk_description === '') {
    $_SESSION['warning'] = "❌ ইনপুট ফাঁকা রাখা যাবে না!";
    header("Location: ../index.php?$redirect_query");
    exit();
}

// 🔢 সংখ্যা কনভার্সন
function bn2en($str) {
    $bn = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
    $en = ['0','1','2','3','4','5','6','7','8','9'];
    return str_replace($bn, $en, $str);
}

 function en2bn_number($str)
    {
        $eng = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $bn = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
        return str_replace($eng, $bn, $str);
    }

// detectCategory dummy (real system এ আপনার DB থেকে আনতে পারেন)
function detectCategory($desc) {
    if (mb_stripos($desc, 'খাবার') !== false) return 'খাবার';
    if (mb_stripos($desc, 'বাজার') !== false) return 'বাজার';
    if (mb_stripos($desc, 'ফল') !== false) return 'ফল';
    return 'অন্যান্য';
}

// ======================
// Parser Function
// ======================
function parseEntries($text) {
    global $bnMonths, $enMonths;
    $bnMonths = ['জানুয়ারি','ফেব্রুয়ারি','মার্চ','এপ্রিল','মে','জুন','জুলাই','আগস্ট','সেপ্টেম্বর','অক্টোবর','নভেম্বর','ডিসেম্বর'];
    $enMonths = ['January','February','March','April','May','June','July','August','September','October','November','December'];

    $lines = preg_split("/\r\n|\n|\r/", $text);
    $results = [];
    $currentDate = null;

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') continue;

        // 1) YYYY-MM-DD : desc
        if (preg_match('/^(\d{4}-\d{2}-\d{2})\s*:?\s*(.+)?$/', $line, $m)) {
            $currentDate = $m[1];
            if (!empty($m[2])) {
                $entries = preg_split("/,|\n/", $m[2]);
                foreach ($entries as $e) addEntry($e, $currentDate, $results);
            }
            continue;
        }

        // 2) dd/mm/yyyy বা d-m-Y
        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $line, $m)) {
            $d = str_pad($m[1], 2, "0", STR_PAD_LEFT);
            $mth = str_pad($m[2], 2, "0", STR_PAD_LEFT);
            $y = $m[3];
            $currentDate = "$y-$mth-$d";
            continue;
        }

        // 3) English Month: 15 July 2025
        if (preg_match('/^(\d{1,2})\s+([A-Za-z]+)\s+(\d{4})/', $line, $m)) {
            $d = str_pad($m[1], 2, "0", STR_PAD_LEFT);
            $y = $m[3];
            $mth = array_search(ucfirst(strtolower($m[2])), $enMonths);
            if ($mth === false) {
                foreach ($enMonths as $idx => $mon) {
                    if (stripos($mon, $m[2]) === 0) $mth = $idx;
                }
            }
            $currentDate = "$y-".str_pad($mth+1,2,"0",STR_PAD_LEFT)."-$d";
            continue;
        }

        // 4) বাংলা তারিখ: ১ জুলাই ২০২৫
        $lineEn = bn2en($line);
        if (preg_match('/^(\d{1,2})\s*([^\s\d]+)\s*(\d{4})/', $lineEn, $m)) {
            $d = str_pad($m[1], 2, "0", STR_PAD_LEFT);
            $y = $m[3];
            $mth = 0;
            foreach ($bnMonths as $idx => $mon) {
                if (mb_strpos($mon, $m[2]) !== false) $mth = $idx+1;
            }
            $currentDate = "$y-".str_pad($mth,2,"0",STR_PAD_LEFT)."-$d";
            continue;
        }

        // Entry line
        if ($currentDate) {
            addEntry($line, $currentDate, $results);
        }
    }

    return $results;
}

// 🔧 Process single entry line
function addEntry($entry, $date, &$results) {
    $entry = bn2en($entry);
    $entry = preg_replace('/^\d+\.\s*/u', '', $entry); // serial বাদ
    $entry = str_ireplace(['টাকা','৳','tk'], '', $entry);

    if (preg_match('/(.+?)\s*([\d\+]+)/u', $entry, $m)) {
        $desc = trim($m[1]);
        $amt_str = $m[2];
        $parts = explode('+', $amt_str);
        $amt = 0;
        foreach ($parts as $p) $amt += floatval(trim($p));
        $results[] = ['date'=>$date,'desc'=>$desc,'amt'=>$amt];
    }
}

// ======================
// Insert into DB
// ======================
$entries = parseEntries($bulk_description);
$inserted = 0;
$created_at = date('Y-m-d H:i:s');

foreach ($entries as $row) {
    $date = $row['date'] ?? date('Y-m-d'); // fallback আজকের তারিখ
    $year = date('Y', strtotime($date));
    $month = date('F', strtotime($date));
    $day_name = date('l', strtotime($date));

    // ওই তারিখে সর্বশেষ serial খুঁজে বের করো
    $serial_query = $con->prepare("SELECT MAX(serial) as max_serial FROM cost_data WHERE user_id=? AND date=?");
    $serial_query->bind_param("is", $user_id, $date);
    $serial_query->execute();
    $max_s = $serial_query->get_result()->fetch_assoc()['max_serial'] ?? 0;
    $serial_query->close();
    $serial = $max_s + 1;

    $category = detectCategory($row['desc']);

    $stmt = $con->prepare("INSERT INTO cost_data 
        (user_id,year,month,date,day_name,description,amount,match_keyword,category,serial,created_at) 
        VALUES (?,?,?,?,?,?,?,?,?,?,?)");
    $mk = ''; // match_keyword আপাতত খালি
    $stmt->bind_param("iissssissis",
        $user_id,$year,$month,$date,$day_name,
        $row['desc'],$row['amt'],$mk,$category,$serial,$created_at
    );
    if ($stmt->execute()) $inserted++;
    $serial++;
}

// ======================
// Message & Redirect
// ======================
if ($inserted>0) {
    $_SESSION['success'] = "✅ " . en2bn_number($inserted) . "টি এন্ট্রি যোগ হয়েছে!";
} else {
    $_SESSION['danger'] = "❌ কোনো এন্ট্রি যোগ হয়নি! ফরম্যাট চেক করুন!";
}
header("Location: ../index.php?$redirect_query");
exit();
