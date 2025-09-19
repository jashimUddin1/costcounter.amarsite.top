<!-- pages/manage_categories.php -->
<?php
include '../core_file/categore_core.php';

// সব গ্রুপের ক্যাটাগরি নাম একত্র করা
$grouped_cats = [];
foreach ($category_groups as $group_cats) {
    $cats = array_map('trim', explode(',', $group_cats['group_category']));
    $grouped_cats = array_merge($grouped_cats, $cats);
}
$grouped_cats = array_map('strtolower', $grouped_cats); // case insensitive match

// যেসব ক্যাটাগরি কোনো গ্রুপে নাই, সেগুলো $uncategorized এ রাখি
$uncategorized = [];
foreach ($categories as $cat_name => $cat_data) {
    if (!in_array(strtolower($cat_name), $grouped_cats)) {
        $uncategorized[] = $cat_name;
    }
}

$query_string = $_SERVER['HTTP_REFERER'] ?? '';
$redirect_url = "../index.php";

if (!empty($query_string)) {
    $parsed_url = parse_url($query_string);
    if (isset($parsed_url['query'])) {
        $redirect_url .= '?' . $parsed_url['query'];
    }
}
?>

<!DOCTYPE html>
<html lang="bn">

<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="../images/cate_icon.png">
    <title>ক্যাটাগরি ম্যানেজমেন্ট</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .word-wrap {
            word-break: break-word;
        }
    </style>
</head>

<body class="container py-4">

    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <a href="<?= $redirect_url ?>" class="btn btn-secondary mb-2 mb-sm-0">Home</a>
        <h3 class="m-0 text-center flex-grow-1">ক্যাটাগরি ম্যানেজমেন্ট</h3>
        <div class="ms-auto">

            <!-- Single Entry button -->
            <button class="btn btn-primary me-2 has-tooltip" data-bs-toggle="modal" data-bs-target="#addSingleModal"
                title="Manual type Entry" data-bs-placement="bottom">
                + Single Entry
            </button>

            <!-- Multi Entry button -->
            <button type="button" class="btn btn-success has-tooltip" data-bs-toggle="modal"
                data-bs-target="#addMultiModal" title="Developer type Entry" data-bs-placement="bottom"
                data-bs-offset="0,10">
                + Multi Entry
            </button>

            <!-- Settings button -->
            <button class="btn btn-outline-secondary" type="button" data-bs-toggle="offcanvas"
                data-bs-target="#settingsMenu">
                ⚙️
            </button>

        </div>
    </div>

    <!-- Session Messages -->
    <div class="session_section">
        <?php include "../includes/session_modal.php"; ?>
    </div>


    <!-- Categories Table -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover align-middle text-center">
            <thead class="table-dark">
                <tr>
                    <th style="width: 2%" data-bs-toggle="tooltip" data-bs-placement="top" title="ক্রমিক নম্বর">ক্র.নং
                    </th>
                    <th style="width: 12%">নাম</th>
                    <th style="width: 10%">সাব-ক্যাটাগরি</th>
                    <th style="width: 53%">ক্যাটাগরি কীওয়ার্ড</th>
                    <?php if (!empty($_SESSION['category_edit']) || !empty($_SESSION['category_delete'])): ?>
                        <th style="width: 13%">একশন</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($categories as $cat_name => $subcats):
                    $rowspan = count($subcats);
                    $firstRow = true;
                    foreach ($subcats as $row): ?>

                        <tr>
                            <?php if ($firstRow): ?>
                                <td rowspan="<?= $rowspan ?>"><?= en2bn(htmlspecialchars($row['serial_no'])) ?></td>
                                <td rowspan="<?= $rowspan ?>" class="text-start word-wrap">
                                    <span class='badge bg-success p-2 me-1'><?= htmlspecialchars($cat_name) ?></span>
                                </td>
                            <?php endif; ?>

                            <td class="text-start word-wrap">
                                <?php if ($row['sub_category'] === 'none'): ?>
                                    <span class='badge bg-dark p-2 me-1'>নাই</span>
                                <?php else: ?>
                                    <span class='badge bg-secondary p-2 me-1'><?= htmlspecialchars($row['sub_category']) ?></span>
                                <?php endif; ?>
                            </td>

                            <td class="text-start word-wrap">
                                <?php
                                $cats = trim($row['category_keywords']);
                                if ($cats === '') {
                                    echo "<span class='text-white bg-dark p-1 rounded'>কোনো কীওয়ার্ড নাই</span>";
                                } else {
                                    echo htmlspecialchars($cats);
                                }
                                ?>
                            </td>

                            <?php if (!empty($_SESSION['category_edit']) || !empty($_SESSION['category_delete'])): ?>
                                <td>
                                    <div class="d-flex justify-content-center gap-2">
                                        <?php if (!empty($_SESSION['category_edit'])): ?>
                                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#editModal<?= $row['id'] ?>">Edit</button>
                                        <?php endif; ?>
                                        <?php if (!empty($_SESSION['category_delete'])): ?>
                                            <button class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#deleteModal<?= $row['id'] ?>">Delete</button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            <?php endif; ?>
                        </tr>

                        <!-- Edit Modal -->
                        <?php include 'edit_modal.php'; ?>

                        <!-- Delete Modal -->
                        <div class="modal fade" id="deleteModal<?= $row['id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <form method="post">
                                        <input type="hidden" name="action" value="delete_category">
                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                        <input type="hidden" name="cat_name" value="<?= $cat_name ?>">
                                        <div class="modal-body text-center">
                                            <p>আপনি কি নিশ্চিত যে এই ক্যাটাগরিটি ডিলিট করতে চান?</p>
                                            <strong><?= htmlspecialchars($cat_name) ?></strong>
                                        </div>
                                        <div class="modal-footer justify-content-center">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">না</button>
                                            <button type="submit" class="btn btn-danger">হ্যাঁ, ডিলিট</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    <?php $firstRow = false; endforeach; endforeach; ?>
            </tbody>
        </table>
    </div>


    <!-- Single Entry Modal -->
    <?php include 'add_single_modal.php'; ?>

    <!-- Multi Entry Modal -->
    <?php include 'addMultiModal.php'; ?>

    <!-- ⚙️ Settings Offcanvas -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="settingsMenu">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title">⚙️ Settings</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body">
            <form method="POST" action="category_setting.php">
                <div class="mb-3">
                    <h6>📂 Category Options</h6>
                    <div style="margin-left: 20px;">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="category_edit" id="categoryEdit"
                                <?= !empty($_SESSION['category_edit']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="categoryEdit">Allow Category Editing</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="category_delete" id="categoryDelete"
                                <?= !empty($_SESSION['category_delete']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="categoryDelete">Allow Category Delete</label>
                        </div>
                    </div>
                </div>
                <button type="submit" name="cat_option_save_btn" class="btn btn-primary w-100">Save Settings</button>
            </form>
        </div>
    </div>


    <hr>


    <!-- category_groups start -->
    <div id="categoriesGroups" class="category_groups mt-4">
        <div class="category_groups_header d-flex justify-content-between align-items-center mb-3">
            <h3 class="text-center">ক্যাটাগরি গ্রুপ ম্যানেজমেন্ট</h3>
            <!-- Add Group -->
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addGroupModal">
                ক্যাটাগরি গ্রুপ যোগ করুন
            </button>
        </div>


        <!-- Groups Table -->
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle text-center">
                <thead class="table-dark">
                    <tr>
                        <th style="width: 5%" data-bs-toggle="tooltip" data-bs-placement="top" title="ক্রমিক নম্বর">
                            ক্র.নং
                        </th>

                        <th style="width: 15%">গ্রুপ নাম</th>
                        <th style="width: 65%">ক্যাটাগরি</th>
                        <?php if (!empty($_SESSION['category_edit']) || !empty($_SESSION['category_delete'])): ?>
                            <th style="width: 15%">একশন</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($uncategorized)): ?>
                        <tr>
                            <td>#</td>
                            <td class="text-start ">
                                <span class="text-bold text-underline text-white bg-danger p-1 rounded"
                                    data-bs-toggle="tooltip" data-bs-html="true" title="Categories Without Group">
                                    গ্রুপ নাম ছাড়া
                                </span>
                            </td>
                            <td class="text-start word-wrap">
                                <?php
                                // সব গ্রুপের ক্যাটাগরি নাম একত্র করা
                                $grouped_cats = [];
                                foreach ($category_groups as $group_cats) {
                                    $cats = array_map('trim', explode(',', $group_cats['group_category']));
                                    $grouped_cats = array_merge($grouped_cats, $cats);
                                }
                                $grouped_cats = array_map('strtolower', $grouped_cats); // case insensitive match
                            
                                // যেসব ক্যাটাগরি কোনো গ্রুপে নাই, সেগুলো দেখানো
                                foreach ($categories as $cat_name => $cat_data) {
                                    if (!in_array(strtolower($cat_name), $grouped_cats)) {
                                        echo "<span class='text-dark bg-warning p-1 rounded me-2'>" . htmlspecialchars($cat_name, ENT_QUOTES) . '</span>';
                                    }
                                }
                                ?>
                            </td>
                            <?php if (!empty($_SESSION['category_edit']) || !empty($_SESSION['category_delete'])): ?>
                                <td>
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                        data-bs-target="#assignGroupModal" data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="গ্রুপে ক্যাটাগরি যুক্ত করুন">
                                        গ্রুপে যুক্ত করুন
                                    </button>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endif ?>

                    <?php foreach ($category_groups as $group_row): ?>

                        <tr>
                            <td><?= en2bn(htmlspecialchars($group_row['id'])) ?></td>
                            <td class="text-start word-wrap">
                                <?= "<span class='bg-info text-dark p-1 rounded'>" . htmlspecialchars($group_row['group_name']);
                                "" . "</span>" ?>
                            </td>
                            <td class="text-start word-wrap">
                                <?php
                                $cats = trim($group_row['group_category']);
                                if ($cats === '') {
                                    echo "<span class='text-white bg-dark p-1 rounded'>কোনো ক্যাটাগরি নাই</span>";
                                } else {
                                    $catArray = explode(',', $cats);
                                    foreach ($catArray as $cat) {
                                        $cat = trim($cat);
                                        if ($cat !== '') {
                                            echo "<span class='text-white bg-success p-1 rounded me-1'>" . htmlspecialchars($cat) . "</span>";
                                        }
                                    }
                                }
                                ?>
                            </td>


                            <?php if (!empty($_SESSION['category_edit']) || !empty($_SESSION['category_delete'])): ?>
                                <td>
                                    <div class="d-flex justify-content-center gap-2">
                                        <?php if (!empty($_SESSION['category_edit'])): ?>
                                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#editGroupModal<?= $group_row['id'] ?>">Edit</button>
                                        <?php endif; ?>
                                        <?php if (!empty($_SESSION['category_delete'])): ?>
                                            <button class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#deleteGroupModal<?= $group_row['id'] ?>">Delete</button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            <?php endif; ?>
                        </tr>

                        <!-- Edit Group Modal -->
                        <div class="modal fade" id="editGroupModal<?= $group_row['id'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content rounded-3 shadow">
                                    <div class="modal-header">
                                        <h5 class="modal-title">গ্রুপ এডিট করুন</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form method="post" action="manage_categories.php">
                                        <div class="modal-body">
                                            <input type="hidden" name="action" value="edit_group">
                                            <input type="hidden" name="old_id" value="<?= $group_row['id'] ?>">
                                            <div class="mb-3">
                                                <label class="form-label">ক্রমিক নং</label>
                                                <input type="text" name="new_id" class="form-control"
                                                    value="<?= htmlspecialchars(en2bn($group_row['id'])) ?>" required>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">গ্রুপ নাম</label>
                                                <input type="text" name="group_name" class="form-control"
                                                    value="<?= htmlspecialchars($group_row['group_name']) ?>" required>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">গ্রুপ ক্যাটাগরি</label>
                                                <textarea name="group_category" class="form-control"
                                                    rows="3"><?= htmlspecialchars($group_row['group_category']) ?></textarea>
                                            </div>

                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">বাতিল</button>
                                            <button type="submit" class="btn btn-success">আপডেট করুন</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Delete Group Modal -->
                        <div class="modal fade" id="deleteGroupModal<?= $group_row['id'] ?>" tabindex="-1"
                            aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content rounded-3 shadow">
                                    <div class="modal-header">
                                        <h5 class="modal-title">গ্রুপ ডিলিট</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form method="post" action="manage_categories.php">
                                        <div class="modal-body">
                                            <input type="hidden" name="action" value="delete_group">
                                            <input type="hidden" name="id" value="<?= $group_row['id'] ?>">
                                            <p>আপনি কি নিশ্চিতভাবে
                                                <strong><?= htmlspecialchars($group_row['group_name']) ?></strong>
                                                গ্রুপটি ডিলিট করতে চান?
                                            </p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">না</button>
                                            <button type="submit" class="btn btn-danger">হ্যাঁ, ডিলিট করুন</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>



    </div>

    <!-- Add Group Modal -->
    <div class="modal fade" id="addGroupModal" tabindex="-1" aria-labelledby="addGroupModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content rounded-3 shadow">
                <div class="modal-header">
                    <h5 class="modal-title" id="addGroupModalLabel">নতুন গ্রুপ যোগ করুন</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="বন্ধ"></button>
                </div>
                <form method="post" action="manage_categories.php">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_group">

                        <div class="mb-3">
                            <label class="form-label">গ্রুপ নাম</label>
                            <input type="text" name="group_name" class="form-control"
                                placeholder="নতুন ক্যাটাগরি গ্রুপ যোগ করুন" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">গ্রুপ ক্যাটাগরি (কমা দিয়ে আলাদা করুন)</label>
                            <textarea name="group_category" class="form-control" rows="3"
                                placeholder="যেমন: বাজার, মোবাইলখরচ, গাড়িভাড়া"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">বাতিল</button>
                        <button type="submit" class="btn btn-success">সংরক্ষণ করুন</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- category_groups end -->


    <!--Change  Modal -->
    <div class="modal fade" id="assignGroupModal" tabindex="-1" aria-labelledby="assignGroupModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="../core_file/change_core.php">
                    <div class="modal-header">
                        <h5 class="modal-title" id="assignGroupModalLabel">গ্রুপে ক্যাটাগরি যুক্ত করুন</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <?php if (!empty($uncategorized)): ?>
                            <p><strong>গ্রুপ ছাড়া ক্যাটাগরি</strong></p>
                            <div class="mb-3">
                                <?php foreach ($uncategorized as $cat): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="category_names[]"
                                            value="<?= htmlspecialchars($cat, ENT_QUOTES) ?>" id="cat_<?= md5($cat) ?>">
                                        <label class="form-check-label" for="cat_<?= md5($cat) ?>">
                                            <?= htmlspecialchars($cat, ENT_QUOTES) ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">গ্রুপ সিলেক্ট করুন</label>
                                <select name="group_id" class="form-select" required>
                                    <option value="">-- গ্রুপ নির্বাচন করুন --</option>
                                    <?php foreach ($category_groups as $group): ?>
                                        <option value="<?= $group['id'] ?>">
                                            <?= htmlspecialchars($group['group_name'], ENT_QUOTES) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php else: ?>
                            <p class="text-success">সব ক্যাটাগরি গ্রুপে যুক্ত আছে ✅</p>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">বন্ধ করুন</button>
                        <?php if (!empty($uncategorized)): ?>
                            <!-- Active Submit Button -->
                            <button type="submit" name="assign_category" class="btn btn-primary">সেভ করুন</button>
                        <?php else: ?>
                            <!-- Disabled Button -->
                            <button type="button" class="btn btn-secondary" disabled>সেভ করুন</button>
                        <?php endif; ?>
                    </div>

                </form>
            </div>
        </div>
    </div>


    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('.has-tooltip'))
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            })
        });
    </script>
</body>

</html>