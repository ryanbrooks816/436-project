<?php
require '../classes/db.php';

// Get search and filter inputs
$search = $_GET['search'] ?? '';
$feature_filter = $_GET['feature'] ?? '';
$publisher_filter = $_GET['publisher'] ?? '';
$rating_filter = $_GET['rating'] ?? '';

// Get dropdown data
$features = $pdo->query("SELECT feature_id, feature_name FROM Accessibility_Features ORDER BY feature_name ASC")->fetchAll();
$publishers = $pdo->query("SELECT publisher_id, publisher_name FROM Publishers ORDER BY publisher_name ASC")->fetchAll();
$ratings = $pdo->query("SELECT DISTINCT game_rating FROM Games WHERE game_rating IS NOT NULL ORDER BY game_rating ASC")->fetchAll(PDO::FETCH_COLUMN);

// Prepare base query
$sql = "
  SELECT g.game_id, g.game_name
  FROM Games g
  LEFT JOIN Game_Accessibility_Features gaf ON g.game_id = gaf.game_id
  LEFT JOIN Accessibility_Features af ON gaf.feature_id = af.feature_id
  LEFT JOIN Game_Publishers gp ON g.game_id = gp.game_id
  LEFT JOIN Publishers p ON gp.publisher_id = p.publisher_id
  WHERE 1=1
";

$params = [];

// Apply filters
if (!empty($search)) {
  $sql .= " AND g.game_name LIKE :search";
  $params[':search'] = "%$search%";
}
if (!empty($feature_filter)) {
  $sql .= " AND af.feature_id = :feature_id";
  $params[':feature_id'] = $feature_filter;
}
if (!empty($publisher_filter)) {
  $sql .= " AND p.publisher_id = :publisher_id";
  $params[':publisher_id'] = $publisher_filter;
}
if (!empty($rating_filter)) {
  $sql .= " AND g.game_rating = :game_rating";
  $params[':game_rating'] = $rating_filter;
}

$sql .= " GROUP BY g.game_id, g.game_name ORDER BY g.game_name ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$games = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Accessible Games</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<?php include '../header.php'; ?>

<body class="bg-light">
  <div class="container py-5">
    <h1 class="mb-4 text-center">Accessible Games</h1>

    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-3">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" class="form-control" placeholder="Search by game name">
        </div>

        <div class="col-md-3">
            <select name="feature" class="form-select">
            <option value="">All Accessibility Features</option>
            <?php foreach ($features as $feature): ?>
                <option value="<?= $feature['feature_id'] ?>" <?= $feature_filter == $feature['feature_id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($feature['feature_name']) ?>
                </option>
            <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-3">
            <select name="publisher" class="form-select">
            <option value="">All Publishers</option>
            <?php foreach ($publishers as $pub): ?>
                <option value="<?= $pub['publisher_id'] ?>" <?= $publisher_filter == $pub['publisher_id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($pub['publisher_name']) ?>
                </option>
            <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-2">
            <select name="rating" class="form-select">
            <option value="">All Ratings</option>
            <?php foreach ($ratings as $rating): ?>
                <option value="<?= $rating ?>" <?= $rating_filter == $rating ? 'selected' : '' ?>>
                <?= htmlspecialchars($rating) ?>
                </option>
            <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-1">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
        </form>

    <?php if (count($games) === 0): ?>
      <div class="alert alert-warning text-center">No games found.</div>
    <?php else: ?>
      <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php foreach ($games as $game): ?>
          <div class="col">
            <a href="/436-project/admin/game-view.php?game_id=<?= urlencode($game['game_id']) ?>" class="text-decoration-none text-dark">
              <div class="card h-100 shadow-sm border-primary">
                <div class="card-body text-center">
                  <h5 class="card-title"><?= htmlspecialchars($game['game_name']) ?></h5>
                </div>
              </div>
            </a>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <a href="/436-project/admin/dashboard.php" class="btn btn-outline-secondary position-fixed start-0 bottom-0 m-4 shadow rounded-pill"
        style="width: 140px; height: 50px; font-weight: bold; text-align: center; line-height: 2.2;">
        Home
    </a>

    <a href="/436-project/admin/game-create.php" class="btn btn-success position-fixed bottom-0 end-0 m-4 shadow rounded-circle" 
        title="Add New Game" style="width: 60px; height: 60px; font-size: 24px; text-align: center; line-height: 1.8;">
        +
    </a>
  </div>
</body>

<?php include '../footer.php'; ?>

</html>





