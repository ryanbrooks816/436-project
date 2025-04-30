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

<style>
    .select2-container--default .select2-selection--multiple {
        min-height: 55px;
        display: flex;
        align-items: center;
        width: 100%;
        /* padding: 0.5rem 1.25rem; */
        border: 1px solid #c4d8dc;
        border-radius: 0.25rem;
        background-color: #fff;
        color: #555;
        font-weight: 400;
        font-size: 0.875rem;
        line-height: 1.875rem;
        font-family: "Open Sans", sans-serif;
        transition: all 0.2s;
        -webkit-appearance: none;
    }

    .select2-container--default .select2-search--inline .select2-search__field {
        height: 55px;
        line-height: 55px;
    }

    .select2-container .select2-selection--multiple .select2-selection__rendered {
        margin: 0;
    }

    .select2-container--default .select2-selection--multiple:hover {
        border: 1px solid #1a1a1a;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: var(--primary);
        border: var(--primary);
        color: #fff;
        font-family: "Open Sans", sans-serif;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice:hover,
    .select2-container--default .select2-selection--multiple .select2-selection__choice:focus {
        border: 1px solid var(--primary-dark);
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        border: var(--primary);
        color: #fff;
        font-size: 1.4em;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover, .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:focus {
        border-color: var(--primary-dark);
        background-color: var(--primary-dark);
        color: #fff;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__clear {
        font-size: 1.4em;
    }
</style>

<main class="page-wrapper">
    <div class="top-space header-margin text-center py-5"
        style="background: linear-gradient(to right, #4e54c8,rgb(92, 99, 240));">
        <h1 class="text-white display-4 fw-bold mb-3">Accessible Games Database</h1>
        <p class="text-white lead mb-4">Find games with accessibility features that match your needs</p>
        <div class="mt-5 d-flex justify-content-center">
            <form method="GET" action="games-homepage.php" class="d-flex w-50">
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
                    <form method="GET" action="games-homepage.php" class="row g-3">
                        <!-- If search was performed, keep the search parameter -->
                        <?php if ($search_filter): ?>
                            <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_filter); ?>">
                        <?php endif; ?>
                        <div class="col-md-4">
                            <div class="mb-3">
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
                        </div>
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
                            <select name="rating[]" id="rating" class="form-select select2-multi" multiple="multiple">
                                <?php foreach ($ratings as $rating): ?>
                                    <option value="<?= htmlspecialchars($rating['game_rating']); ?>"
                                        <?= in_array($rating['game_rating'], $rating_filters) ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($rating['game_rating']) . " and above"; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 text-center mt-4">
                            <button type="submit" class="btn btn-primary">Apply Filters</button>
                            <a href="games-homepage.php" class="btn btn-outline-secondary ms-2">Reset Filters</a>
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
                <p class="text-muted">No games found.</p>
            <?php else: ?>
                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4" id="game-list">
                    <?php foreach ($games as $game): ?>
                        <div class="col">
                            <div class="card h-100 d-flex flex-column">
                                <img src="images/placeholder.jpg" class="card-img-top"
                                    alt="Placeholder image for <?php echo htmlspecialchars($game['game_name']); ?>">
                                <div class="card-body d-flex flex-column justify-content-between">
                                    <h5 class="card-title d-flex justify-content-between align-items-start" style="gap: 8px;">
                                        <?php echo htmlspecialchars($game['game_name']); ?>
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-star-fill"></i>
                                            <?php echo htmlspecialchars($game['game_rating'] ?? 'N/A'); ?>
                                        </span>
                                    </h5>
                                    <p class="card-text">
                                        Released:
                                        <?php echo htmlspecialchars(date('F j, Y', strtotime($game['game_release_date'] ?? '1970-01-01'))); ?><br>
                                    </p>
                                    <div class="mt-2">
                                        <a href="game-description.php?game_id=<?php echo htmlspecialchars($game['game_id']); ?>"
                                            class="btn btn-primary btn-sm w-100">View Details</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
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