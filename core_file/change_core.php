<?php  //change_core.php
session_start();
include("../db/dbcon.php");

if (!isset($_SESSION['authenticated'])) {
    header("Location: ../login/index.php");
    exit();
}

$user_id = $_SESSION['auth_user']['id'] ?? null;
if (isset($_POST['assign_category'])) {
    $group_id = $_POST['group_id'] ?? null;
    $selected_cats = $_POST['category_names'] ?? [];

    if ($group_id && !empty($selected_cats)) {
        // আগের গ্রুপের ডেটা নিয়ে আসি
        $stmt = $con->prepare("SELECT group_name, group_category FROM category_groups WHERE id = ?");
        $stmt->bind_param("i", $group_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        $group_name = $result['group_name'] ?? '';

        $existing_cats = !empty($result['group_category'])
            ? array_map('trim', explode(',', $result['group_category']))
            : [];

        // নতুন ক্যাটাগরি যোগ করা
        $updated_cats = array_unique(array_merge($existing_cats, $selected_cats));
        $updated_str = implode(',', $updated_cats);

        // Update query
        $stmt = $con->prepare("UPDATE category_groups SET group_category = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("si", $updated_str, $group_id);
        $stmt->execute();
        $stmt->close();

        $cats_text = $selected_cats;
        $last = array_pop($cats_text);
        $cats_str = $cats_text ? implode(", ", array_map('htmlspecialchars', $cats_text)) . " এবং " . htmlspecialchars($last) : htmlspecialchars($last);

        $_SESSION['success'] = "<strong>$cats_str</strong> ক্যাটাগরি সফলভাবে <strong>$group_name</strong> গ্রুপে যুক্ত হয়েছে ✅";


        // redirect করতে হবে
        header("Location: ../pages/manage_categories.php");
        exit();

    }
}


