<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cat_option_save_btn'])) {
    $_SESSION['category_edit']   = isset($_POST['category_edit']);
    $_SESSION['category_delete'] = isset($_POST['category_delete']);

    $_SESSION['success'] = "Settings Updated Successfully ✅";
    header("Location: manage_categories.php");
    exit;
} else {
    $_SESSION['danger'] = "Settings Updated Faild!";
    header("Location: manage_categories.php");
    exit;
}
