<?php
require '../header.php';
require_once "../modules/require-login.php";

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'employee') {
  header("Location: 403.php");
}

if (!isset($_GET['game_id'])) {
  die("No game ID provided.");
}

$game_id = $_GET['game_id'];

// Fetch game with joined info
$sql = "
  SELECT 
    g.game_id,
    g.game_name,
    g.game_rating,
    g.game_release_date,
    GROUP_CONCAT(DISTINCT af.feature_name ORDER BY af.feature_name SEPARATOR ', ') AS features,
    GROUP_CONCAT(DISTINCT pl.platform_name ORDER BY pl.platform_name SEPARATOR ', ') AS platforms,
    GROUP_CONCAT(DISTINCT cat.cat_name ORDER BY cat.cat_name SEPARATOR ', ') AS categories,
    GROUP_CONCAT(DISTINCT pub.publisher_name ORDER BY pub.publisher_name SEPARATOR ', ') AS publishers
  FROM Games g
  LEFT JOIN Game_Accessibility_Features gaf ON g.game_id = gaf.game_id
  LEFT JOIN Accessibility_Features af ON gaf.feature_id = af.feature_id
  LEFT JOIN Game_Platforms gp ON g.game_id = gp.game_id
  LEFT JOIN Platforms pl ON gp.platform_id = pl.platform_id
  LEFT JOIN Game_Categories gc ON g.game_id = gc.game_id
  LEFT JOIN Categories cat ON gc.cat_id = cat.cat_id
  LEFT JOIN Game_Publishers gpub ON g.game_id = gpub.game_id
  LEFT JOIN Publishers pub ON gpub.publisher_id = pub.publisher_id
  WHERE g.game_id = :game_id
  GROUP BY g.game_id, g.game_name, g.game_rating
";

$stmt = $pdo->prepare($sql);
$stmt->execute([':game_id' => $game_id]);
$game = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$game) {
  die("Game not found.");
}
?>

<?php require '../modules/admin-sidebar.php' ?>

<main class="page-wrapper">
  <div class="top-space bg-primary text-white text-center py-5">
    <h1 class="text-white">View Game Details</h1>
  </div>
  <div class="after-sidebar-content">
    <div class="container py-5">
      <div class="card">
        <div class="card shadow">
          <div class="card-header">
            <h1 class="mb-0"><?= htmlspecialchars($game['game_name']) ?></h1>
          </div>
          <div class="card-body">
            <div class="mb-3">
              <strong>Rating:</strong><br>
              <?= htmlspecialchars($game['game_rating'] ?? 'Unrated') ?>
            </div>

            <div class="mb-3">
              <strong>Accessibility Features:</strong><br>
              <?php
              // Query to fetch features grouped by their categories
              $featureSql = "
              SELECT 
                afc.cat_name,
                GROUP_CONCAT(af.feature_name ORDER BY af.feature_name SEPARATOR ', ') AS features
              FROM Accessibility_Features af
              INNER JOIN Accessibility_Feature_Categories afc ON af.feature_cat_id = afc.cat_id
              INNER JOIN Game_Accessibility_Features gaf ON af.feature_id = gaf.feature_id
              WHERE gaf.game_id = :game_id
              GROUP BY afc.cat_name
              ORDER BY afc.cat_name
              ";

              $featureStmt = $pdo->prepare($featureSql);
              $featureStmt->execute([':game_id' => $game_id]);
              $featureCategories = $featureStmt->fetchAll(PDO::FETCH_ASSOC);
              ?>

              <?php if ($featureCategories): ?>
                <ul>
                  <?php foreach ($featureCategories as $category): ?>
                    <li>
                      <strong><?= htmlspecialchars($category['cat_name']) ?>:</strong>
                      <ul>
                        <?php foreach (explode(', ', $category['features']) as $feature): ?>
                          <li><?= htmlspecialchars($feature) ?></li>
                        <?php endforeach; ?>
                      </ul>
                    </li>
                  <?php endforeach; ?>
                </ul>
              <?php else: ?>
                None listed
              <?php endif; ?>
            </div>

            <div class="mb-3">
              <strong>Platforms:</strong><br>
              <?php if ($game['platforms']): ?>
                <ul>
                  <?php foreach (explode(', ', $game['platforms']) as $platform): ?>
                    <li><?= htmlspecialchars($platform) ?></li>
                  <?php endforeach; ?>
                </ul>
              <?php else: ?>
                None listed
              <?php endif; ?>
            </div>

            <div class="mb-3">
              <strong>Categories:</strong><br>
              <?php if ($game['categories']): ?>
                <ul>
                  <?php foreach (explode(', ', $game['categories']) as $category): ?>
                    <li><?= htmlspecialchars($category) ?></li>
                  <?php endforeach; ?>
                </ul>
              <?php else: ?>
                None listed
              <?php endif; ?>
            </div>

            <div class="mb-3">
              <strong>Publishers:</strong><br>
              <?php if ($game['publishers']): ?>
                <ul>
                  <?php foreach (explode(', ', $game['publishers']) as $publisher): ?>
                    <li><?= htmlspecialchars($publisher) ?></li>
                  <?php endforeach; ?>
                </ul>
              <?php else: ?>
                None listed
              <?php endif; ?>
            </div>

            <div class="mb-3">
              <strong>Release Date:</strong><br>
              <?= $game['game_release_date'] ? htmlspecialchars($game['game_release_date']) : 'Unknown' ?>
            </div>
            <div class="card-footer text-end pt-3">
              <a href="../game-list.php" class="btn btn-secondary m-0 ms-2">Back to List</a>
              <a href="game-edit.php?game_id=<?= urlencode($game['game_id']) ?>" class="btn btn-primary m-0">Edit
                Game</a>
            </div>
          </div>
        </div>
      </div>
    </div>
</main>

<?php require '../footer.php' ?>