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
$stmt = $con->prepare("SELECT id, category_name, sub_category, category_keywords 
                       FROM categories WHERE user_id = ? ORDER BY serial_no ASC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

while ($row = $res->fetch_assoc()) {
  $cat_name = trim($row['category_name']);
  $sub_name = trim($row['sub_category']);
  $keywords = trim($row['category_keywords']);

  if (!isset($categories[$cat_name])) {
    $categories[$cat_name] = [];
  }

  if (!isset($categories[$cat_name][$sub_name])) {
    $categories[$cat_name][$sub_name] = [];
  }

  if ($keywords !== '') {
    $kw_arr = array_map('trim', explode(',', $keywords));
    $categories[$cat_name][$sub_name] = array_merge($categories[$cat_name][$sub_name], $kw_arr);
  }
}
$stmt->close();
?>

<div class="container">
  <h2 class="mt-4">ক্যাটাগরি, সাব-ক্যাটাগরি এবং কীওয়ার্ড</h2>
  <hr>

  <?php include '../includes/header.php'; ?>

  <table class="table table-bordered table-striped">
    <thead>
      <tr>
        <th>ক্যাটাগরি</th>
        <th>সাব-ক্যাটাগরি</th>
        <th>কীওয়ার্ড</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($categories as $cat_name => $subs): ?>
        <?php foreach ($subs as $sub_name => $keywords): ?>
          <tr>
            <td class="text-start"><?= htmlspecialchars($cat_name) ?></td>
            <td class="text-start">
              <?= ($sub_name === 'none' ? "<span class='text-muted'>[কোনো সাব-ক্যাটাগরি নাই]</span>" : htmlspecialchars($sub_name)) ?>
            </td>
            <td class="text-start">
              <?php if (empty($keywords)): ?>
                <span class='text-white bg-dark p-1 rounded'>কোনো কীওয়ার্ড নাই</span>
              <?php else: ?>
                <?= implode(', ', array_map('htmlspecialchars', $keywords)) ?>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endforeach; ?>
    </tbody>
  </table>

  <hr>
  <h5>Debugging View (Array Format)</h5>
  <pre><?php print_r($categories); ?></pre>
</div>

<?php include '../includes/footer.php'; ?>
