<?php
session_start();
include("../db/dbcon.php");

if (!isset($_SESSION['authenticated'])) {
    header("Location: ../login/index.php");
    exit();
}

$user_id = $_SESSION['auth_user']['id'] ?? null;
if (!$user_id) {
    $_SESSION['danger'] = "❌ Unauthorized access!";
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php");
    exit();
}

$redirect_query = $_POST['redirect_query'] ?? '';
$bulk_text      = $_POST['bulk_description'] ?? '';
$created_at     = date('Y-m-d H:i:s');

// =========================
// Helper Functions
// =========================
function bn2en_number($s) {
    $bn = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
    $en = ['0','1','2','3','4','5','6','7','8','9'];
    return str_replace($bn,$en,$s);
}

// বাংলা/ইংরেজি মাস mapping
$monthMap = [
    // English
    'January'=>'January','February'=>'February','March'=>'March','April'=>'April',
    'May'=>'May','June'=>'June','July'=>'July','August'=>'August',
    'September'=>'September','October'=>'October','November'=>'November','December'=>'December',

    // Bangla variations
    'জানুয়ারি'=>'January','জানুয়ারী'=>'January','জানুয়ারি'=>'January',
    'ফেব্রুয়ারি'=>'February','ফেব্রুয়ারি'=>'February',
    'মার্চ'=>'March','এপ্রিল'=>'April','মে'=>'May','জুন'=>'June','জুলাই'=>'July',
    'আগস্ট'=>'August','সেপ্টেম্বর'=>'September','অক্টোবর'=>'October',
    'নভেম্বর'=>'November','ডিসেম্বর'=>'December'
];

// দিননাম regex (বাংলা + ইংরেজি)
$dayRegex = '(?:monday|tuesday|wednesday|thursday|friday|saturday|sunday|রবিবার|সোমবার|মঙ্গলবার|বুধবার|বৃহস্পতিবার|শুক্রবার|শনিবার)';

// =========================
// Category Map Load
// =========================
$category_map = [];
$stmt = $con->prepare("SELECT category_name, sub_category, category_keywords 
                       FROM categories 
                       WHERE user_id = ?");
$stmt->bind_param("i",$user_id);
$stmt->execute();
$res = $stmt->get_result();
while($row = $res->fetch_assoc()){
    $cat_name = trim($row['category_name']);
    $sub_cat  = trim($row['sub_category']);
    if ($sub_cat==='' || strtolower($sub_cat)==='none') $sub_cat='none';

    $cats = trim($row['category_keywords']);
    $keywords = $cats!==''? array_map('trim',explode(',',$cats)):[];

    if (!isset($category_map[$cat_name])) $category_map[$cat_name] = [];
    $category_map[$cat_name][$sub_cat] = $keywords;
}
$stmt->close();

function detectCategory($description,$category_map){
    $desc_lower = mb_strtolower(trim($description));
    $desc_lower = preg_replace('/\s+/u',' ',$desc_lower);

    $best = ['category'=>'অন্যান্য','keyword'=>''];
    $best_length=0;

    foreach($category_map as $cat=>$subcats){
        foreach($subcats as $sub=>$keywords){
            foreach($keywords as $kw){
                $kw = mb_strtolower(trim($kw));
                if ($kw==='') continue;
                $kw = preg_replace('/\s+/u',' ',$kw);

                if (mb_strpos($desc_lower,$kw)!==false){
                    if (mb_strlen($kw)>$best_length){
                        $best['category']=$cat;
                        $best['keyword']=$kw;
                        $best_length=mb_strlen($kw);
                    }
                }
            }
        }
    }
    return $best;
}

// =========================
// Entry processors
// =========================
function process_single_entry($entry, $date, &$entries_by_date, $debugFile){
    // বাংলা→ইংরেজি সংখ্যা, সিরিয়াল/টাকা বাদ, desc+amount split
    $line = bn2en_number(trim($entry));
    if ($line==='') return;

    // সিরিয়াল নম্বর বাদ (যেমন: "২." / "2:" / "2-" ইত্যাদি)
    $line = preg_replace('/^\d+[\.\-:]?\s*/u','',$line);
    // টাকা শব্দ/চিহ্ন বাদ
    $line = str_ireplace([' টাকা','টাকা',' tk','tk','৳'],'',$line);

    if (preg_match('/^(.+?)\s*([\d\+\.\s]+)$/u',$line,$m)){
        $desc   = trim($m[1]);
        $amtStr = trim($m[2]);

        // "40+50" যোগফল
        $parts = array_filter(array_map('trim', explode('+',$amtStr)), fn($v)=>$v!=='');
        $amt = 0;
        foreach($parts as $p) $amt += floatval($p);

        $entries_by_date[$date][] = [
            'description'=>$desc,
            'amount'=>$amt
        ];
        file_put_contents($debugFile, "ENTRY: [$date] $desc = $amt\n", FILE_APPEND);
    } else {
        file_put_contents($debugFile, "NO MATCH INLINE: [$entry]\n", FILE_APPEND);
    }
}

function process_inline_entries($text, $date, &$entries_by_date, $debugFile, $dayRegex){
    if (!$date) return;
    if ($text===null) return;

    // শুরুর কোলন/ড্যাশ/দিননাম বাদ
    $clean = preg_replace('/^\s*[:,\-–—]\s*/u','', $text); // শুরুতে থাকা : , - মুছি
    $clean = preg_replace('/^\s*'.$dayRegex.'\s*[:,\-–—]?\s*/iu','', $clean); // শুরুতে day থাকলে মুছি

    $clean = trim($clean);
    if ($clean==='') return;

    file_put_contents($debugFile, "INLINE AFTER DATE: [$clean]\n", FILE_APPEND);

    // কমা/আরবি কমা/পাইপ দিয়ে ভাগ
    $chunks = preg_split('/[,\|،]+/u', $clean);
    foreach($chunks as $chunk){
        $chunk = trim($chunk);
        if ($chunk==='') continue;
        process_single_entry($chunk, $date, $entries_by_date, $debugFile);
    }
}

// =========================
$lines = preg_split('/\r\n|\r|\n/',$bulk_text);
$current_date='';
$entries_by_date=[];

$debugFile = __DIR__ . "/../debug_log.txt";
file_put_contents($debugFile, "==== NEW RUN ".date("Y-m-d H:i:s")." ====\n");

foreach($lines as $line){
    $line = trim($line);
    if ($line==='') continue;

    file_put_contents($debugFile, "RAW: [$line]\n", FILE_APPEND);

    // ---- 1) YYYY-MM-DD (inline entries allowed) ----
    if (preg_match('/^(\d{4}-\d{2}-\d{2})\s*[:,\-–—]?\s*(.*)$/u',$line,$m)){
        $current_date = $m[1];
        file_put_contents($debugFile, "DATE DETECTED: $current_date\n", FILE_APPEND);
        $rest = trim($m[2] ?? '');
        if ($rest!==''){
            process_inline_entries($rest, $current_date, $entries_by_date, $debugFile, $dayRegex);
        }
        continue;
    }

    // ---- 2) dd/mm/yyyy বা dd-mm-yyyy + optional day + inline entries ----
    if (preg_match('/^(\d{1,2}[\/\-]\d{1,2}[\/\-]\d{4})\s*(?:'.$dayRegex.')?\s*[:,\-–—]?\s*(.*)$/iu',$line,$m)){
        $current_date = date('Y-m-d', strtotime(bn2en_number($m[1])));
        file_put_contents($debugFile, "DATE DETECTED: $current_date\n", FILE_APPEND);
        $rest = trim($m[2] ?? '');
        if ($rest!==''){
            process_inline_entries($rest, $current_date, $entries_by_date, $debugFile, $dayRegex);
        }
        continue;
    }

    // ---- 3) d Month yyyy (Bangla/English) + optional day + inline entries ----
    if (preg_match('/^(\d{1,2})\s*([A-Za-z]+|\p{Bengali}+)\s+(\d{4})(?:\s+(?:'.$dayRegex.'))?\s*[:,\-–—]?\s*(.*)$/u',$line,$m)){
        $d      = bn2en_number($m[1]);
        $mn_raw = trim($m[2]);
        $y      = bn2en_number($m[3]);
        $rest   = trim($m[4] ?? '');

        global $monthMap;
        $mn = $monthMap[$mn_raw] ?? $mn_raw;

        $current_date = date('Y-m-d', strtotime("$d $mn $y"));
        file_put_contents($debugFile, "DATE DETECTED: $current_date\n", FILE_APPEND);

        if ($rest!==''){
            process_inline_entries($rest, $current_date, $entries_by_date, $debugFile, $dayRegex);
        }
        continue;
    }

    // ---- 4) যদি আজকের লাইনটা entry হয় (তারিখ আগের লাইনে ধরা আছে) ----
    if ($current_date!==''){
        process_single_entry($line, $current_date, $entries_by_date, $debugFile);
    }
}

// =========================
// Insert into DB
// =========================
$inserted=0;
foreach($entries_by_date as $date=>$items){
    $year=date('Y',strtotime($date));
    $month=date('F',strtotime($date));
    $day_name=date('l',strtotime($date));

    // last serial
    $serial_query=$con->prepare("SELECT MAX(serial) as max_serial FROM cost_data WHERE user_id=? AND date=?");
    $serial_query->bind_param("is",$user_id,$date);
    $serial_query->execute();
    $max_s=$serial_query->get_result()->fetch_assoc()['max_serial'] ?? 0;
    $serial_query->close();
    $serial=$max_s+1;

    foreach($items as $it){
        $desc=$it['description'];
        $amt=$it['amount'];

        $result=detectCategory($desc,$category_map);
        $cat=$result['category'];
        $match_kw=$result['keyword'];

        $stmt=$con->prepare("INSERT INTO cost_data 
            (user_id,year,month,date,day_name,description,amount,match_keyword,category,serial,created_at) 
            VALUES (?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("iissssissis",
            $user_id,$year,$month,$date,$day_name,
            $desc,$amt,$match_kw,$cat,$serial,$created_at
        );
        if($stmt->execute()) $inserted++;
        $serial++;
    }
}

// =========================
// Session Message + Redirect
// =========================
$debug_link = "<a href='view_debug.php' target='_blank'>📜 Debug Log দেখুন</a>";

if($inserted>0){
    $_SESSION['success']="✅ {$inserted} টি এন্ট্রি যোগ হয়েছে! $debug_link";
}else{
    $_SESSION['danger']="❌ কোনো এন্ট্রি যোগ হয়নি! $debug_link";
}

header("Location: ../index.php?$redirect_query");
exit;
