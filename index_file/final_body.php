<?php // index_file/final_body.php

// ================== ONE-TIME HELPERS (avoid redeclare) ==================
if (!function_exists('en_to_bn_digits')) {
  function en_to_bn_digits($str) {
    $en = ['0','1','2','3','4','5','6','7','8','9'];
    $bn = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
    return str_replace($en, $bn, (string)$str);
  }
}
if (!function_exists('en2bn_number')) {
  function en2bn_number($x) { return en_to_bn_digits($x); }
}
if (!function_exists('bn_month')) {
  function bn_month($enMonth) {
    $map = [
      'january'=>'জানুয়ারি','jan'=>'জানুয়ারি',
      'february'=>'ফেব্রুয়ারি','feb'=>'ফেব্রুয়ারি',
      'march'=>'মার্চ','mar'=>'মার্চ',
      'april'=>'এপ্রিল','apr'=>'এপ্রিল',
      'may'=>'মে',
      'june'=>'জুন','jun'=>'জুন',
      'july'=>'জুলাই','jul'=>'জুলাই',
      'august'=>'আগস্ট','aug'=>'আগস্ট',
      'september'=>'সেপ্টেম্বর','sep'=>'সেপ্টেম্বর','sept'=>'সেপ্টেম্বর',
      'october'=>'অক্টোবর','oct'=>'অক্টোবর',
      'november'=>'নভেম্বর','nov'=>'নভেম্বর',
      'december'=>'ডিসেম্বর','dec'=>'ডিসেম্বর',
    ];
    $k = strtolower(trim($enMonth));
    return $map[$k] ?? $enMonth;
  }
}
if (!function_exists('bn_month_short')) {
  function bn_month_short($enMonth) {
    $map = [
      'january'=>'জানু','jan'=>'জানু',
      'february'=>'ফেব','feb'=>'ফেব',
      'march'=>'মার্চ','mar'=>'মার্চ',
      'april'=>'এপ্রি','apr'=>'এপ্রি',
      'may'=>'মে',
      'june'=>'জুন','jun'=>'জুন',
      'july'=>'জুলা','jul'=>'জুলা',
      'august'=>'আগ','aug'=>'আগ',
      'september'=>'সেপ্টে','sep'=>'সেপ্টে','sept'=>'সেপ্টে',
      'october'=>'অক্টো','oct'=>'অক্টো',
      'november'=>'নভে','nov'=>'নভে',
      'december'=>'ডিসে','dec'=>'ডিসে',
    ];
    $k = strtolower(trim($enMonth));
    return $map[$k] ?? $enMonth;
  }
}
if (!function_exists('bn_weekday_full')) {
  function bn_weekday_full(DateTime $dt) {
    $map = [
      'Sat'=>'শনিবার','Sun'=>'রবিবার','Mon'=>'সোমবার',
      'Tue'=>'মঙ্গলবার','Wed'=>'বুধবার','Thu'=>'বৃহস্পতিবার','Fri'=>'শুক্রবার',
    ];
    return $map[$dt->format('D')] ?? $dt->format('D');
  }
}
if (!function_exists('bn_weekday_short')) {
  function bn_weekday_short(DateTime $dt) {
    $map = [
      'Sat'=>'শনি','Sun'=>'রবি','Mon'=>'সোম',
      'Tue'=>'মঙ্গল','Wed'=>'বুধ','Thu'=>'বৃহ', // চাইলে 'বৃহস্পতি'
      'Fri'=>'শুক্র',
    ];
    return $map[$dt->format('D')] ?? $dt->format('D');
  }
}
if (!function_exists('bn_full_date')) {
  // single date -> full month + optional full weekday
  function bn_full_date(string $ymd, bool $with_weekday=false): string {
    $ts = strtotime($ymd);
    if ($ts === false) return en_to_bn_digits($ymd);
    $d  = date('d', $ts);
    $m  = date('M', $ts);
    $y  = date('Y', $ts);
    $txt = en_to_bn_digits($d).' '.bn_month($m).' '.en_to_bn_digits($y);
    if ($with_weekday) {
      $dt = DateTime::createFromFormat('!Y-m-d', date('Y-m-d',$ts));
      if ($dt) $txt .= ' | '.bn_weekday_full($dt);
    }
    return $txt;
  }
}

/**
 * header_date_or_range() ইংরেজি বেস স্ট্রিং বানায়:
 * - same month: "08-10 Nov 2025"
 * - cross month/year: "28 Nov 2025 → 02 Dec 2025"
 */
if (!function_exists('header_date_or_range')) {
  function header_date_or_range(string $fromDate, ?string $toDate): string {
    $fromTxt = date('d M Y', strtotime($fromDate));
    if (!empty($toDate)) {
        $toTxt = date('d M Y', strtotime($toDate));
        if (date('mY', strtotime($fromDate)) === date('mY', strtotime($toDate))) {
            $d1 = date('d', strtotime($fromDate));
            $d2 = date('d', strtotime($toDate));
            $tail = date('M Y', strtotime($toDate));
            return "$d1-$d2 $tail"; // 08-10 Nov 2025
        }
        return $fromTxt . " → " . $toTxt; // cross month
    }
    return $fromTxt; // single
  }
}

/**
 * ✅ MIXED WEEKDAY RANGE (আপনার চাওয়া):
 * same month input: "08-10 Nov 2025" ->
 * "০৮–১০ নভেম্বর ২০২৫ | শনি – সোমবার"
 * cross month input: "28 Nov 2025 → 02 Dec 2025" ->
 * "২৮ নভেম্বর ২০২৫ → ০২ ডিসেম্বর ২০২৫ | শনি – সোমবার"
 */
if (!function_exists('bn_date_range_pretty_mixed')) {
  function bn_date_range_pretty_mixed(string $input, string $dash='–'): string {
    $input = trim($input);

    // Case 1: same-month range: 08-10 Mon YYYY
    $reSame = '/^\s*(\d{1,2})\s*[-–—]\s*(\d{1,2})\s+([A-Za-z]+)\s+(\d{4})\s*$/';
    if (preg_match($reSame, $input, $m)) {
      [$full,$d1,$d2,$mon,$yr] = $m;
      $monBn = bn_month($mon);
      $dateTxt = en_to_bn_digits(sprintf('%02d',$d1)).$dash.en_to_bn_digits(sprintf('%02d',$d2)).' '.$monBn.' '.en_to_bn_digits($yr);

      $mNum = date('n', strtotime("1 $mon $yr"));
      $dt1 = DateTime::createFromFormat('!Y-n-j', "$yr-$mNum-$d1");
      $dt2 = DateTime::createFromFormat('!Y-n-j', "$yr-$mNum-$d2");
      if ($dt1 && $dt2) {
        $wdTxt = bn_weekday_short($dt1).' '.$dash.' '.bn_weekday_full($dt2); // short – full
        return $dateTxt.' | '.$wdTxt;
      }
      return $dateTxt;
    }

    // Case 2: cross-month range: "28 Mon YYYY → 02 Mon YYYY"
    $parts = preg_split('/\s*→\s*/u', $input);
    if (count($parts) === 2) {
      $left = $parts[0];  // "28 Nov 2025"
      $right = $parts[1]; // "02 Dec 2025"
      $reSingle = '/^\s*(\d{1,2})\s+([A-Za-z]+)\s+(\d{4})\s*$/';

      if (preg_match($reSingle, $left, $L) && preg_match($reSingle, $right, $R)) {
        [$fullL,$d1,$mon1,$yr1] = $L;
        [$fullR,$d2,$mon2,$yr2] = $R;

        $leftTxt  = en_to_bn_digits(sprintf('%02d',$d1)).' '.bn_month($mon1).' '.en_to_bn_digits($yr1);
        $rightTxt = en_to_bn_digits(sprintf('%02d',$d2)).' '.bn_month($mon2).' '.en_to_bn_digits($yr2);
        $dateTxt = $leftTxt.' → '.$rightTxt;

        $mNum1 = date('n', strtotime("1 $mon1 $yr1"));
        $mNum2 = date('n', strtotime("1 $mon2 $yr2"));
        $dt1 = DateTime::createFromFormat('!Y-n-j', "$yr1-$mNum1-$d1");
        $dt2 = DateTime::createFromFormat('!Y-n-j', "$yr2-$mNum2-$d2");

        if ($dt1 && $dt2) {
          $wdTxt = bn_weekday_short($dt1).' '.$dash.' '.bn_weekday_full($dt2);
          return $dateTxt.' | '.$wdTxt;
        }
        return $dateTxt;
      }
    }

    // Case 3: single already formatted like "12 Nov 2025" → we won't get here in header, but just in case
    return en_to_bn_digits($input);
  }
}

// =============== DEFAULTS / SAFETY ==================
if (!isset($excluded_categories) || !is_array($excluded_categories)) {
  $excluded_categories = [];
}

// ================== Balance বের করা ==================
$query = "SELECT id, amount
          FROM balancesheet
          WHERE user_id = '$user_id'
            AND date LIKE '$current_year-$current_month-%'
            AND balance_type = 'balance_bd'
          ORDER BY date DESC
          LIMIT 1";
$result = mysqli_query($con, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $balance_id = $row['id'];
    $amount = $row['amount'];
    $has_balance_bd = true;
} else {
    $amount = 0;
    $has_balance_bd = false;
}

// ================== Sort control ==================
$sort = (($_GET['sort'] ?? 'asc') === 'desc') ? 'DESC' : 'ASC';

// ================== মাসের সব ট্রান্স্যাকশন ==================
$txn_query = "SELECT id, date, to_date, description, amount, category 
              FROM cost_data 
              WHERE user_id = '$user_id' 
                AND YEAR(date) = $current_year
                AND MONTH(date) = $current_month
              ORDER BY date $sort";
$txn_result = mysqli_query($con, $txn_query);

$grouped_data = [];
$current_balance = $amount ?? 0;
$total_monthly_cost = 0;
$total_monthly_income = 0;

// ================== প্রসেসিং ==================
if ($txn_result && mysqli_num_rows($txn_result) > 0) {
  while ($txn = mysqli_fetch_assoc($txn_result)) {
    $date = date('Y-m-d', strtotime($txn['date'])); // from_date
    $to_date_raw = $txn['to_date'] ?? null;
    $to_date = $to_date_raw ? date('Y-m-d', strtotime($to_date_raw)) : null;

    // Running balance build-up
    if ($txn['category'] === 'আয়') {
      $total_monthly_income += $txn['amount'];
      $current_balance += $txn['amount'];
    } elseif ($txn['category'] === 'প্রাপ্তি') {
      $current_balance += $txn['amount'];
    } elseif ($txn['category'] === 'ব্যয় হৃাস') {
      $current_balance += $txn['amount'];
      $total_monthly_cost -= $txn['amount'];
    } else {
      $current_balance -= $txn['amount'];
      if (!in_array($txn['category'], $excluded_categories)) {
        $total_monthly_cost += $txn['amount'];
      }
    }

    // attach
    $txn['running_balance'] = $current_balance;
    $txn['to_date_norm'] = $to_date;

    $grouped_data[$date][] = $txn;
  }
}
$final_running_balance = $current_balance;

// ================== RENDER ==================
?>
<div class="costDetails">
  <div class="d-flex justify-content-between align-items-center mb-3 mt-3 monthly-cost-header">
    <h4 class="mb-0">🗓️ মাসের খরচ</h4>

    <form method="GET" class="d-inline-block ms-3">
      <input type="hidden" name="year" value="<?= $current_year ?>">
      <input type="hidden" name="month" value="<?= $current_month ?>">
      <select name="sort" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">
        <option value="asc"  <?= (($_GET['sort'] ?? '') === 'asc')  ? 'selected' : '' ?>>পুরাতন আগে</option>
        <option value="desc" <?= (($_GET['sort'] ?? '') === 'desc') ? 'selected' : '' ?>>নতুন আগে</option>
      </select>
    </form>

    <div class="d-flex">
      <h4 class="mb-0">অবশিষ্ট <span id="balanceAmount"><?= en2bn_number($amount) ?></span> টাকা </h4>

      <?php if (!empty($_SESSION['edit_balance'])): ?>
        <?php if ($has_balance_bd): ?>
          <button class="btn btn-sm btn-outline-secondary edit-btn"
                  data-bs-toggle="modal"
                  data-bs-target="#editBalanceModal"
                  data-id="<?= $balance_id ?>"
                  data-value="<?= $amount ?>"
                  data-year="<?= $current_year ?>"
                  data-month="<?= $current_month ?>">
            ✏️
          </button>
        <?php else: ?>
          <button class="btn btn-sm btn-outline-primary edit-btn"
                  data-bs-toggle="modal"
                  data-bs-target="#setBalanceModal"
                  data-id="<?= $user_id ?>"
                  data-year="<?= $current_year ?>"
                  data-month="<?= $current_month ?>">
            ✏️
          </button>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>

  <?php foreach ($grouped_data as $date => $records): ?>
    <?php
      // এই গ্রুপে যদি কোন এন্ট্রির to_date থাকে—হেডার রেঞ্জ হবে
      $grp_to_date = null;
      foreach ($records as $r) {
        if (!empty($r['to_date_norm'])) { $grp_to_date = $r['to_date_norm']; break; }
      }
      $header_txt = header_date_or_range($date, $grp_to_date); // "08-10 Nov 2025" or "28 Nov 2025 → 02 Dec 2025" or "08 Nov 2025"
    ?>

    <div class="card mb-3">
      <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <div>
          <?php if ($grp_to_date): ?>
            <!-- ✅ Range: first = short weekday, last = full weekday -->
            <strong><?= bn_date_range_pretty_mixed($header_txt); ?></strong>
          <?php else: ?>
            <!-- ✅ Single: full weekday -->
            <strong><?= bn_full_date($date, true); ?></strong>
          <?php endif; ?>
        </div>

        <div class="rightEditDelete">
          <?php if (!empty($_SESSION['edit_date'])): ?>
            <button class="btn btn-sm btn-outline-secondary edit-date-btn"
                    data-bs-toggle="modal"
                    data-bs-target="#editDateModal"
                    data-date="<?= date('Y-m-d', strtotime($date)) ?>">
              ✏️ তারিখ
            </button>
          <?php endif; ?>

          <?php if (!empty($_SESSION['delete_day'])): ?>
            <a href="core_file/delete_day_entries.php?date=<?= date('d-m-Y', strtotime($date)) ?>"
               class="btn btn-sm btn-outline-danger"
               onclick="return confirm('🔴 আপনি কি নিশ্চিত যে, <?= date('d/m/Y', strtotime($date)) ?> তারিখের সব এন্ট্রি মুছে ফেলতে চান?')">
              🗑️
            </a>
          <?php endif; ?>
        </div>
      </div>

      <div class="card-body">
        <?php $total = 0; $i = 1; ?>
        <ul class="list-group list-group-flush">
          <?php foreach ($records as $txn): ?>
            <?php if (!in_array($txn['category'], $excluded_categories)) { $total += $txn['amount']; } ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <div>
                <?= en2bn_number($i) ?>.
                <?= htmlspecialchars(en2bn_number($txn['description'])) ?>
                <?= en2bn_number($txn['amount']) ?> টাকা
                (<?= htmlspecialchars($txn['category']) ?>)
              </div>
              <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary rounded-pill">
                  <?= en2bn_number($txn['running_balance']) ?>৳
                </span>

                <?php if (!empty($_SESSION['edit_enabled'])): ?>
                  <button class="btn btn-sm btn-outline-warning edit-btn"
                          data-id="<?= $txn['id'] ?>"
                          data-date="<?= date('Y-m-d', strtotime($txn['date'])) ?>"
                          data-description="<?= htmlspecialchars($txn['description']) ?>"
                          data-amount="<?= $txn['amount'] ?>"
                          data-category="<?= htmlspecialchars($txn['category']) ?>"
                          data-to_date="<?= $txn['to_date'] ? date('Y-m-d', strtotime($txn['to_date'])) : '' ?>"
                          data-bs-toggle="modal"
                          data-bs-target="#editCostDataModal">
                    ✏️
                  </button>
                <?php endif; ?>

                <?php if (!empty($_SESSION['delete_enabled'])): ?>
                  <a href="core_file/delete_entry.php?id=<?= $txn['id'] ?>"
                     class="btn btn-sm btn-outline-danger"
                     onclick="return confirm('তুমি কি এই এন্ট্রিটি মুছে ফেলতে চাও?')">🗑️</a>
                <?php endif; ?>
              </div>
            </li>
            <?php $i++; endforeach; ?>
        </ul>
        <div class="mt-2 fw-bold">🔸 মোট: <?= en2bn_number($total) ?> টাকা</div>
      </div>
    </div>
  <?php endforeach; ?>

  <div class="mb-5 mt-5"><hr></div>

  <div class="container rounded-3 alert alert-success fixed-bottom mb-0 d-flex justify-content-between align-items-center fs-5 bottom_fixed_menu">      
    <div class="text-start">
      <strong><span class="bottom_nav_cut">মোট</span> আয়: <?= en2bn_number($total_monthly_income) ?> টাকা</strong>
    </div>

    <div class="text-center flex-grow-1">
      <strong><span class="bottom_nav_cut">মোট</span> ব্যয়: <?= en2bn_number($total_monthly_cost) ?> টাকা</strong>
    </div>

    <div class="text-end">
      <strong>অবশিষ্ট: <?= en2bn_number($final_running_balance) ?> টাকা</strong>
    </div>
  </div>
</div>
