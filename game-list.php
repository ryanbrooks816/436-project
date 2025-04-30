<?php
require 'header.php';

// Fetch accessibility features for the filter
try {
    $sql = "SELECT DISTINCT af.Feature_Name 
            FROM Accessibility_Features af 
            JOIN Game_Accessibility_Features gaf ON af.Feature_ID = gaf.Feature_ID";
    $stmt = pdo($pdo, $sql);
    $accessibility_features = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error fetching accessibility features: " . htmlspecialchars($e->getMessage());
}

// Fetch publishers for the filter
try {
    $sql = "SELECT DISTINCT p.Publisher_Name 
            FROM Publishers p 
            JOIN Game_Publishers gp ON gp.Publisher_ID = p.Publisher_ID";
    $stmt = pdo($pdo, $sql);
    $publishers = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error fetching publishers: " . htmlspecialchars($e->getMessage());
}

// Fetch categories for the filter
try {
    $sql = "SELECT DISTINCT c.Cat_Name 
            FROM Categories c 
            JOIN Game_Categories gc ON c.Cat_ID = gc.Cat_ID";
    $stmt = pdo($pdo, $sql);
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error fetching categories: " . htmlspecialchars($e->getMessage());
}

// Fetch platforms for the filter
try {
    $sql = "SELECT DISTINCT p.Platform_Name 
            FROM Platforms p 
            JOIN Game_Platforms gp ON p.Platform_ID = gp.Platform_ID";
    $stmt = pdo($pdo, $sql);
    $platforms = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error fetching platforms: " . htmlspecialchars($e->getMessage());
}

// Fetch distinct ratings for the filter
try {
    $sql = "SELECT DISTINCT game_rating 
            FROM Games 
            WHERE game_rating IS NOT NULL 
            ORDER BY game_rating ASC";
    $stmt = pdo($pdo, $sql);
    $ratings = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error fetching ratings: " . htmlspecialchars($e->getMessage());
}

// Fetch games from the database with filters
$accessibility_filters = $_GET['accessibility'] ?? [];
$publisher_filters = $_GET['publisher'] ?? [];
$category_filters = $_GET['category'] ?? [];
$platform_filters = $_GET['platform'] ?? [];
$rating_filters = $_GET['rating'] ?? [];
$search_filter = $_GET['search'] ?? '';

try {
    $sql = "SELECT DISTINCT g.game_id, g.game_name, g.game_release_date, g.game_rating 
            FROM Games g";
    $params = [];
    $conditions = [];

    // Join with Game_Accessibility_Features and Accessibility_Features if accessibility filters are applied
    if (!empty($accessibility_filters)) {
        $sql .= " JOIN Game_Accessibility_Features gaf ON g.game_id = gaf.Game_ID 
                  JOIN Accessibility_Features af ON gaf.Feature_ID = af.Feature_ID";
        $placeholders = implode(',', array_fill(0, count($accessibility_filters), '?'));
        $conditions[] = "af.Feature_Name IN ($placeholders)";
        $params = array_merge($params, $accessibility_filters);
    }

    // Join with Game_Publishers and Publishers if publisher filters are applied
    if (!empty($publisher_filters)) {
        $sql .= " JOIN Game_Publishers gp ON g.game_id = gp.Game_ID 
                  JOIN Publishers p ON gp.Publisher_ID = p.Publisher_ID";
        $placeholders = implode(',', array_fill(0, count($publisher_filters), '?'));
        $conditions[] = "p.Publisher_Name IN ($placeholders)";
        $params = array_merge($params, $publisher_filters);
    }

    // Join with Game_Categories and Categories if category filters are applied
    if (!empty($category_filters)) {
        $sql .= " JOIN Game_Categories gc ON g.game_id = gc.Game_ID 
                  JOIN Categories c ON gc.Cat_ID = c.Cat_ID";
        $placeholders = implode(',', array_fill(0, count($category_filters), '?'));
        $conditions[] = "c.Cat_Name IN ($placeholders)";
        $params = array_merge($params, $category_filters);
    }

    // Join with Game_Platforms and Platforms if platform filters are applied
    if (!empty($platform_filters)) {
        $sql .= " JOIN Game_Platforms gp ON g.game_id = gp.Game_ID 
                  JOIN Platforms p ON gp.Platform_ID = p.Platform_ID";
        $placeholders = implode(',', array_fill(0, count($platform_filters), '?'));
        $conditions[] = "p.Platform_Name IN ($placeholders)";
        $params = array_merge($params, $platform_filters);
    }

    // Apply rating filters if selected
    if (!empty($rating_filters)) {
        $placeholders = implode(',', array_fill(0, count($rating_filters), '?'));
        $conditions[] = "g.game_rating IN ($placeholders)";
        $params = array_merge($params, $rating_filters);
    }

    // Apply search filter if provided
    if ($search_filter) {
        $conditions[] = "g.game_name LIKE ?";
        $params[] = "%" . $search_filter . "%";
    }

    // Combine conditions
    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }

    $stmt = pdo($pdo, $sql, $params);
    $games = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error fetching games: " . htmlspecialchars($e->getMessage());
}
?>

<main class="page-wrapper">
    <div class="top-space header-margin text-center py-5"
        style="background: linear-gradient(to right, #4e54c8,rgb(92, 99, 240));">
        <h1 class="text-white display-4 fw-bold mb-3">Accessible Games Database</h1>
        <p class="text-white lead mb-4">Find games with accessibility features that match your needs</p>
        <div class="mt-5 d-flex justify-content-center">
            <form method="GET" action="game-list.php" class="d-flex w-50">
                <div class="input-group shadow-sm">
                    <input type="text" name="search" class="form-control form-control-lg"
                        placeholder="Search games by name..." value="<?php echo htmlspecialchars($search_filter); ?>">
                    <button type="submit" class="btn btn-primary mb-0" style="border-radius: 0.3rem;">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
    <section class="bottom-space">
        <div class="container">
            <!-- Filter Form Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Filter Games</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="game-list.php" class="row g-3">
                        <!-- If search was performed, keep the search parameter -->
                        <?php if ($search_filter): ?>
                            <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_filter); ?>">
                        <?php endif; ?>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label for="accessibility" class="form-label">Accessibility Features</label>
                                <select name="accessibility[]" id="accessibility"
                                    class="form-select form-control-select select2-multi" multiple="multiple">
                                    <?php foreach ($accessibility_features as $feature): ?>
                                        <option value="<?= htmlspecialchars($feature['Feature_Name']) ?>"
                                            <?= in_array($feature['Feature_Name'], $accessibility_filters) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($feature['Feature_Name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="category" class="form-label">Categories</label>
                                <select name="category[]" id="category" class="form-select select2-multi"
                                    multiple="multiple">
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= htmlspecialchars($category['Cat_Name']); ?>"
                                            <?= in_array($category['Cat_Name'], $category_filters) ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($category['Cat_Name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-4">
                                <label for="platform" class="form-label">Platforms</label>
                                <select name="platform[]" id="platform" class="form-select select2-multi"
                                    multiple="multiple">
                                    <?php foreach ($platforms as $platform): ?>
                                        <option value="<?= htmlspecialchars($platform['Platform_Name']); ?>"
                                            <?= in_array($platform['Platform_Name'], $platform_filters) ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($platform['Platform_Name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="rating" class="form-label">Game Ratings</label>
                                <select name="rating[]" id="rating" class="form-select select2-multi"
                                    multiple="multiple">
                                    <?php foreach ($ratings as $rating): ?>
                                        <option value="<?= htmlspecialchars($rating['game_rating']); ?>"
                                            <?= in_array($rating['game_rating'], $rating_filters) ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($rating['game_rating']) . " and above"; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="publisher" class="form-label">Publishers</label>
                                <select name="publisher[]" id="publisher" class="form-select select2-multi"
                                    multiple="multiple">
                                    <?php foreach ($publishers as $publisher): ?>
                                        <option value="<?= htmlspecialchars($publisher['Publisher_Name']); ?>"
                                            <?= in_array($publisher['Publisher_Name'], $publisher_filters) ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($publisher['Publisher_Name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12 text-center mt-4">
                                <button type="submit" class="btn btn-primary">Apply Filters</button>
                                <a href="game-list.php" class="btn btn-outline-secondary ms-2">Reset Filters</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Games Homepage -->
            <div class="d-flex justify-content-between align-items-center mb-4" style="padding-top: 80px;">
                <h2>All Games <span class="badge bg-secondary"><?php echo count($games); ?></span></h2>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error; ?>
                </div>
            <?php elseif (empty($games)): ?>
                <div class="d-flex justify-content-center align-items-center">
                    <div class="text-center">
                        <div>
                            <h3 class="card-title">No Games Found</h3>
                            <p class="card-text text-muted">Try adjusting your filters or search criteria.</p>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-5" id="game-list">
                    <?php foreach ($games as $game): ?>
                        <div class="col">
                            <div class="card h-100 d-flex flex-column">
                                <img src="images/placeholder.jpg" class="card-img-top" style="height: 200px; object-fit: cover;"
                                    alt="Placeholder image for <?php echo htmlspecialchars($game['game_name']); ?>">
                                <div class="card-body d-flex flex-column justify-content-between">
                                    <h5 class="card-title d-flex justify-content-between align-items-start mb-3"
                                        style="gap: 8px;">
                                        <?php echo htmlspecialchars($game['game_name']); ?>
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-star-fill"></i>
                                            <?php echo htmlspecialchars($game['game_rating'] ?? 'N/A'); ?>
                                        </span>
                                    </h5>
                                    <p class="card-text">
                                        Platforms:
                                        <?php
                                        // Fetch platforms for the game
                                        $sql = "SELECT p.Platform_Name 
                                                    FROM Platforms p 
                                                    JOIN Game_Platforms gp ON p.Platform_ID = gp.Platform_ID 
                                                    WHERE gp.Game_ID = ?";
                                        $stmt = pdo($pdo, $sql, [$game['game_id']]);
                                        $platforms = $stmt->fetchAll(PDO::FETCH_COLUMN);
                                        foreach ($platforms as $index => $platform) {
                                            echo '<span class="badge bg-secondary me-1">' . htmlspecialchars($platform) . '</span>';
                                            if ($index === 0 && count($platforms) > 1) {
                                                echo '<span class="badge bg-secondary">+ ' . (count($platforms) - 1) . '</span>';
                                                break;
                                            }
                                        }
                                        ?><br>
                                        Categories:
                                        <?php
                                        // Fetch categories for the game
                                        $sql = "SELECT c.Cat_Name 
                                                    FROM Categories c 
                                                    JOIN Game_Categories gc ON c.Cat_ID = gc.Cat_ID 
                                                    WHERE gc.Game_ID = ?";
                                        $stmt = pdo($pdo, $sql, [$game['game_id']]);
                                        $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
                                        foreach ($categories as $index => $category) {
                                            echo '<span class="badge bg-info me-1">' . htmlspecialchars($category) . '</span>';
                                            if ($index === 0 && count($categories) > 1) {
                                                echo '<span class="badge bg-info">+ ' . (count($categories) - 1) . '</span>';
                                                break;
                                            }
                                        }
                                        ?>
                                    </p>
                                    <div class="mt-4">
                                        <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'employee'): ?>
                                            <a href="admin/game-view.php?game_id=<?php echo htmlspecialchars($game['game_id']); ?>" 
                                                class="btn btn-primary btn-sm w-100">Edit / View Details</a>
                                        <?php else: ?>
                                            <a href="game-description.php?game_id=<?php echo htmlspecialchars($game['game_id']); ?>" 
                                                class="btn btn-primary btn-sm w-100">View Details</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'employee'): ?>
                <a href="admin/game-create.php" class="btn btn-primary position-fixed bottom-0 end-0 m-4 shadow"
                    style="font-size: 1.2rem;">
                    + Add New Game
                </a>
            <?php endif; ?>
        </div>
</main>

<?php require 'footer.php'; ?>

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