<?php //categore_core.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
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
$stmt = $con->prepare("SELECT * FROM categories WHERE user_id = ? ORDER BY serial_no, subcategory_serial ASC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $cat = $row['category_name'];
    $categories[$cat][] = $row;
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
        $sub_category = trim($_POST['subcategory_name'] ?? 'none');
        $keywords = trim($_POST['category_keywords'] ?? '');

        if ($name !== '' && $keywords !== '') {
            // ✅ Keywords sanitize
            $keywords_array = array_map('trim', explode(',', $keywords));
            $keywords_array = array_filter($keywords_array);
            $keywords_array = array_unique($keywords_array);
            $keywords_clean = implode(', ', $keywords_array);

            // =====================
            // ক্যাটাগরি আগে থেকে আছে কিনা চেক করি
            // =====================
            $stmt = $con->prepare("SELECT * FROM categories WHERE user_id = ? AND category_name = ? LIMIT 1");
            $stmt->bind_param("is", $user_id, $name);
            $stmt->execute();
            $exist_cat = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            // =====================
            // CASE 1 & CASE 2: নতুন Category
            // =====================
            if (!$exist_cat) {
                $stmt = $con->prepare("SELECT MAX(serial_no) as max_serial FROM categories WHERE user_id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $res = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                $new_serial = ($res && $res['max_serial']) ? intval($res['max_serial']) + 1 : 1;

                if ($sub_category !== '' && $sub_category !== 'none') {
                    // -------------------
                    // CASE 1: নতুন category + নতুন subcategory + keyword
                    // -------------------
                    $new_sub_serial = 1;
                    $stmt = $con->prepare("INSERT INTO categories 
                        (user_id, category_name, serial_no, sub_category, subcategory_serial, category_keywords, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, NOW())");
                    $stmt->bind_param("isisss", $user_id, $name, $new_serial, $sub_category, $new_sub_serial, $keywords_clean);
                    $stmt->execute();
                    $stmt->close();

                    $_SESSION['success'] = "✅ নতুন ক্যাটাগরি <strong>$name</strong> (Serial: $new_serial), নতুন সাব-ক্যাটাগরি <strong>$sub_category</strong> (Sub Serial: 1) এবং keywords যোগ হয়েছে।";

                } else {
                    // -------------------
                    // CASE 2: নতুন category + no subcategory + keyword
                    // -------------------
                    $sub_category = "none";
                    $new_sub_serial = 0;
                    $stmt = $con->prepare("INSERT INTO categories 
                        (user_id, category_name, serial_no, sub_category, subcategory_serial, category_keywords, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, NOW())");
                    $stmt->bind_param("isisss", $user_id, $name, $new_serial, $sub_category, $new_sub_serial, $keywords_clean);
                    $stmt->execute();
                    $stmt->close();

                    $_SESSION['success'] = "✅ নতুন ক্যাটাগরি <strong>$name</strong> (Serial: $new_serial) keywords সহ যোগ হয়েছে।";
                }

            } else {
                // =====================
                // CASE 3, 4, 5: বিদ্যমান Category
                // =====================
                if ($sub_category !== '' && $sub_category !== 'none') {
                    // Subcategory check
                    $stmt = $con->prepare("SELECT * FROM categories WHERE user_id = ? AND category_name = ? AND sub_category = ? LIMIT 1");
                    $stmt->bind_param("iss", $user_id, $name, $sub_category);
                    $stmt->execute();
                    $exist_sub = $stmt->get_result()->fetch_assoc();
                    $stmt->close();

                    if (!$exist_sub) {
                        // -------------------
                        // CASE 3: existing category + নতুন subcategory + keyword
                        // -------------------
                        $stmt = $con->prepare("SELECT MAX(subcategory_serial) as max_serial 
                            FROM categories WHERE user_id = ? AND category_name = ?");
                        $stmt->bind_param("is", $user_id, $name);
                        $stmt->execute();
                        $res = $stmt->get_result()->fetch_assoc();
                        $stmt->close();

                        $new_sub_serial = ($res && $res['max_serial']) ? intval($res['max_serial']) + 1 : 1;
                        $serial_no = $exist_cat['serial_no'];

                        $stmt = $con->prepare("INSERT INTO categories 
                            (user_id, category_name, serial_no, sub_category, subcategory_serial, category_keywords, created_at) 
                            VALUES (?, ?, ?, ?, ?, ?, NOW())");
                        $stmt->bind_param("isisss", $user_id, $name, $serial_no, $sub_category, $new_sub_serial, $keywords_clean);
                        $stmt->execute();
                        $stmt->close();

                        $_SESSION['success'] = "✅ ক্যাটাগরি <strong>$name</strong> এ নতুন সাব-ক্যাটাগরি <strong>$sub_category</strong> (Sub Serial: $new_sub_serial) এবং keywords যোগ হয়েছে।";

                    } else {
                        // -------------------
                        // CASE 4: existing category + existing subcategory + নতুন keyword update
                        // -------------------
                        $old_kw = array_map('trim', explode(',', $exist_sub['category_keywords']));
                        $old_kw = array_values(array_unique(array_filter($old_kw)));

                        $new_kw = array_diff($keywords_array, $old_kw); // শুধু নতুন keyword
                        $final_kw = array_merge($old_kw, $new_kw);
                        $keywords_clean = implode(', ', $final_kw);

                        $stmt = $con->prepare("UPDATE categories SET category_keywords = ?, updated_at = NOW() WHERE id = ?");
                        $stmt->bind_param("si", $keywords_clean, $exist_sub['id']);
                        $stmt->execute();
                        $stmt->close();

                        if ($new_kw) {
                            $_SESSION['success'] = "🔄 ক্যাটাগরি <strong>$name</strong> → সাব-ক্যাটাগরি <strong>{$exist_sub['sub_category']}</strong> এ নতুন keyword (<span style='color:green'>" . implode(', ', $new_kw) . "</span>) যোগ হয়েছে।";
                        } else {
                            // -------------------
                            // CASE 5: কোনো নতুন keyword নাই
                            // -------------------
                            $_SESSION['danger'] = "❌ কোনো নতুন keyword পাওয়া যায়নি।";
                        }
                    }
                } else {
                    $_SESSION['danger'] = "❌ ক্যাটাগরি <strong>$name</strong> আগেই আছে, আবার নতুন category হিসেবে যোগ করা যাবে না।";
                }
            }

        } else {
            $_SESSION['warning'] = "⚠️ ক্যাটাগরির নাম এবং কীওয়ার্ড দিতে হবে।";
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
            $skipped = 0;

            $lines = preg_split('/\r\n|\r|\n/', $input);

            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '')
                    continue;

                // ✅ Split into parts: category, subcategory, keywords
                $cat = $sub = $kw_str = '';
                if (preg_match('/^(.+?)\s*=>\s*(.+?)\s*=>\s*(.+)$/u', $line, $m)) {
                    // Format: ক্যাটাগরি => সাবক্যাটাগরি => কীওয়ার্ড
                    $cat = trim($m[1]);
                    $sub = trim($m[2]);
                    $kw_str = trim($m[3]);
                } elseif (preg_match('/^(.+?)\s*=>\s*(.+?)\s*->\s*(.+)$/u', $line, $m)) {
                    // Format: ক্যাটাগরি => সাবক্যাটাগরি -> কীওয়ার্ড
                    $cat = trim($m[1]);
                    $sub = trim($m[2]);
                    $kw_str = trim($m[3]);
                } elseif (preg_match('/^(.+?)\s*->\s*(.+)$/u', $line, $m)) {
                    // Format: ক্যাটাগরি -> কীওয়ার্ড
                    $cat = trim($m[1]);
                    $sub = 'none';
                    $kw_str = trim($m[2]);
                } elseif (preg_match('/^(.+?)\s*=>\s*(.+)$/u', $line, $m)) {
                    // Format: ক্যাটাগরি => কীওয়ার্ড
                    $cat = trim($m[1]);
                    $sub = 'none';
                    $kw_str = trim($m[2]);
                }

                if ($cat === '' || $kw_str === '')
                    continue;

                // ✅ Keywords sanitize
                $kw_arr = array_unique(array_filter(array_map('trim', explode(',', $kw_str))));
                $keywords_clean = implode(', ', $kw_arr);

                // =====================
                // Check existing category
                // =====================
                $stmt = $con->prepare("SELECT * FROM categories WHERE user_id = ? AND category_name = ? LIMIT 1");
                $stmt->bind_param("is", $user_id, $cat);
                $stmt->execute();
                $exist_cat = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if (!$exist_cat) {
                    // =====================
                    // CASE 1 & 2: নতুন Category
                    // =====================
                    $stmt = $con->prepare("SELECT MAX(serial_no) as max_serial FROM categories WHERE user_id = ?");
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $res = $stmt->get_result()->fetch_assoc();
                    $stmt->close();

                    $new_serial = ($res && $res['max_serial']) ? intval($res['max_serial']) + 1 : 1;

                    if ($sub !== '' && $sub !== 'none') {
                        // CASE 1: নতুন category + নতুন subcategory
                        $new_sub_serial = 1;
                        $stmt = $con->prepare("INSERT INTO categories 
                            (user_id, category_name, serial_no, sub_category, subcategory_serial, category_keywords, created_at) 
                            VALUES (?, ?, ?, ?, ?, ?, NOW())");
                        $stmt->bind_param("isisss", $user_id, $cat, $new_serial, $sub, $new_sub_serial, $keywords_clean);
                        $stmt->execute();
                        $stmt->close();
                        $added++;
                    } else {
                        // CASE 2: নতুন category + no subcategory
                        $sub = "none";
                        $new_sub_serial = 0;
                        $stmt = $con->prepare("INSERT INTO categories 
                            (user_id, category_name, serial_no, sub_category, subcategory_serial, category_keywords, created_at) 
                            VALUES (?, ?, ?, ?, ?, ?, NOW())");
                        $stmt->bind_param("isisss", $user_id, $cat, $new_serial, $sub, $new_sub_serial, $keywords_clean);
                        $stmt->execute();
                        $stmt->close();
                        $added++;
                    }

                } else {
                    // =====================
                    // CASE 3, 4, 5: Existing category
                    // =====================
                    $stmt = $con->prepare("SELECT * FROM categories WHERE user_id = ? AND category_name = ? AND sub_category = ? LIMIT 1");
                    $stmt->bind_param("iss", $user_id, $cat, $sub);
                    $stmt->execute();
                    $exist_sub = $stmt->get_result()->fetch_assoc();
                    $stmt->close();

                    if (!$exist_sub && $sub !== 'none') {
                        // CASE 3: existing category + নতুন subcategory
                        $stmt = $con->prepare("SELECT MAX(subcategory_serial) as max_serial FROM categories WHERE user_id = ? AND category_name = ?");
                        $stmt->bind_param("is", $user_id, $cat);
                        $stmt->execute();
                        $res = $stmt->get_result()->fetch_assoc();
                        $stmt->close();

                        $new_sub_serial = ($res && $res['max_serial']) ? intval($res['max_serial']) + 1 : 1;
                        $serial_no = $exist_cat['serial_no'];

                        $stmt = $con->prepare("INSERT INTO categories 
                            (user_id, category_name, serial_no, sub_category, subcategory_serial, category_keywords, created_at) 
                            VALUES (?, ?, ?, ?, ?, ?, NOW())");
                        $stmt->bind_param("isisss", $user_id, $cat, $serial_no, $sub, $new_sub_serial, $keywords_clean);
                        $stmt->execute();
                        $stmt->close();
                        $added++;

                    } elseif ($exist_sub) {
                        // CASE 4 & 5: existing category + existing subcategory
                        $old_kw = array_unique(array_filter(array_map('trim', explode(',', $exist_sub['category_keywords']))));
                        $new_kw = array_diff($kw_arr, $old_kw);

                        if ($new_kw) {
                            // CASE 4: নতুন keyword যোগ হবে
                            $final_kw = array_merge($old_kw, $new_kw);
                            $keywords_final = implode(', ', $final_kw);

                            $stmt = $con->prepare("UPDATE categories SET category_keywords = ?, updated_at = NOW() WHERE id = ?");
                            $stmt->bind_param("si", $keywords_final, $exist_sub['id']);
                            $stmt->execute();
                            $stmt->close();
                            $updated++;
                        } else {
                            // CASE 5: সব keyword আগে থেকেই আছে → skip
                            $skipped++;
                        }
                    }
                }
            }

            $_SESSION['success'] = "✅ নতুন: $added, 🔄 আপডেট: $updated, ⏭️ স্কিপড: $skipped";
        } else {
            $_SESSION['warning'] = "⚠️ মাল্টি-এন্ট্রির জন্য ইনপুট খালি দেওয়া হয়েছে।";
        }
        header("Location: manage_categories.php");
        exit();
    }



    // --- Edit category ---
    if ($action === 'edit_category') {
        $id = intval($_POST['id'] ?? 0);
        $serial_no = intval(bn2en($_POST['serial_no'] ?? 0));
        $category = trim($_POST['category'] ?? '');
        $subcategory_serial = intval($_POST['subcategory_serial'] ?? 0);
        $sub_category = trim($_POST['sub_category'] ?? '');
        if ($sub_category === '') {
            $sub_category = 'none';
        }
        $keywords = trim($_POST['category_keywords'] ?? '');

        if ($id && $category !== '') {
            // =====================
            // old data fetch
            // =====================
            $stmt = $con->prepare("SELECT * FROM categories WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $id, $user_id);
            $stmt->execute();
            $old = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($old) {
                // =====================
                // Serial_no duplicate check (only if changed)
                // =====================
                if ($old['serial_no'] != $serial_no) {
                    $stmt = $con->prepare("SELECT category_name 
                                        FROM categories 
                                        WHERE user_id = ? AND serial_no = ? AND id != ? LIMIT 1");
                    $stmt->bind_param("iii", $user_id, $serial_no, $id);
                    $stmt->execute();
                    $dup = $stmt->get_result()->fetch_assoc();
                    $stmt->close();

                    if ($dup) {
                        $duplicate_cat = $dup['category_name'];
                        $_SESSION['danger'] = "<strong>{$serial_no}</strong> no serial ইতিমধ্যেই <strong>{$duplicate_cat}</strong> ক্যাটাগরিতে ব্যবহার হয়েছে। দয়া করে অন্য serial দিন।";
                        header("Location: manage_categories.php");
                        exit();
                    }
                }

                // =====================
                // Subcategory_serial duplicate check (only if changed and not 'none')
                // =====================
                if ($sub_category !== 'none' && $old['subcategory_serial'] != $subcategory_serial) {
                    $stmt = $con->prepare("SELECT sub_category 
                                        FROM categories 
                                        WHERE user_id = ? AND category_name = ? AND subcategory_serial = ? AND id != ? LIMIT 1");
                    $stmt->bind_param("isii", $user_id, $category, $subcategory_serial, $id);
                    $stmt->execute();
                    $dup = $stmt->get_result()->fetch_assoc();
                    $stmt->close();

                    if ($dup) {
                        $duplicate_subcat = $dup['sub_category'];
                        $_SESSION['danger'] = "<strong>{$subcategory_serial}</strong> no subcategory_serial ইতিমধ্যেই <strong>{$duplicate_subcat}</strong> সাব-ক্যাটাগরিতে ব্যবহার হয়েছে। দয়া করে অন্য সংখ্যা দিন।";
                        header("Location: manage_categories.php");
                        exit();
                    }
                }

                // =====================
                // Keywords sanitize (ডুপ্লিকেট বাদ)
                // =====================
                $new_raw = array_map('trim', explode(',', $keywords));
                $new_raw = array_filter($new_raw);

                $new_kw = array_values(array_unique($new_raw));
                $keywords_clean = implode(', ', $new_kw);

                // =====================
                // UPDATE QUERY
                // =====================
                $stmt = $con->prepare("UPDATE categories 
                                    SET serial_no = ?, category_name = ?, subcategory_serial = ?, sub_category = ?, category_keywords = ?, updated_at = NOW() 
                                    WHERE id = ? AND user_id = ?");
                $stmt->bind_param("isissii", $serial_no, $category, $subcategory_serial, $sub_category, $keywords_clean, $id, $user_id);
                $stmt->execute();
                $stmt->close();

                // =====================
                // CHANGE TRACKING
                // =====================
                $changes = [];

                if ($old['category_name'] != $category) {
                    $changes[] = "Category name <strong>{$old['category_name']}</strong> → <strong>{$category}</strong>";
                }
                if ($old['serial_no'] != $serial_no) {
                    $changes[] = "<strong>{$category}</strong> ক্যাটাগরি te serial_no <span style='color:red'>{$old['serial_no']}</span> → <span style='color:green'>{$serial_no}</span>";
                }
                if ($old['subcategory_serial'] != $subcategory_serial) {
                    $changes[] = "subcategory_serial <span style='color:red'>{$old['subcategory_serial']}</span> → <span style='color:green'>{$subcategory_serial}</span>";
                }
                if ($old['sub_category'] != $sub_category) {
                    $changes[] = "Subcategory <strong>{$old['sub_category']}</strong> → <strong>{$sub_category}</strong>";
                }

                $old_kw = array_map('trim', explode(',', $old['category_keywords']));
                $old_kw = array_values(array_unique(array_filter($old_kw)));

                $removed = array_diff($old_kw, $new_kw);
                $added = array_diff($new_kw, $old_kw);

                if ($removed || $added) {
                    // Label বানাই
                    if ($sub_category !== 'none') {
                        $msg = "<strong>{$category}</strong> ক্যাটাগরি এর <strong>{$sub_category}</strong> সাব-ক্যাটাগরি";
                    } else {
                        $msg = "<strong>{$category}</strong> ক্যাটাগরি";
                    }

                    if ($removed) {
                        $msg .= " থেকে <span style='color:red'>" . implode(', ', $removed) . "</span> keywords বাদ দেওয়া হয়েছে ";
                    }
                    if ($added) {
                        if ($removed)
                            $msg .= " এবং <strong>{$sub_category}</strong> সাব-ক্যাটাগরি";
                        $msg .= "তে <span style='color:green'>" . implode(', ', $added) . "</span> যোগ হয়েছে";
                    }

                    $changes[] = $msg;
                }


                $duplicate_input = array_diff($new_raw, $new_kw);
                $already_exist = array_intersect($duplicate_input, $old_kw);

                if ($already_exist) {
                    if (count($already_exist) == 1) {
                        $changes[] = "এই <strong>" . implode(', ', $already_exist) . "</strong> keyword আগে থেকেই ছিলো";
                    } else {
                        $changes[] = "এই <strong>" . implode(', ', $already_exist) . "</strong> keywords আগে থেকেই ছিলো";
                    }
                }

                if ($changes) {
                    $_SESSION['success'] = implode("<br>", $changes);
                } else {
                    $_SESSION['success'] = "<strong>{$old['category_name']}</strong> ক্যাটাগরি তে কোনো পরিবর্তন হয় নাই";
                }

            } else {
                $_SESSION['danger'] = "ক্যাটাগরি পাওয়া যায়নি।";
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