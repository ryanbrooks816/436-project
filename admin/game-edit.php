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

// Fetch game info
$sql = "
  SELECT g.*, 
         GROUP_CONCAT(DISTINCT f.feature_name) AS features,
         GROUP_CONCAT(DISTINCT pl.platform_name) AS platforms,
         GROUP_CONCAT(DISTINCT cat.cat_name) AS categories
  FROM Games g
  LEFT JOIN Game_Accessibility_Features af ON g.game_id = af.game_id
  LEFT JOIN Accessibility_Features f ON af.feature_id = f.feature_id
  LEFT JOIN Game_Platforms gp ON g.game_id = gp.game_id
  LEFT JOIN Platforms pl ON gp.platform_id = pl.platform_id
  LEFT JOIN Game_Categories gc ON g.game_id = gc.game_id
  LEFT JOIN Categories cat ON gc.cat_id = cat.cat_id
  WHERE g.game_id = :game_id
  GROUP BY g.game_id
";

$stmt = $pdo->prepare($sql);
$stmt->execute([':game_id' => $game_id]);
$game = $stmt->fetch(PDO::FETCH_ASSOC);

$feature_stmt = $pdo->query("SELECT * FROM Accessibility_Features ORDER BY feature_name ASC");
$all_features = $feature_stmt->fetchAll();

$current_feature_stmt = $pdo->prepare("SELECT feature_id FROM Game_Accessibility_Features WHERE game_id = ?");
$current_feature_stmt->execute([$game_id]);
$current_feature_ids = array_column($current_feature_stmt->fetchAll(), 'feature_id');

if (!$game) {
    die("Game not found.");
}

// Fetch all categories
$category_stmt = $pdo->query("SELECT * FROM Categories ORDER BY cat_name ASC");
$all_categories = $category_stmt->fetchAll();

// Fetch all platforms
$platform_stmt = $pdo->query("SELECT * FROM Platforms ORDER BY platform_name ASC");
$all_platforms = $platform_stmt->fetchAll();

// Fetch all publishers
$publisher_stmt = $pdo->query("SELECT * FROM Publishers ORDER BY publisher_name ASC");
$publishers = $publisher_stmt->fetchAll();


// Get current category IDs for this game
$current_category_stmt = $pdo->prepare("SELECT cat_id FROM Game_Categories WHERE game_id = ?");
$current_category_stmt->execute([$game_id]);
$current_category_ids = array_column($current_category_stmt->fetchAll(), 'cat_id');

// Get current platform IDs for this game
$current_platform_stmt = $pdo->prepare("SELECT platform_id FROM Game_Platforms WHERE game_id = ?");
$current_platform_stmt->execute([$game_id]);
$current_platform_ids = array_column($current_platform_stmt->fetchAll(), 'platform_id');

// Get current publisher IDs for this game
$pub_stmt = $pdo->prepare("SELECT publisher_id FROM Game_Publishers WHERE game_id = ?");
$pub_stmt->execute([$game_id]);
$current_publisher_ids = array_column($pub_stmt->fetchAll(), 'publisher_id');


// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $game_name = $_POST['game_name'] ?? '';
    $game_rating = $_POST['game_rating'] ?? null;
    $release_date = $_POST['game_release_date'] ?? null;
    $new_category_ids = $_POST['category_ids'] ?? [];
    $new_platform_ids = $_POST['platform_ids'] ?? [];
    $new_publisher_ids = $_POST['publisher_ids'] ?? [];

    $update_sql = "UPDATE Games 
    SET game_name = :game_name, 
        game_rating = :game_rating, 
        game_release_date = :game_release_date 
    WHERE game_id = :game_id";
    
    $stmt = $pdo->prepare($update_sql);
    $stmt->execute([
        ':game_name' => $game_name,
        ':game_rating' => $game_rating,
        ':game_release_date' => $game_release_date,
        ':game_id' => $game_id
    ]);

    // Update Game_Categories join table
    $pdo->prepare("DELETE FROM Game_Categories WHERE game_id = ?")->execute([$game_id]);
    $insert_category = $pdo->prepare("INSERT INTO Game_Categories (game_id, cat_id) VALUES (?, ?)");
    foreach ($new_category_ids as $cid) {
        $insert_category->execute([$game_id, $cid]);
    }

    // Update Game_Platforms join table
    $pdo->prepare("DELETE FROM Game_Platforms WHERE game_id = ?")->execute([$game_id]);
    $insert_platform = $pdo->prepare("INSERT INTO Game_Platforms (game_id, platform_id) VALUES (?, ?)");
    foreach ($new_platform_ids as $pid) {
        $insert_platform->execute([$game_id, $pid]);
    }

    // Update Game_Publishers join table
    $pdo->prepare("DELETE FROM Game_Publishers WHERE game_id = ?")->execute([$game_id]);
    $insert_pub = $pdo->prepare("INSERT INTO Game_Publishers (game_id, publisher_id) VALUES (?, ?)");
    foreach ($new_publisher_ids as $pid) {
        $insert_pub->execute([$game_id, $pid]);
    }

    $new_feature_ids = $_POST['feature_ids'] ?? [];

    // Clear existing features
    $pdo->prepare("DELETE FROM Game_Accessibility_Features WHERE game_id = ?")->execute([$game_id]);

    // Insert new feature links
    $insert_feature = $pdo->prepare("INSERT INTO Game_Accessibility_Features (game_id, feature_id) VALUES (?, ?)");
    foreach ($new_feature_ids as $fid) {
        $insert_feature->execute([$game_id, $fid]);
    }

    header("Location: admin/game-view.php?game_id=$game_id");
    exit;
}
?>

<?php require '../modules/admin-sidebar.php' ?>

<main class="page-wrapper">
    <div class="top-space bg-primary text-white text-center py-5">
        <h1 class="text-white">Edit Game Details</h1>
    </div>
    <div class="after-sidebar-content">
        <div class="container py-5">
            <div class="card">
                <div class="card shadow">
                    <div class="card-header">
                        <h1 class="mb-0">Edit Game: <?= htmlspecialchars($game['game_name']) ?></h1>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="form-group">
                                <label for="game_name" class="form-label">Title</label>
                                <input type="text" class="form-control-input" name="game_name"
                                    value="<?= htmlspecialchars($game['game_name']) ?>">
                            </div>
                            <div class="row">
                                <div class="col-6 form-group">
                                    <label for="game_rating" class="form-label">Game Rating</label>
                                    <input type="text" class="form-control-input" name="game_rating"
                                        value="<?= htmlspecialchars($game['game_rating']) ?>">
                                </div>
                                <div class="col-6 form-group">
                                    <label for="release_date" class="form-label">Release Date</label>
                                    <input type="date" class="form-control-input" name="release_date"
                                        value="<?= htmlspecialchars($game['game_release_date']) ?>">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="game_name" class="form-label">Accessibility Features</label>
                                <select name="feature_ids[]" class="form-select select2-multi" multiple>
                                    <?php foreach ($all_features as $feature): ?>
                                        <option value="<?= $feature['feature_id'] ?>" <?= in_array($feature['feature_id'], $current_feature_ids) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($feature['feature_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="category_ids" class="form-label">Categories</label>
                                <select name="category_ids[]" class="form-select select2-multi" multiple>
                                    <?php foreach ($all_categories as $cat): ?>
                                        <option value="<?= $cat['cat_id'] ?>" <?= in_array($cat['cat_id'], $current_category_ids) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['cat_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="platform_ids" class="form-label">Platforms</label>
                                <select name="platform_ids[]" class="form-select select2-multi" multiple>
                                    <?php foreach ($all_platforms as $plat): ?>
                                        <option value="<?= $plat['platform_id'] ?>" <?= in_array($plat['platform_id'], $current_platform_ids) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($plat['platform_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="game_name" class="form-label">Publishers</label>
                                <select name="publisher_ids[]" class="form-select select2-multi" multiple>
                                    <?php foreach ($publishers as $pub): ?>
                                        <option value="<?= $pub['publisher_id'] ?>" <?= in_array($pub['publisher_id'], $current_publisher_ids) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($pub['publisher_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="text-end">
                                <a href="game-view.php?game_id=<?= urlencode($game['game_id']) ?>"
                                    class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">Update Game</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require '../footer.php'; ?>

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function () {
        // Initialize Select2 for all multi-select elements
        $('.select2-multi').select2({
            placeholder: "Select options...",
            width: '100%',
            allowClear: true
        });
    });
</script>