<?php
session_start();

if (isset($_POST['save_setting_btn'])) {
    /* ======================
       ✏️ Edit Options
    ====================== */
    $_SESSION['edit_enabled']  = isset($_POST['edit_enabled']);
    $_SESSION['edit_date']     = isset($_POST['edit_date']);
    $_SESSION['edit_balance']  = isset($_POST['edit_balance']);

    /* ======================
       🗑️ Delete Options
    ====================== */
    $_SESSION['delete_enabled'] = isset($_POST['delete_enabled']);
    $_SESSION['delete_day']     = isset($_POST['delete_day']);

    /* ======================
       🧾 Entry Mode
       (single / manual / multiple / multi_entry_one_page)
    ====================== */
    $entry_mode = $_POST['entry_mode'] ?? 'single';
    $_SESSION['entry_mode'] = $entry_mode;

    // ✅ Mapping:
    // single → index_file/signle_date_multi_entry.php
    // manual → index_file/data_entry.php
    // multiple → index_file/multi_date_multi_entry.php
    // multi_entry_one_page → index_file/multi_entry_one_page.php

    /* ======================
       📂 Category Options
    ====================== */
    $_SESSION['category_enabled'] = isset($_POST['category_enabled']);
    $_SESSION['category_edit']    = isset($_POST['category_edit']);
    $_SESSION['category_delete']  = isset($_POST['category_delete']);

    /* ======================
       ⚙️ Display Options
    ====================== */
    $_SESSION['enabled_displayed'] = isset($_POST['enabled_displayed']);

    /* ======================
       ✅ Success Message
    ====================== */
    $_SESSION['status'] = "Settings saved successfully ✅";

    // Redirect back
    $redirect_url = $_SERVER['HTTP_REFERER'] ?? '../index.php';
    header("Location: $redirect_url");
    exit;

} else {
    $_SESSION['danger'] = "🚫 Invalid access detected.";
    echo "<script>alert('🚫 Unauthorized access!'); window.history.back();</script>";
    exit;
}
?>
