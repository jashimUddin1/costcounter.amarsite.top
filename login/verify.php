<?php
session_start();
require '../db/dbcon.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    $check_token_query = "SELECT id FROM `users` WHERE verify_token='$token' LIMIT 1";
    $check_token_query_run = mysqli_query($con, $check_token_query);

    if (mysqli_num_rows($check_token_query_run) > 0) {
        $update_query = "UPDATE `users` SET verify_token=NULL, verify_status=1, updated_at=NOW() WHERE verify_token='$token' LIMIT 1";
        $update_query_run = mysqli_query($con, $update_query);

        if ($update_query_run) {
            $_SESSION['status'] = "Email verified successfully. You can now log in.";
            header("Location: index.php");
            exit(0);
        } else {
            $_SESSION['status'] = "Verification failed. Please try again.";
            header("Location: register.php");
            exit(0);
        }
    } else {
        $_SESSION['status'] = "Invalid or expired token.";
        header("Location: register.php");
        exit(0);
    }
} else {
    $_SESSION['status'] = "No token provided.";
    header("Location: register.php");
    exit(0);
}
?>
