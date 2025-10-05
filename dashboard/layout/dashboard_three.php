<?php // dashboard/layout/dashboard_three.php ?>

<style>
    .table {
        font-size: 11px;
        table-layout: auto;
        width: 100%;
        margin-bottom: 0 !important;
    }

    .table th {
        vertical-align: middle;
        text-align: center;
        padding: 4px 6px;
        overflow: hidden;
        white-space: nowrap;
    }

    .table td {
        vertical-align: middle;
        text-align: center;
        padding: 1px 2px !important;
        overflow: hidden;
        white-space: nowrap;
    }

    .table th.cat-col,
    .table td.cat-col {
        max-width: 70px;
    }

    .month-scroll {
        max-height: 90vh;
        overflow-y: auto;
        overflow-x: auto;
        border: 1px solid #ddd;
    }

    .card-body.no-padding {
        padding: 0 !important;
        padding-bottom: 2px !important;
    }

    .summary-bar {
        margin-top: 0 !important;
        background: #f8f9fa;
        border-top: 1px solid #ddd;
        padding: 6px 0;
        font-size: 12px;
    }

    /* 🔹 Yearly Summary Responsive Scroll */
    .yearly-summary-scroll {
        overflow-x: auto;
        border: 1px solid #ddd;
        border-top: none;
        background: #fff;
    }

    .yearly-summary-scroll table {
        min-width: 1000px;
        /* large table হলে scroll হবে */
    }

    @media (max-width: 768px) {
        .table {
            font-size: 10px;
        }
    }



    .card-header.bg-dark.text-white.text-center {
        font-size: 16px;
        /* 🔹 চাইলে 18px-20px ও দিতে পারো */
        font-weight: 600;
        /* একটু bold effect */
        letter-spacing: 0.5px;
        /* হালকা spacing */
    }
</style>

<?php foreach ($dashboard_three_data as $month => $days): ?>
    <?php
    // ✅ মূল category list
    $month_categories = $dashboard_three_categories;

    // ✅ মাসে যেসব category তে data আছে শুধু সেগুলো নাও
    $active_cats = [];
    foreach ($month_categories as $cat) {
        $has_data = false;
        foreach ($days as $day_data) {
            if (!empty($day_data[$cat])) {
                $has_data = true;
                break;
            }
        }
        if ($has_data)
            $active_cats[] = $cat;
    }

    // ✅ Fixed columns বাদ দাও
    $fixed_cols = ['অন্যান্য', 'মোট ব্যয়', 'আয়'];
    foreach ($fixed_cols as $f) {
        if (($idx = array_search($f, $active_cats)) !== false)
            unset($active_cats[$idx]);
    }

    // ✅ Fixed ৩টা সবশেষে add করো
    $active_cats = array_values($active_cats);
    $active_cats = array_merge($active_cats, $fixed_cols);

    // ✅ যদি category ১১ টার কম হয় → serial list থেকে বাকি পূরণ
    if (count($active_cats) < 11) {
        foreach ($month_categories as $cat) {
            if (!in_array($cat, $active_cats)) {
                array_splice($active_cats, count($active_cats) - 3, 0, $cat);
                if (count($active_cats) >= 11)
                    break;
            }
        }
    }
    ?>

    <div class="card mb-4">
        <div class="card-body no-padding">
            <div class="month-scroll">
                <table class="table table-bordered table-sm text-center align-middle">
                    <thead>
                        <tr>
                            <th colspan="<?= 1 + count($active_cats) ?>" class="bg-dark text-white">
                                <?= $month_map[$month] ?? $month ?>     <?= $year_bn ?>
                            </th>
                        </tr>
                        <tr class="table-light">
                            <th style="width:60px;">তাং</th>
                            <?php foreach ($active_cats as $cat): ?>
                                <th class="cat-col" title="<?= htmlspecialchars($cat) ?>">
                                    <?= htmlspecialchars($cat) ?>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        $days_in_month = cal_days_in_month(
                            CAL_GREGORIAN,
                            date('n', strtotime($month . " 1 " . $year)),
                            $year
                        );

                        for ($d = 1; $d <= $days_in_month; $d++):
                            $hasData = false;
                            foreach ($days[$d] ?? [] as $v) {
                                if ($v != 0) {
                                    $hasData = true;
                                    break;
                                }
                            }
                            if (!$hasData)
                                continue;

                            // প্রতিদিনের মোট ব্যয়
                            $daily_total = 0;
                            foreach ($active_cats as $cat_check) {
                                if (!in_array($cat_check, ['আয়', 'মোট ব্যয়'])) {
                                    $daily_total += $days[$d][$cat_check] ?? 0;
                                }
                            }
                            ?>
                            <tr>
                                <td><?= en2bn_number($d) ?></td>
                                <?php foreach ($active_cats as $cat): ?>
                                    <?php if ($cat === 'মোট ব্যয়'): ?>
                                        <td class="fw-bold bg-light"><?= $daily_total ? en2bn_number($daily_total) : '' ?></td>
                                    <?php else:
                                        $val = $days[$d][$cat] ?? 0;
                                        $vals = $dashboard_three_breakdown[$month][$d][$cat] ?? [];

                                        // ✅ Tooltip তৈরি করা
                                        if ($vals) {
                                            $entry_count = count($vals);
                                            $joined = implode(' + ', array_map('en2bn_number', $vals));
                                            $sum_bn = en2bn_number(array_sum($vals));
                                            $title = "{$entry_count} এন্ট্রি: {$joined} = {$sum_bn}";
                                        } else {
                                            $title = '';
                                        }
                                        ?>
                                        <td title="<?= htmlspecialchars($title) ?>">
                                            <?= $val ? en2bn_number($val) : '' ?>
                                        </td>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </tr>
                        <?php endfor; ?>
                    </tbody>

                    <tfoot>
                        <tr class="fw-bold table-secondary">
                            <td>মোট</td>
                            <?php foreach ($active_cats as $cat):
                                $sum = 0;
                                foreach ($days as $day_data) {
                                    if ($cat === 'মোট ব্যয়') {
                                        foreach ($day_data as $cat2 => $val2) {
                                            if (!in_array($cat2, ['আয়', 'মোট ব্যয়'])) {
                                                $sum += $val2;
                                            }
                                        }
                                    } else {
                                        $sum += $day_data[$cat] ?? 0;
                                    }
                                }
                                ?>
                                <td><?= $sum ? en2bn_number($sum) : '' ?></td>
                            <?php endforeach; ?>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="summary-bar fw-bold text-center mt-2">
                <?php
                // 🔹 মোট আয় ও মোট ব্যয় হিসাব
                $total_income = 0;
                $total_expense = 0;

                foreach ($days as $day_data) {
                    foreach ($day_data as $cat => $val) {
                        if ($cat === 'আয়') {
                            $total_income += $val;
                        } elseif ($cat !== 'আয়' && $cat !== 'মোট ব্যয়') {
                            $total_expense += $val;
                        }
                    }
                }

                $net_income = $total_income - $total_expense;
                $is_profit = $net_income >= 0;
                $label = $is_profit ? "সম্পদ" : "দায়";
                $net_color = $is_profit ? "text-success" : "text-danger";
                ?>

                <div class="row m-0 px-2 text-nowrap">
                    <div class="col-4 text-start ps-2 text-success">
                        মোট আয়: <?= format_currency_bn($total_income) ?>
                    </div>
                    <div class="col-4 text-center text-primary">
                        মোট ব্যয়: <?= format_currency_bn($total_expense) ?>
                    </div>
                    <div class="col-4 text-end pe-2 <?= $net_color ?>">
                        <?= $label ?>: <?= format_currency_bn(abs($net_income)) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<?php
// ===================== 🔹 Yearly Summary Table =====================
if (!empty($dashboard_three_data)) {
    $all_categories = $dashboard_three_categories;
    $fixed_cols = ['অন্যান্য', 'মোট ব্যয়', 'আয়'];
    foreach ($fixed_cols as $f) {
        if (($idx = array_search($f, $all_categories)) !== false)
            unset($all_categories[$idx]);
    }
    $all_categories = array_values($all_categories);
    $all_categories = array_merge($all_categories, $fixed_cols);

    $monthly_summary = [];
    $total_per_category = array_fill_keys($all_categories, 0);
    $total_income = 0;
    $total_expense = 0;

    foreach ($dashboard_three_data as $month => $days) {
        foreach ($all_categories as $cat) {
            $sum = 0;
            foreach ($days as $day_data) {
                if ($cat === 'মোট ব্যয়') {
                    foreach ($day_data as $cat2 => $val2) {
                        if (!in_array($cat2, ['আয়', 'মোট ব্যয়'])) {
                            $sum += $val2;
                        }
                    }
                } else {
                    $sum += $day_data[$cat] ?? 0;
                }
            }
            $monthly_summary[$month][$cat] = $sum;
            $total_per_category[$cat] += $sum;
        }

        $monthly_income = $monthly_summary[$month]['আয়'] ?? 0;
        $monthly_expense = $monthly_summary[$month]['মোট ব্যয়'] ?? 0;
        $total_income += $monthly_income;
        $total_expense += $monthly_expense;
    }

    $net = $total_income - $total_expense;
    $net_label = $net >= 0 ? "মোট সম্পদ" : "মোট দায়";
    $net_color = $net >= 0 ? "text-success" : "text-danger";
}
?>

<div class="card yearSummary mt-4 mb-5">
    <div class="card-header bg-dark text-white text-center fs-3">
        <?= en2bn_number($year) ?> সালের সারসংক্ষেপ
    </div>
    <div class="card-body p-0 yearly-summary-scroll">
        <table class="table table-bordered  table- text-center align-middle mb-0">
            <thead class="table-light">
                <tr class="table-secondary">
                    <th style="width:70px;font-size:18px;font-weight:600">মাস</th>
                    <?php foreach ($all_categories as $cat): ?>
                        <th style="width:70px;font-size:17px;font-weight:500"><?= htmlspecialchars($cat) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dashboard_three_data as $month => $days): ?>
                    <tr>
                        <td style="width:70px;font-size:18px;font-weight:600"><?= $month_map[$month] ?? $month ?></td>
                        <?php foreach ($all_categories as $cat): ?>
                            <td style="width:70px;font-size:16px;font-weight:400"><?= !empty($monthly_summary[$month][$cat]) ? en2bn_number($monthly_summary[$month][$cat]) : '' ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot class="fw-bold table-secondary">
                <tr>
                    <td style="width:70px;font-size:18px;font-weight:600">মোট</td>
                    <?php foreach ($all_categories as $cat): ?>
                        <td style="width:70px;font-size:17px;font-weight:500"><?= $total_per_category[$cat] ? en2bn_number($total_per_category[$cat]) : '' ?></td>
                    <?php endforeach; ?>
                </tr>
            </tfoot>
        </table>
    </div>
    <div class="row fw-bold text-center py-2 border-top bg-light m-0">
        <div class="col-4 text-start ps-3 text-success fs-4">
            মোট আয়: <?= format_currency_bn($total_income) ?>
        </div>
        <div class="col-4 text-primary fs-4">
            মোট ব্যয়: <?= format_currency_bn($total_expense) ?>
        </div>
        <div class="col-4 text-end pe-3 fs-4 <?= $net_color ?>">
            <?= $net_label ?>: <?= format_currency_bn(abs(num: $net)) ?>
        </div>
    </div>
</div>