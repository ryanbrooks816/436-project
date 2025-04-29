<?php
session_start();
require '../header.php';

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
        }
        else {
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

<!-- Sidebar -->
<div class="sidebar">
  <a href="dashboard.php">Dashboard</a>
  <a href="game-list.php">Manage Games</a>
  <a href="manage-game-details.php">Manage Game Details</a>
  <a href="manage-users.php">Manage Users</a>
  <a href="my-tickets.php">Tickets</a>
</div>

<div class="container mt-5" id='main-content'>
  <h1>Manage Database Entries</h1>

  <?php if (isset($success)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
  <?php elseif (isset($error)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>

  <form method="POST" action="">
    <div class="mb-3">
      <label for="entry_type" class="form-label">Entry Type</label>
      <select class="form-select" id="entry_type" name="entry_type" onchange="toggleCategoryField()" required>
        <option value="">Select Type</option>
        <option value="categories">Category</option>
        <option value="publishers">Publisher</option>
        <option value="platforms">Platform</option>
        <option value="accessibility_features">Accessibility Feature</option>
      </select>
    </div>

    <div class="mb-3" id="feature_category_field" style="display: none;">
    <label for="feature_category" class="form-label">Feature Category</label>
    <select class="form-select" id="feature_category" name="feature_category">
      <option value="">Select Category</option>
      <option value="Color">Color</option>
      <option value="Auditory">Auditory</option>
      <option value="Fine Motor">Fine Motor</option>
      <option value="General">General</option>
      <option value="General">Visual</option>
    </select>
  </div>

    <div class="mb-3">
      <label for="entry_name" class="form-label">Entry Name</label>
      <input type="text" class="form-control" id="entry_name" name="entry_name" required>
    </div>

    <button type="submit" class="btn btn-primary">Add Entry</button>
  </form>

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

<?php 
  require '../footer.php';
?>
