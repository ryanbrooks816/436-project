<?php
require '../header.php';
require_once "../modules/require-login.php";

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'employee') {
  header("Location: 403.php");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $entryType = $_POST['entry_type'];
  $entryName = trim($_POST['entry_name']);

  if (!empty($entryType) && !empty($entryName)) {
    if ($entryType !== 'accessibility_feature') {
      try {
        $stmt = $pdo->prepare("INSERT INTO $entryType (name) VALUES (:name)");
        $stmt->execute(['name' => $entryName]);
        $success = "New $entryType added successfully!";
      } catch (PDOException $e) {
        $error = "Error adding entry: " . $e->getMessage();
      }
    } else {
      $featureCategory = $_POST['feature_category'] ?? '';
      if (empty($featureCategory)) {
        $error = "Feature Category is required for Accessibility Features.";
      } else {
        try {
          $stmt = $pdo->prepare("INSERT INTO $entryType (name, feature_category) VALUES (:name, :category)");
          $stmt->execute(['name' => $entryName, 'category' => $featureCategory]);
          $success = "New Accessibility Feature added successfully!";
        } catch (PDOException $e) {
          $error = "Error adding entry: " . $e->getMessage();
        }
      }
    }
  } else {
    $error = "Please fill out all fields.";
  }
}
?>

<?php require '../modules/admin-sidebar.php' ?>

<main class="page-wrapper">
  <div class="top-space bg-primary text-white text-center py-5">
    <h1 class="text-white">Manage Database Entries</h1>
  </div>
  <div class="after-sidebar-content">
    <div class="container py-5">
      <div class="card">
        <div class="card-body">
          <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
          <?php elseif (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
          <?php endif; ?>

          <form method="POST" action="">
            <div class="form-group">
              <label for="entry_type" class="form-label">Entry Type</label>
              <select class="form-select form-control-input" id="entry_type" name="entry_type"
                onchange="toggleCategoryField()" required>
                <option value="">Select Type</option>
                <option value="categories">Category</option>
                <option value="publishers">Publisher</option>
                <option value="platforms">Platform</option>
                <option value="accessibility_features">Accessibility Feature</option>
              </select>
            </div>

            <div class="form-group" id="feature_category_field" style="display: none;">
              <label for="feature_category" class="form-label">Feature Category</label>
              <select class="form-select" id="feature_category" name="feature_category">
                <option value="">Select Category</option>
                <?php
                try {
                  $stmt = $pdo->query("SELECT * FROM Accessibility_Feature_Categories");
                  $categories = $stmt->fetchAll();
                  error_log("Categories fetched: " . print_r($categories, true)); // Debugging line
                  foreach ($categories as $row) {
                    echo '<option value="' . htmlspecialchars($row['cat_name']) . '">' . htmlspecialchars($row['cat_name']) . '</option>';
                  }
                } catch (PDOException $e) {
                  echo '<option value="">Error loading categories</option>';
                }
                ?>
              </select>
            </div>

            <div class="form-group">
              <label for="entry_name" class="form-label">Entry Name</label>
              <input type="text" class="form-control-input" id="entry_name" name="entry_name" required>
            </div>

            <button type="submit" class="btn btn-primary">Add Entry</button>
          </form>
        </div>
      </div>

      <script>
        function toggleCategoryField() {
          const detailType = document.getElementById('entry_type').value;
          const featureCategoryField = document.getElementById('feature_category_field');

          if (detailType === 'accessibility_features') {
            featureCategoryField.style.display = 'block';
          } else {
            featureCategoryField.style.display = 'none';
          }
        }
      </script>
    </div>
  </div>
</main>

<?php require '../footer.php'; ?>