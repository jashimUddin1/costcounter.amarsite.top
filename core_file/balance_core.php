<?php //core_file/balance_core.php 
session_start();
include "../db/dbcon.php";

if (!isset($_SESSION['auth_user'])) {
    header("Location: ../login/index.php");
    exit();
}

$user_id = $_SESSION['auth_user']['id'];

// ------------------ Balance Update ------------------
if (isset($_POST['balance_bd_btn'])) {
    $id = intval($_POST['id']);
    $amount = intval($_POST['balance_bd']);
    $balance_type = $_POST['balance_type'];

    if ($id > 0 && $amount >= 0) {

        // 1️⃣ আগের ভ্যালু বের করা
        $prev_sql = "SELECT amount FROM balancesheet WHERE id=? AND user_id=?";
        $prev_stmt = $con->prepare($prev_sql);
        $prev_stmt->bind_param("ii", $id, $user_id);
        $prev_stmt->execute();
        $prev_result = $prev_stmt->get_result();

        if ($prev_result && $prev_result->num_rows > 0) {
            $prev_row = $prev_result->fetch_assoc();
            $previous_value = $prev_row['amount'];
        } else {
            $previous_value = null; // কিছু পাওয়া যায়নি
        }

        // 2️⃣ নতুন ভ্যালু আপডেট করা
        $sql = "UPDATE balancesheet 
                SET balance_type=?, amount=?, updated_at=NOW() 
                WHERE id=? AND user_id=?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("siii", $balance_type, $amount, $id, $user_id);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {

                // 3️⃣ লগ টেবিলে ইনসার্ট করা (যদি previous value থাকে)
                if ($previous_value !== null) {
                    $update_type = "balance_bd";
                    $date = date("Y-m-d");

                    $log_sql = "INSERT INTO update_logs (user_id, date, update_type, previous_value, updated_value) 
                                VALUES (?, ?, ?, ?, ?)";
                    $log_stmt = $con->prepare($log_sql);
                    $log_stmt->bind_param("issdd", $user_id, $date, $update_type, $previous_value, $amount);
                    $log_stmt->execute();
                }

                $_SESSION['success'] = "✅ Balance সফলভাবে আপডেট হয়েছে!";
            } else {
                $_SESSION['warning'] = "⚠️ Update query রান হয়েছে, কিন্তু কোনো row পরিবর্তন হয়নি (id ভুল হতে পারে)";
            }
        } else {
            $_SESSION['danger'] = "❌ Balance আপডেট করতে সমস্যা হয়েছে! Error: " . $stmt->error;
        }

    } else {
        $_SESSION['warning'] = "⚠️ ভুল তথ্য দেওয়া হয়েছে!";
    }

    header("Location: ../index.php");
    exit();
}


// ------------------ Set Balance ------------------
elseif (isset($_POST['set_balance_btn'])) {
    $year = $_POST['year'];
    $month = $_POST['month'];
    $amount = intval($_POST['amount']);

    // মাসের ১ তারিখ ধরা হচ্ছে
    $date = "$year-$month-01";

    $query = "INSERT INTO balancesheet (user_id, date, balance_type, amount, created_at) 
              VALUES ('$user_id', '$date', 'balance_bd', '$amount', NOW())";

    if (mysqli_query($con, $query)) {
        $_SESSION['success'] = "✅ Balance সফলভাবে সেট হয়েছে!";
    } else {
        $_SESSION['danger'] = "❌ Balance সেট করা যায়নি!";
    }

    header("Location: ../index.php");
    exit();
}

// ------------------ Invalid Request ------------------
else {
    $_SESSION['warning'] = "⚠️ Invalid Request! You are unauthorized people";
    header("Location: ../index.php");
    exit();
}
