<?php // --- Add multiple categories ---
if ($action === 'add_category_multi') {
    $raw = trim($_POST['multi_categories'] ?? '');
    if ($raw === '') {
        $_SESSION['warning'] = "কোনো ডেটা দেওয়া হয় নাই";
        header("Location: manage_categories.php");
        exit();
    }

    $lines = preg_split("/\r\n|\n|\r/", $raw);
    $messages = [];

    foreach ($lines as $line) {
        if (trim($line) === '') continue;

        // Format: Category | Subcategory | Keywords
        $parts = array_map('trim', explode('|', $line));
        $name = $parts[0] ?? '';
        $sub_category = $parts[1] ?? '';
        $keywords = $parts[2] ?? '';

        if ($name === '') continue;

        // Keywords sanitize
        $kw_array = array_map('trim', explode(',', $keywords));
        $kw_array = array_values(array_unique(array_filter($kw_array)));
        $keywords_clean = implode(', ', $kw_array);

        // --- Check if category exists ---
        $stmt = $con->prepare("SELECT id, serial_no, category_keywords FROM categories WHERE user_id = ? AND category_name = ? AND sub_category IS NULL LIMIT 1");
        $stmt->bind_param("is", $user_id, $name);
        $stmt->execute();
        $cat_res = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$cat_res) {
            // New Category → assign next serial_no
            $stmt = $con->prepare("SELECT MAX(serial_no) as max_serial FROM categories WHERE user_id = ? AND sub_category IS NULL");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            $new_serial = ($row && $row['max_serial']) ? intval($row['max_serial']) + 1 : 1;

            $stmt = $con->prepare("INSERT INTO categories (user_id, category_name, serial_no, category_keywords, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->bind_param("isis", $user_id, $name, $new_serial, $keywords_clean);
            $stmt->execute();
            $stmt->close();

            $messages[] = "নতুন ক্যাটাগরি <strong>$name</strong> (Serial No: $new_serial) যোগ হয়েছে।";
        }

        if ($sub_category !== '') {
            // Subcategory check
            $stmt = $con->prepare("SELECT id, subcategory_serial, category_keywords FROM categories WHERE user_id = ? AND category_name = ? AND sub_category = ? LIMIT 1");
            $stmt->bind_param("iss", $user_id, $name, $sub_category);
            $stmt->execute();
            $sub_res = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$sub_res) {
                // নতুন subcategory → assign next subcategory_serial
                $stmt = $con->prepare("SELECT MAX(subcategory_serial) as max_sub FROM categories WHERE user_id = ? AND category_name = ?");
                $stmt->bind_param("is", $user_id, $name);
                $stmt->execute();
                $row = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                $new_sub_serial = ($row && $row['max_sub']) ? intval($row['max_sub']) + 1 : 1;

                $stmt = $con->prepare("INSERT INTO categories (user_id, category_name, sub_category, subcategory_serial, category_keywords, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                $stmt->bind_param("issis", $user_id, $name, $sub_category, $new_sub_serial, $keywords_clean);
                $stmt->execute();
                $stmt->close();

                $messages[] = "ক্যাটাগরি <strong>$name</strong> এর নতুন সাব-ক্যাটাগরি <strong>$sub_category</strong> (Sub Serial No: $new_sub_serial) যোগ হয়েছে।";
            } else {
                // আগের subcategory → শুধু keywords update
                $existing_keywords = array_map('trim', explode(',', $sub_res['category_keywords']));
                $existing_keywords = array_values(array_unique(array_filter($existing_keywords)));

                $added = array_diff($kw_array, $existing_keywords);
                $ignored = array_intersect($kw_array, $existing_keywords);

                $final_keywords = implode(', ', array_unique(array_merge($existing_keywords, $kw_array)));

                $stmt = $con->prepare("UPDATE categories SET category_keywords = ?, updated_at = NOW() WHERE id = ? AND user_id = ?");
                $stmt->bind_param("sii", $final_keywords, $sub_res['id'], $user_id);
                $stmt->execute();
                $stmt->close();

                if ($added) {
                    $messages[] = "ক্যাটাগরি <strong>$name</strong> এর সাব-ক্যাটাগরি <strong>$sub_category</strong> এ নতুন কীওয়ার্ড যোগ হয়েছে: " . implode(', ', $added);
                }
                if ($ignored && !$added) {
                    $messages[] = "এই ".implode(', ', $ignored)." keyword(s) আগে থেকেই ছিলো।";
                }
            }
        }
    }

    $_SESSION['success'] = implode("<br>", $messages);
    header("Location: manage_categories.php");
    exit();
}
?>


case 1: notun category + notun sub_category + new keyword
case 2: notun category + no sub_category + new keyword
case 3: existing category + notun sub_category +  new keyword
case 4: existing category + existing sub_category + new keyword (just update keyword)
case 5: existing category + existing sub_category + keyword (invalid case, should not happen)

ai case onujaye amake Add single category er code ta likhte daw . please 
 mani first a sob notun add hobe 



akhon amara multi entry niye kaj korbo 
 format hobe

ক্যাটাগরি => সাব-ক্যাটাগরি => কীওয়ার্ড, কীওয়ার্ড, কীওয়ার্ড
ক্যাটাগরি => সাব-ক্যাটাগরি -> কীওয়ার্ড, কীওয়ার্ড, কীওয়ার্ড
ক্যাটাগরি -> কীওয়ার্ড, কীওয়ার্ড, কীওয়ার্ড
ক্যাটাগরি => কীওয়ার্ড, কীওয়ার্ড, কীওয়ার্ড

onk gula category ek sathe add kora jabe 

case 1: notun category + notun sub_category + new keyword
case 2: notun category + no sub_category + new keyword
case 3: existing category + notun sub_category +  new keyword
case 4: existing category + existing sub_category + new keyword (just update keyword)
case 5: existing category + existing sub_category + keyword (invalid case, should not happen) 
ai gula akhane handle korte hobe



<?php
// category_core.php #Edit category
//old code 
 if ($removed || $added) {
                    $msg = "<strong>{$category}</strong> ক্যাটাগরি এর category_keywords ";
                    if ($removed) {
                        $msg .= " থেকে <span style='color:red'>" . implode(', ', $removed) . "</span> বাদ দেওয়া হয়েছে ";
                    }
                    if ($added) {
                        if ($removed) $msg .= " এবং ";
                        $msg .= "<span style='color:green'>" . implode(', ', $added) . "</span> যোগ হয়েছে";
                    }
                    $changes[] = $msg;
                }
//ami chai
 if ($removed || $added) {
                    $msg = "<strong>{$category}</strong> ক্যাটাগরি এর <strong>{$sub_category}</strong> সাব-ক্যাটাগরি";
                    if ($removed) {
                        $msg .= " থেকে <span style='color:red'>" . implode(', ', $removed) . "</span> keywords বাদ দেওয়া হয়েছে ";
                    }
                    if ($added) {
                        if ($removed) $msg .= " এবং ";
                        $msg .= "<span style='color:green'>" . implode(', ', $added) . "</span> যোগ হয়েছে";
                    }
                    $changes[] = $msg;
                }