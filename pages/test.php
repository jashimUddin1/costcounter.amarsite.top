<?php
session_start();
include("../db/dbcon.php");

// ইউজার লগইন না করলে রিডাইরেক্ট করো
if (!isset($_SESSION['authenticated'])) {
    header("Location: ../login/index.php");
    exit();
}

$user_id = $_SESSION['auth_user']['id'] ?? null;


$categories = [];
$stmt = $con->prepare("SELECT id, category_name, category_keywords FROM categories WHERE user_id = ? ");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
  $categories[$row['category_name']] = $row;
}
$stmt->close();

// --- Fetch category groups ---
$category_groups = [];
$stmt = $con->prepare("SELECT * FROM category_groups WHERE user_id = ? ORDER BY id");
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
<div class="container">
<div class="col-md-2">
      <label class="form-label">নির্বাচন করুন</label>
      <select name="category" class="form-select" required>

        <option value="" disabled selected>ক্যাটাগরি দিন</option>
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


<?php
$categories = [];
$stmt = $con->prepare("SELECT id, category_name, category_keywords FROM categories WHERE user_id = ? ");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
  $categories[$row['category_name']] = $row;
}
$stmt->close();

include '../includes/header.php';
?>

<?php foreach ($categories as $cat_name => $row): ?>
  <tr>
    <td class="text-start word-wrap">
      <?= htmlspecialchars($cat_name) . " => " ?>
    </td>
    <td class="text-start word-wrap">
      <?php
      $cats = trim($row['category_keywords']);
      if ($cats === '') {
        echo "<span class='text-white bg-dark p-1 rounded'>কোনো কীওয়ার্ড নাই</span><br>";
      } else {
        // কমা দিয়ে আলাদা করো
        $keywords = array_map('trim', explode(',', $cats));
        $formatted = [];
        foreach ($keywords as $kw) {
          $formatted[] =  htmlspecialchars($kw);
        }
        echo  implode(", ", $formatted) ."<br>";
      }
      ?>
    </td>
  </tr>
<?php endforeach; ?>

<?php
$category_map = [];  // খালি array

$stmt = $con->prepare("SELECT category_name, category_keywords FROM categories WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

while ($row = $res->fetch_assoc()) {
    $cat_name = trim($row['category_name']);
    $cats = trim($row['category_keywords']);

    if ($cats !== '') {
        $keywords = array_map('trim', explode(',', $cats));
        $category_map[$cat_name] = $keywords;
    } else {
        $category_map[$cat_name] = []; // কীওয়ার্ড না থাকলে খালি array
    }
}

$stmt->close();

// শুধু টেস্ট করার জন্য প্রিন্ট করো
echo "<hr><pre>";
print_r($category_map);
echo "</pre>";
"</div>";
include '../includes/footer.php';
?>
