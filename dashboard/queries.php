<?php
// গার্ড
if (!isset($user_id)) {
  $user_id = $_SESSION['auth_user']['id'] ?? 0;
}
if (!isset($year) || !$year) {
  $year = (int) date('Y');
}
if (!isset($month) || $month === '') {
  $month = date('F');
}
if (!isset($is_all_year)) {
  $is_all_year = (strtolower($year) === 'all');
}
if (!isset($is_all_month)) {
  $is_all_month = (strtolower($month) === 'all');
}

$placeholders = implode(',', array_fill(0, count($excluded_categories), '?'));

/* ----------------------
   CATEGORY-WISE QUERY
-----------------------*/
if ($is_all_year) {
  $sql = "SELECT year, SUM(amount) as total
          FROM cost_data
          WHERE user_id = ?
            AND category NOT IN ($placeholders)
          GROUP BY year";
  $stmt = $con->prepare($sql);
  $stmt->bind_param("i" . str_repeat("s", count($excluded_categories)), $user_id, ...$excluded_categories);

  $stmt->execute();
  $res = $stmt->get_result();
  while ($row = $res->fetch_assoc()) {
    $category_data[$row['year'] . " সাল"] = (float) $row['total'];
    $total_expense += (float) $row['total'];
  }
  $stmt->close();

} elseif ($is_all_month) {
  $sql = "SELECT category, SUM(amount) as total
          FROM cost_data
          WHERE user_id = ?
            AND year = ?
            AND category NOT IN ($placeholders)
          GROUP BY category";
  $stmt = $con->prepare($sql);
  $stmt->bind_param(
    "ii" . str_repeat("s", count($excluded_categories)),
    $user_id,
    $year,
    ...$excluded_categories
  );
  $stmt->execute();
  $res = $stmt->get_result();
  while ($row = $res->fetch_assoc()) {
    $category_data[$row['category']] = (float) $row['total'];
    $total_expense += (float) $row['total'];
  }
  $stmt->close();

} else {
  $sql = "SELECT category, SUM(amount) as total
          FROM cost_data
          WHERE user_id = ?
            AND year = ?
            AND month = ?
            AND category NOT IN ($placeholders)
          GROUP BY category";
  $stmt = $con->prepare($sql);
  $stmt->bind_param(
    "iis" . str_repeat("s", count($excluded_categories)),
    $user_id,
    $year,
    $month,
    ...$excluded_categories
  );
  $stmt->execute();
  $res = $stmt->get_result();

  while ($row = $res->fetch_assoc()) {
    $cat = $row['category'];
    $total = (float) $row['total'];

    $category_data[$cat] = $total;

    if ($cat === 'ব্যয় হৃাস') {
      $total_expense -= $total; // 🟢 minus হবে
    } else {
      $total_expense += $total;
    }
  }


  $stmt->close();
}

/* ----------------------
   AXIS (DAILY / MONTHLY / YEARLY)
-----------------------*/
if ($is_all_year) {
  // বছরভিত্তিক
  $axis_raw = [];
  $sql2 = "SELECT year, SUM(amount) as total
           FROM cost_data
           WHERE user_id = ?
             AND category NOT IN ($placeholders)
           GROUP BY year
           ORDER BY year ASC";
  $stmt2 = $con->prepare($sql2);
  $stmt2->bind_param(
    "i" . str_repeat("s", count($excluded_categories)),
    $user_id,
    ...$excluded_categories
  );
  $stmt2->execute();
  $res2 = $stmt2->get_result();
  while ($row = $res2->fetch_assoc()) {
    $axis_labels[] = en2bn_number($row['year']);
    $axis_data[] = (float) $row['total'];
  }
  $stmt2->close();

} elseif ($is_all_month) {
  // মাসভিত্তিক
  $axis_raw = array_fill(1, 12, 0.0);
  $sql2 = "SELECT month, SUM(amount) as total
           FROM cost_data
           WHERE user_id = ?
             AND year = ?
             AND category NOT IN ($placeholders)
           GROUP BY month";
  $stmt2 = $con->prepare($sql2);
  $stmt2->bind_param(
    "ii" . str_repeat("s", count($excluded_categories)),
    $user_id,
    $year,
    ...$excluded_categories
  );
  $stmt2->execute();
  $res2 = $stmt2->get_result();

  while ($row = $res2->fetch_assoc()) {
    $day = (int) $row['day'];
    $total = (float) $row['total'];
    $cat = $row['category'] ?? '';

    if (!isset($axis_raw[$day]))
      $axis_raw[$day] = 0;

    if ($cat === 'ব্যয় হৃাস') {
      $axis_raw[$day] -= $total; // 🟢 খরচ কমবে
    } else {
      $axis_raw[$day] += $total;
    }
  }

  $stmt2->close();

  foreach (range(1, 12) as $mi) {
    $m_en = $months_en[$mi - 1];
    $axis_labels[] = $month_map[$m_en];
    $axis_data[] = $axis_raw[$mi];
  }

} else {
  // প্রতিদিন
  $axis_raw = [];
$sql2 = "SELECT DAY(date) as day, category, SUM(amount) as total
         FROM cost_data
         WHERE user_id = ? AND year = ? AND month = ?
           AND category NOT IN ($placeholders)
         GROUP BY date, category
         ORDER BY date ASC";

  $stmt2 = $con->prepare($sql2);
  $stmt2->bind_param(
    "iis" . str_repeat("s", count($excluded_categories)),
    $user_id,
    $year,
    $month,
    ...$excluded_categories
  );
  $stmt2->execute();
  $res2 = $stmt2->get_result();

  while ($row = $res2->fetch_assoc()) {
    $day = (int) $row['day'];
    $total = (float) $row['total'];
    $cat = $row['category'];

    if (!isset($axis_raw[$day]))
      $axis_raw[$day] = 0;

    if ($cat === 'ব্যয় হৃাস') {
      $axis_raw[$day] -= $total; // 🟢 minus হবে
    } else {
      $axis_raw[$day] += $total;
    }
  }


  $stmt2->close();

  $ts = strtotime("{$month} 1 {$year}");
  $month_number = $ts ? (int) date('n', $ts) : (int) date('n');
  $safe_year = $year ?: (int) date('Y');
  $days_in_month = cal_days_in_month(CAL_GREGORIAN, $month_number, $safe_year) ?: 30;

  for ($d = 1; $d <= $days_in_month; $d++) {
    $axis_labels[] = en2bn_number($d);
    $axis_data[] = $axis_raw[$d] ?? 0.0;
  }
}

arsort($category_data, SORT_NUMERIC);
