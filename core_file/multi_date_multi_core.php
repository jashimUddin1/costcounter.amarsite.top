<?php // core_file/merged_multi_core.php
session_start();
include("../db/dbcon.php");

$user_id = $_SESSION['auth_user']['id'] ?? null;
if(!$user_id){ header("Location: ../login/index.php"); exit(); }

if($_SERVER['REQUEST_METHOD']!=='POST'){ 
    header("Location: ../index.php"); exit(); 
}

$redirect_query = $_POST['redirect_query'] ?? '';

// get categories
$category_map = [];
$stmt = $con->prepare("SELECT category_name, category_keywords FROM categories WHERE user_id=?");
$stmt->bind_param("i",$user_id);
$stmt->execute();
$res = $stmt->get_result();
while($row=$res->fetch_assoc()){
    $category_map[$row['category_name']] = $row['category_keywords']!==''? array_map('trim',explode(',',$row['category_keywords'])):[];
}
$stmt->close();

// bn2en
function bn2en_number($s){
    $bn=['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
    $en=['0','1','2','3','4','5','6','7','8','9'];
    return str_replace($bn,$en,$s);
}

// detect category
function detectCategory($desc,$category_map){
    $desc_lower = mb_strtolower(trim($desc));
    $best_match = 'অন্যান্য';
    $best_length=0;
    foreach($category_map as $cat=>$keywords){
        foreach($keywords as $kw){
            $kw=mb_strtolower(trim($kw));
            if($kw=='' ) continue;
            if(mb_strpos($desc_lower,$kw)!==false && mb_strlen($kw)>$best_length){
                $best_match=$cat;
                $best_length=mb_strlen($kw);
            }
        }
    }
    return $best_match;
}

// process each date row
$inserted=0;
foreach($_POST['entries'] as $entry){
    $date = $entry['date'] ?? '';
    $bulk = $entry['bulk_description'] ?? '';
    if(!$date || !$bulk) continue;

    $year = date('Y',strtotime($date));
    $month = date('F',strtotime($date));
    $day_name = date('l',strtotime($date));
    $created_at = date('Y-m-d H:i:s');

    $entries = explode(',',$bulk);
    $serial_query = $con->prepare("SELECT MAX(serial) as max_serial FROM cost_data WHERE user_id=? AND date=?");
    $serial_query->bind_param("is",$user_id,$date);
    $serial_query->execute();
    $max_s = $serial_query->get_result()->fetch_assoc()['max_serial'] ?? 0;
    $serial_query->close();
    $serial = $max_s+1;

    foreach($entries as $e){
        $e = trim(bn2en_number($e));
        $e = preg_replace('/^\d+\.\s*/u','',$e);
        $e = str_ireplace([' টাকা','টাকা',' tk','tk'],'',$e);

        if(preg_match('/^(.+?)\s*([\d\+\.\s]+)$/u',$e,$m)){
            $desc = trim($m[1]);
            $amt_str = trim($m[2]);
            $parts = explode('+',$amt_str);
            $amt=0;
            foreach($parts as $p) $amt+=floatval(trim($p));

            $cat = detectCategory($desc,$category_map);

            $stmt = $con->prepare("INSERT INTO cost_data (user_id,year,month,date,day_name,description,amount,category,serial,created_at)
                VALUES (?,?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param("iissssdsis",$user_id,$year,$month,$date,$day_name,$desc,$amt,$cat,$serial,$created_at);
            if($stmt->execute()) $inserted++;
            $serial++;
        }
    }
}

if($inserted>0) $_SESSION['success']="✅ {$inserted}টি এন্ট্রি যোগ হয়েছে!";
else $_SESSION['danger']="❌ কোনো এন্ট্রি যোগ হয়নি!";
header("Location: ../index.php?$redirect_query");
exit();
