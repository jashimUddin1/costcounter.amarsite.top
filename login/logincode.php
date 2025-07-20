<!-- logincode.php -->
<?php
session_start();
require '../db/dbcon.php';

if (isset($_SESSION['authenticated'])) {
    header("Location: ../index.php");
    exit(0);
}

if (isset($_POST['login_now_btn'])) {
    if (!empty(trim($_POST['email'])) && !empty(trim($_POST['password']))) {
        $email = mysqli_real_escape_string($con, $_POST['email']);
        $password = mysqli_real_escape_string($con, $_POST['password']);

        $check_user_query = "SELECT * FROM `users` WHERE `email`='$email' LIMIT 1";
        $check_user_query_run = mysqli_query($con, $check_user_query);

        if (mysqli_num_rows($check_user_query_run) > 0) {
            $user_data = mysqli_fetch_array($check_user_query_run);

            if ($user_data["verify_status"] == "1") { // ✅ Email Verify চেক ✅
                if (password_verify($password, $user_data['password'])) {
                    $_SESSION['authenticated'] = true;
                    $_SESSION['auth_user'] = [
                        'id' => $user_data['id'],
                        'fname' => $user_data['first_name'],
                        'lname' => $user_data['last_name'],
                        'email' => $user_data['email'],
                        'phone' => $user_data['phone'],
                        'role' => $user_data['role']
                    ];


                    $_SESSION['success'] = "Login successful!";
                    header("Location: ../index.php");
                    exit(0);
                } else {
                    $_SESSION['danger'] = "Invalid Password!";
                    header("Location: index.php");
                    exit(0);
                }
            } else {
                $_SESSION['warning'] = "Please verify your email address to login!";
                header("Location: index.php");
                exit(0);
            }
        } else {
            $_SESSION['warning'] = "No Account Found with this Email!. Please <a href='register.php'>create</a> Acount";
            header("Location: index.php");
            exit(0);
        }
    }
}
?>
