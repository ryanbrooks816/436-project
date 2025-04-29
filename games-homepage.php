<?php
session_start();

// Include database connection
require_once 'classes/db.php';

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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accessible Games Database - Games Homepage</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- Custom CSS -->
    <link href="css/styles.css" rel="stylesheet">
    <style>
        /* Custom styles for Select2 to match Bootstrap */
        .select2-container--default .select2-selection--multiple {
            border: 1px solid #ced4da;
            min-height: 38px;
            border-radius: 0.25rem;
        }

        .select2-container--default.select2-container--focus .select2-selection--multiple {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #0d6efd;
            border: 1px solid #0d6efd;
            color: white;
            border-radius: 0.2rem;
            padding: 2px 8px;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: #fff;
            margin-right: 5px;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
            color: #f8f9fa;
        }
    </style>
</head>
<body class="games-homepage">
    <?php include 'header.php'; ?>

    <div class="container mt-4">
        <h1 class="mb-4 text-center">Accessible Games Database</h1>

        <!-- Search Bar -->
        <div class="mb-4">
            <form method="GET" action="games-homepage.php" class="d-flex">
                <input type="text" name="search" class="form-control me-2" placeholder="Search games by name..." value="<?php echo htmlspecialchars($search_filter); ?>">
                <button type="submit" class="btn btn-primary">Search</button>
            </form>
        </div>

        <!-- Filter Form -->
        <div class="mb-4 filter-form">
            <form method="GET" action="games-homepage.php" class="row g-3">
                <!-- If search was performed, keep the search parameter -->
                <?php if ($search_filter): ?>
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_filter); ?>">
                <?php endif; ?>
                
                <div class="col-md-4">
                    <label for="accessibility" class="form-label">Accessibility Features</label>
                    <select name="accessibility[]" id="accessibility" class="form-select select2-multi" multiple="multiple">
                        <?php foreach ($accessibility_features as $feature): ?>
                            <option value="<?php echo htmlspecialchars($feature['Feature_Name']); ?>" 
                                    <?php echo in_array($feature['Feature_Name'], $accessibility_filters) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($feature['Feature_Name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="platform" class="form-label">Platforms</label>
                    <select name="platform[]" id="platform" class="form-select select2-multi" multiple="multiple">
                        <?php foreach ($platforms as $platform): ?>
                            <option value="<?php echo htmlspecialchars($platform['Platform_Name']); ?>" 
                                    <?php echo in_array($platform['Platform_Name'], $platform_filters) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($platform['Platform_Name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="rating" class="form-label">Game Ratings</label>
                    <select name="rating[]" id="rating" class="form-select select2-multi" multiple="multiple">
                        <?php foreach ($ratings as $rating): ?>
                            <option value="<?php echo htmlspecialchars($rating['game_rating']); ?>" 
                                    <?php echo in_array($rating['game_rating'], $rating_filters) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($rating['game_rating']) . " and above"; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 text-center mt-3">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="games-homepage.php" class="btn btn-outline-secondary ms-2">Reset Filters</a>
                </div>
            </form>
        </div>

        <!-- Games Homepage -->
        <h3>All Games</h3>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error; ?>
            </div>
        <?php elseif (empty($games)): ?>
            <p class="text-muted">No games found.</p>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4" id="game-list">
                <?php foreach ($games as $game): ?>
                    <div class="col">
                        <div class="card h-100">
                            <img src="images/placeholder.jpg" class="card-img-top" alt="Placeholder image for <?php echo htmlspecialchars($game['game_name']); ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($game['game_name']); ?></h5>
                                <p class="card-text">
                                    Release: <?php echo htmlspecialchars($game['game_release_date'] ?? 'Unknown'); ?><br>
                                    Rating: <?php echo htmlspecialchars($game['game_rating'] ?? 'N/A'); ?>
                                </p>
                                <a href="game-description.php?game_id=<?php echo htmlspecialchars($game['game_id']); ?>" class="btn btn-primary">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'footer.php'; ?>

    <!-- jQuery (required for Select2) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Initialize Select2 for all multi-select elements
            $('.select2-multi').select2({
                placeholder: "Select options...",
                width: '100%',
                allowClear: true
            });
        });
    </script>
</body>
</html>