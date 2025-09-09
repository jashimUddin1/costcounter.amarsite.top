<?php
session_start();

if (isset($_POST['save_setting_btn'])) {
    // ✅ Edit toggle
    $_SESSION['edit_enabled']  = isset($_POST['edit_enabled']);
    $_SESSION['edit_date']     = isset($_POST['edit_date']);
    $_SESSION['edit_balance']  = isset($_POST['edit_balance']);

    // ✅ Delete toggle
    $_SESSION['delete_enabled'] = isset($_POST['delete_enabled']);
    $_SESSION['delete_day']     = isset($_POST['delete_day']);

    // ✅ Entry mode: single / multiple
    $entry_mode = $_POST['entry_mode'] ?? 'single';
    $_SESSION['multi_entry_enabled'] = ($entry_mode === 'multiple');

    if ($_SESSION['multi_entry_enabled']) {
        $_SESSION['entry_type_select'] = $_POST['entry_type_select'] ?? [];
    } else {
        unset($_SESSION['entry_type_select']);
    }

    // ✅ Category toggle
    $_SESSION['category_enabled'] = isset($_POST['category_enabled']);
    $_SESSION['category_edit']    = isset($_POST['category_edit']);
    $_SESSION['category_delete']  = isset($_POST['category_delete']);

    // ✅ Display toggle
    $_SESSION['enabled_displayed'] = isset($_POST['enabled_displayed']);

    // ✅ Success message
    $_SESSION['status'] = "Settings saved successfully ✅";

    // ✅ Redirect back to index.php (or referer if needed)
    $redirect_url = $_SERVER['HTTP_REFERER'] ?? '../index.php';
    header("Location: $redirect_url");
    exit;

} else {
    // ❌ Invalid / direct access → back to previous page with JS
     $_SESSION['danger'] = "Invalid access. unauthorized. will be reported. next time ip will be blocked or punished.";

    echo "<script>
        alert('Unauthorized access detected 🚫, Invalid access.  will be reported. next time ip will be blocked or punished.');
        window.history.back();
    </script>";
    exit;
}
?>