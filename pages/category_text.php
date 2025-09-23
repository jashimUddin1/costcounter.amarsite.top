<?php
session_start();
include("../db/dbcon.php");

// ইউজার লগইন না করলে রিডাইরেক্ট করো
if (!isset($_SESSION['authenticated'])) {
  header("Location: ../login/index.php");
  exit();
}

$user_id = $_SESSION['auth_user']['id'] ?? null;

// --- Fetch categories with subcategories ---
$categories = [];
$stmt = $con->prepare("SELECT id, serial_no, category_name, sub_category, category_keywords 
                       FROM categories WHERE user_id = ? ORDER BY serial_no ASC, subcategory_serial ASC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

while ($row = $res->fetch_assoc()) {
  $cat_serial = (int)$row['serial_no'];
  $cat_name   = trim($row['category_name']);
  $sub_name   = trim($row['sub_category']);
  $keywords   = trim($row['category_keywords']);

  if (!isset($categories[$cat_name])) {
    $categories[$cat_name] = [
      'serial_no' => $cat_serial,
      'subs'      => []
    ];
  }

  if (!isset($categories[$cat_name]['subs'][$sub_name])) {
    $categories[$cat_name]['subs'][$sub_name] = [];
  }

  if ($keywords !== '') {
    $kw_arr = array_map('trim', explode(',', $keywords));
    $categories[$cat_name]['subs'][$sub_name] = array_merge($categories[$cat_name]['subs'][$sub_name], $kw_arr);
  }
}
$stmt->close();

$path = "../"; 
$page_title = "Category List";
include '../master_layout/header.php';
?>

<div class="mt-0">
  <div class="card-header p-2 bg-info text-white">
    <div class="container d-flex justify-content-between">
      <h5 class="mb-0">Category Details</h5>
      <a href="manage_categories.php" class="btn btn-light btn-sm"> Back</a>
    </div>
  </div>
</div>  

<div class="container">
  <h2 class="mt-4">ক্যাটাগরি, সাব-ক্যাটাগরি এবং কীওয়ার্ড</h2>
  <hr>

  <?php foreach ($categories as $cat_name => $cat_data): ?>
    <?php foreach ($cat_data['subs'] as $sub_name => $keywords): ?>
      <div>
        <?= htmlspecialchars($cat_data['serial_no']) . ". " ?>
        <?= htmlspecialchars($cat_name) ?> 
        <?= " => " ?>
        <?= ($sub_name === 'none' || $sub_name === '' 
              ? "<span class='text-muted'>[কোনো সাব-ক্যাটাগরি নাই]</span>" 
              : htmlspecialchars($sub_name)) ?>
        <?= " -> " ?>

        <?php if (empty($keywords)): ?>
          <span class='text-white bg-dark p-1 rounded'>কোনো কীওয়ার্ড নাই</span>
        <?php else: ?>
          <?= implode(', ', array_map('htmlspecialchars', $keywords)) ?>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
    <br>
  <?php endforeach; ?>

  <hr>
  <h5>Debugging View (Array Format)</h5>
  <pre><?php print_r($categories); ?></pre>
</div>

<?php include '../master_layout/footer.php'; ?>
