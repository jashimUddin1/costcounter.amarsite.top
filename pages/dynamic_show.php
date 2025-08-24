<?php
session_start();
include("../db/dbcon.php");

// User authentication
if (!isset($_SESSION['authenticated'])) {
    header("Location: ../login/index.php");
    exit();
}
$user_id = $_SESSION['auth_user']['id'] ?? null;

// --- English → Bangla ---
function en2bn($number)
{
    $en = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    $bn = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
    return str_replace($en, $bn, $number);
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
$stmt = $con->prepare("SELECT * FROM category_groups WHERE user_id = ? ORDER BY group_name");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    // explode categories string into array
    $cats = array_map('trim', explode(',', $row['group_category']));
    $category_groups[$row['group_name']] = $cats;
}
$stmt->close();
?>

<?php include "../includes/header.php" ?>
<!-- HTML part -->
<div class="col-md-2">
    <label class="form-label">নির্বাচন করুন</label>
    <select name="category" class="form-select" required>
        <option value="" disabled selected>ক্যাটাগরি দিন</option>
        <option value="__add_new__">+ নতুন ক্যাটাগরি</option>
        <?php
        foreach ($category_groups as $group_name => $cats) {
            if (!empty($cats)) {
                echo "<optgroup label='" . htmlspecialchars($group_name, ENT_QUOTES) . "'>";
                foreach ($cats as $cat) {
                    if (isset($categories[$cat])) {
                        echo "<option value='" . htmlspecialchars($cat, ENT_QUOTES) . "'>" . htmlspecialchars($cat) . "</option>";
                    }
                }
                echo "</optgroup>";
            }
        }

        // Show categories not in any group
        foreach ($categories as $cat_name => $row) {
            $in_group = false;
            foreach ($category_groups as $group_cats) {
                if (in_array($cat_name, $group_cats)) {
                    $in_group = true;
                    break;
                }
            }
            if (!$in_group) {
                echo "<option value='" . htmlspecialchars($cat_name, ENT_QUOTES) . "'>" . htmlspecialchars($cat_name) . "</option>";
            }
        }
        ?>
    </select>
</div>

<hr>

<div class="container mt-4">
    <div class="row">
        <table class="table table-bordered table-striped align-middle text-center">
            <thead class="table-dark">
                <tr>
                    <th>S.N</th>
                    <th>Group Name</th>
                    <th>Category Name</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1.</td>
                    <td>দৈনন্দিন খরচ</td>
                    <td class="text-start">
                        <span class="bg-secondary text-white rounded px-2 py-1 me-1">
                            বাজার <button class="btn-close btn-close-white btn-sm ms-1" aria-label="Remove"></button>
                        </span>
                        <span class="bg-secondary text-white rounded px-2 py-1 me-1">
                            বাহিরেরখরচ <button class="btn-close btn-close-white btn-sm ms-1"
                                aria-label="Remove"></button>
                        </span>
                        <span class="bg-secondary text-white rounded px-2 py-1 me-1">
                            মোবাইলখরচ <button class="btn-close btn-close-white btn-sm ms-1"
                                aria-label="Remove"></button>
                        </span>
                        <span class="bg-secondary text-white rounded px-2 py-1 me-1">
                            গাড়িভাড়া <button class="btn-close btn-close-white btn-sm ms-1"
                                aria-label="Remove"></button>
                        </span>
                        <span class="bg-secondary text-white rounded px-2 py-1 me-1 fs-7">
                            কসমেটিক্স <button class="btn-close btn-close-white btn-sm ms-1"
                                aria-label="Remove"></button>
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-primary">Edit</button>
                        <button class="btn btn-sm btn-danger">Delete</button>
                    </td>
                </tr>

            </tbody>
        </table>
    </div>
</div>

<?php include "../includes/footer.php" ?>