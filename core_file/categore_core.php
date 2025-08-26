<?php //categore_core.php
session_start();
include("../db/dbcon.php");

if (!isset($_SESSION['authenticated'])) {
    header("Location: ../login/index.php");
    exit();
}

$user_id = $_SESSION['auth_user']['id'] ?? null;

// English → Bangla
function en2bn($number)
{
    $en = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    $bn = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
    return str_replace($en, $bn, $number);
}

// Bangla → English
function bn2en($number)
{
    $en = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    $bn = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
    return str_replace($bn, $en, $number);
}

// --- Fetch categories ---
$categories = [];
$stmt = $con->prepare("SELECT id, category_name, category_keywords FROM categories WHERE user_id = ? ORDER BY id");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $categories[$row['category_name']] = $row;
}
$stmt->close();

// --- Fetch category groups ---
$category_groups = [];
$stmt = $con->prepare("SELECT * FROM category_groups WHERE user_id = ? ");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $category_groups[] = $row;
}
$stmt->close();

// --- Handle POST actions ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // --- Add single category ---
    if ($action === 'add_category_single') {
        $name = trim($_POST['category_name'] ?? '');
        $keywords = trim($_POST['category_keywords'] ?? '');

        if ($name !== '') {
            if (isset($categories[$name])) {
                $existing_keywords = $categories[$name]['category_keywords'] ?? '';
                $all_keywords = array_unique(array_filter(array_map('trim', array_merge(
                    explode(',', $existing_keywords),
                    explode(',', $keywords)
                ))));
                $keywords_final = implode(', ', $all_keywords);

                $stmt = $con->prepare("UPDATE categories SET category_keywords = ?, updated_at = NOW() WHERE id = ? AND user_id = ?");
                $stmt->bind_param("sii", $keywords_final, $categories[$name]['id'], $user_id);
                $stmt->execute();
                $stmt->close();

                $_SESSION['success'] = "ক্যাটাগরি <strong>$name</strong> এর কীওয়ার্ড আপডেট হয়েছে।";
            } else {
                $stmt = $con->prepare("INSERT INTO categories (user_id, category_name, category_keywords, created_at) VALUES (?, ?, ?, NOW())");
                $stmt->bind_param("iss", $user_id, $name, $keywords);
                $stmt->execute();
                $stmt->close();

                $_SESSION['success'] = "নতুন ক্যাটাগরি <strong>$name</strong> যোগ হয়েছে।";
            }
        } else {
            $_SESSION['warning'] = "ক্যাটাগরির নাম দেওয়া হয় নাই";
        }
        header("Location: manage_categories.php");
        exit();
    }

    // --- Add multiple categories ---
    if ($action === 'add_category_multi') {
        $input = trim($_POST['category_input'] ?? '');
        if ($input !== '') {
            $added = 0;
            $updated = 0;
            $lines = preg_split('/\r\n|\r|\n/', $input);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '')
                    continue;

                if (strpos($line, '=>') !== false) {
                    [$cat, $kw] = explode('=>', $line, 2);
                    $cat = trim($cat);
                    $kw = rtrim(trim($kw), ',');
                    $kw_arr = array_filter(array_map('trim', explode(',', $kw)));
                    $kw_final = implode(', ', $kw_arr);

                    if ($cat === '')
                        continue;

                    if (isset($categories[$cat])) {
                        $existing_keywords = $categories[$cat]['category_keywords'] ?? '';
                        $merged = array_unique(array_filter(array_map('trim', array_merge(
                            explode(',', $existing_keywords),
                            $kw_arr
                        ))));
                        $keywords_final = implode(', ', $merged);

                        $stmt = $con->prepare("UPDATE categories SET category_keywords = ?, updated_at = NOW() WHERE id = ? AND user_id = ?");
                        $stmt->bind_param("sii", $keywords_final, $categories[$cat]['id'], $user_id);
                        $stmt->execute();
                        $stmt->close();
                        $updated++;
                    } else {
                        $stmt = $con->prepare("INSERT INTO categories (user_id, category_name, category_keywords, created_at) VALUES (?, ?, ?, NOW())");
                        $stmt->bind_param("iss", $user_id, $cat, $kw_final);
                        $stmt->execute();
                        $stmt->close();
                        $added++;
                    }
                }
            }
            $_SESSION['success'] = "মোট $added টি নতুন ক্যাটাগরি যোগ হয়েছে এবং $updated টি আপডেট হয়েছে।";
        } else {
            $_SESSION['warning'] = "মাল্টি-এন্ট্রির জন্য ইনপুট খালি দেওয়া হয়েছে।";
        }
        header("Location: manage_categories.php");
        exit();
    }

    // --- Edit category ---
    if ($action === 'edit_category') {
        $id = intval($_POST['id'] ?? 0);
        $new_id = intval(bn2en($_POST['category_id'] ?? 0));
        $name = trim($_POST['category_name'] ?? '');
        $keywords = trim($_POST['category_keywords'] ?? '');

        if ($id && $new_id && $name !== '') {
            $stmt = $con->prepare("SELECT COUNT(*) as cnt FROM categories WHERE id = ? AND id != ? AND user_id = ?");
            $stmt->bind_param("iii", $new_id, $id, $user_id);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($res['cnt'] > 0) {
                $_SESSION['danger'] = "Error: ID <strong>$new_id</strong> আগে থেকেই ব্যবহার হচ্ছে।";
            } else {
                $stmt = $con->prepare("SELECT * FROM categories WHERE id = ? AND user_id = ?");
                $stmt->bind_param("ii", $id, $user_id);
                $stmt->execute();
                $old = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if ($old) {
                    $stmt = $con->prepare("UPDATE categories SET id = ?, category_name = ?, category_keywords = ?, updated_at = NOW() WHERE id = ? AND user_id = ?");
                    $stmt->bind_param("issii", $new_id, $name, $keywords, $id, $user_id);
                    $stmt->execute();
                    $stmt->close();

                    $changes = [];
                    if ($old['id'] != $new_id)
                        $changes[] = "ক্রমিক নং: " . en2bn($old['id']) . " → " . en2bn($new_id);
                    if ($old['category_name'] != $name)
                        $changes[] = "Name: {$old['category_name']} → {$name}";
                    if ($old['category_keywords'] != $keywords)
                        $changes[] = "Keywords: {$old['category_keywords']} → {$keywords}";

                    $_SESSION['success'] = $changes ? "ক্যাটাগরি আপডেট হয়েছে: <br>" . implode('<br>', $changes) : "কোনো পরিবর্তন করা হয়নি।";
                } else {
                    $_SESSION['danger'] = "ক্যাটাগরি পাওয়া যায়নি।";
                }
            }
        } else {
            $_SESSION['danger'] = "ক্যাটাগরি আপডেট করা সম্ভব হয়নি।";
        }
        header("Location: manage_categories.php");
        exit();
    }

    // --- Delete category ---
    if ($action === 'delete_category') {
        $id = intval($_POST['id'] ?? 0);
        $category_name = $_POST['cat_name'];
        if ($id) {
            $stmt = $con->prepare("DELETE FROM categories WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $id, $user_id);
            $stmt->execute();
            $stmt->close();
            $_SESSION['success'] = "ক্যাটাগরি <strong>$category_name</strong> মুছে ফেলা হয়েছে।";
        } else {
            $_SESSION['danger'] = "ক্যাটাগরি <strong>$category_name</strong> মুছতে সমস্যা হয়েছে।";
        }
        header("Location: manage_categories.php");
        exit();
    }



    // --- Add / Edit / Delete Category Groups ---
    if ($action === 'add_group') {
        $group_name = trim($_POST['group_name'] ?? '');
        $group_category = trim($_POST['group_category'] ?? '');
        if ($group_name !== '') {
            $stmt = $con->prepare("SELECT id FROM category_groups WHERE user_id=? AND group_name=?");
            $stmt->bind_param("is", $user_id, $group_name);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $_SESSION['danger'] = "গ্রুপ <strong>{$group_name}</strong> আগে থেকেই আছে।";
            } else {
                $stmt->close();
                $stmt = $con->prepare("INSERT INTO category_groups (user_id, group_name, group_category, created_at) VALUES (?, ?, ?, NOW())");
                $stmt->bind_param("iss", $user_id, $group_name, $group_category);
                $stmt->execute();
                $_SESSION['success'] = "ক্যাটাগরি গ্রুপ <strong>{$group_name}</strong> যোগ করা হয়েছে।";
            }
            $stmt->close();
        } else {
            $_SESSION['danger'] = "গ্রুপের নাম অবশ্যই দিতে হবে।";
        }
        header("Location: manage_categories.php");
        exit();
    }

    if ($action === 'edit_group') {
        $old_id = intval(bn2en($_POST['old_id'] ?? 0));
        $new_id = intval(bn2en($_POST['new_id'] ?? 0));
        $group_name = trim($_POST['group_name'] ?? '');
        $group_category = trim($_POST['group_category'] ?? '');

        if ($old_id && $new_id && $group_name !== '') {
            // আগের ডেটা আনি
            $stmt = $con->prepare("SELECT id, group_name, group_category FROM category_groups WHERE id=? AND user_id=?");
            $stmt->bind_param("ii", $old_id, $user_id);
            $stmt->execute();
            $old_data = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            // আপডেট করি
            $stmt = $con->prepare("UPDATE category_groups 
                SET id=?, group_name=?, group_category=?, updated_at=NOW() 
                WHERE id=? AND user_id=?");
            $stmt->bind_param("issii", $new_id, $group_name, $group_category, $old_id, $user_id);
            $stmt->execute();
            $stmt->close();


            // --- পরিবর্তন ট্র্যাক ---
            $added_cats = [];
            $removed_cats = [];

            $old_cats = array_filter(array_map('trim', explode(',', $old_data['group_category'])));
            $new_cats = array_filter(array_map('trim', explode(',', $group_category)));

            $added_cats = array_diff($new_cats, $old_cats);
            $removed_cats = array_diff($old_cats, $new_cats);

            // --- Session message তৈরি ---
            $group_name_html = "<strong>{$old_data['group_name']}</strong>";
            $added_html = !empty($added_cats) ? "<strong>" . implode(', ', $added_cats) . "</strong>" : '';
            $removed_html = !empty($removed_cats) ? "<strong>" . implode(', ', $removed_cats) . "</strong>" : '';

            if (!empty($added_cats) && !empty($removed_cats)) {
                $_SESSION['success'] = "{$group_name_html} গ্রুপে {$added_html} যোগ করা হয়েছে এবং {$removed_html} বাদ দেওয়া হয়েছে";
            } elseif (!empty($added_cats)) {
                $_SESSION['success'] = "{$group_name_html} গ্রুপে {$added_html} যোগ করা হয়েছে";
            } elseif (!empty($removed_cats)) {
                $_SESSION['success'] = "{$group_name_html} গ্রুপ থেকে {$removed_html} বাদ দেওয়া হয়েছে";
            } else {
                $_SESSION['success'] = "{$group_name_html} গ্রুপে কোনো পরিবর্তন হয়নি";
            }


        } else {
            $_SESSION['danger'] = "গ্রুপ আপডেট করা সম্ভব হয়নি ❌";
        }

        header("Location: manage_categories.php");
        exit();
    }

    if ($action === 'delete_group') {
        $id = intval($_POST['id'] ?? 0);
        if ($id) {
            $stmt = $con->prepare("DELETE FROM category_groups WHERE id=? AND user_id=?");
            $stmt->bind_param("ii", $id, $user_id);
            $stmt->execute();
            $stmt->close();
            $_SESSION['warning'] = "গ্রুপ ডিলিট করা হয়েছে।";
        }
        header("Location: manage_categories.php");
        exit();
    }
}
?>