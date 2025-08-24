<?php //settings_handler.php
session_start();

// ✅ Edit toggle
$_SESSION['edit_enabled'] = isset($_POST['edit_enabled']);
$_SESSION['edit_date'] = isset($_POST['edit_date']);
$_SESSION['edit_balance'] = isset($_POST['edit_balance']);

// ✅ Delete toggle
$_SESSION['delete_enabled'] = isset($_POST['delete_enabled']);
$_SESSION['delete_day'] = isset($_POST['delete_day']);

// ✅ Entry mode: single / multiple
$entry_mode = $_POST['entry_mode'] ?? 'single';
$_SESSION['multi_entry_enabled'] = ($entry_mode === 'multiple');

// ✅ If multiple mode selected, check for entry type selection
if ($_SESSION['multi_entry_enabled']) {
    $_SESSION['entry_type_select'] = $_POST['entry_type_select'] ?? [];
} else {
    unset($_SESSION['entry_type_select']);
}

// ✅ Category toggle
$_SESSION['category_enabled'] = isset($_POST['category_enabled']);
$_SESSION['category_edit'] = isset($_POST['category_edit']);
$_SESSION['category_delete'] = isset($_POST['category_delete']);

//display setting 
$_SESSION['enabled_displayed'] = isset($_POST['enabled_displayed']);


// ✅ Redirect back to referring page
$redirect_url = $_SERVER['HTTP_REFERER'] ?? '../index.php';
header("Location: $redirect_url");
exit;
