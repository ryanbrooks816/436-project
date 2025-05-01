<?php
require 'header.php';

// Check if game_id is provided
if (!isset($_GET['game_id']) || empty($_GET['game_id'])) {
    header('Location: game-list.php');
    exit;
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

<style>
    .game-container {
        background-color: white;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        padding: 30px;
        margin-bottom: 30px;
    }

    .card {
        border: none;
        border-radius: 12px;
        background-color: white;
    }

    .card:hover {
        box-shadow: 0 6px 25px rgba(0, 0, 0, 0.15);
    }

    .card-img-top {
        border-top-left-radius: 12px;
        border-top-right-radius: 12px;
        object-fit: cover;
        height: 250px;
        width: 100%;
    }

    .game-header {
        border-bottom: 2px solid #e9ecef;
        padding-bottom: 15px;
        margin-bottom: 25px;
    }

    h1 {
        font-size: 2.5rem;
        color: #1a3c34;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    h5 {
        font-size: 1.5rem;
        color: #0d6efd;
        font-weight: 600;
        margin-bottom: 1.25rem;
    }

    .feature-badge,
    .platform-badge {
        padding: 8px 16px;
        border-radius: 20px;
        margin: 6px;
        display: inline-block;
        font-size: 0.95rem;
        font-weight: 500;
        transition: background-color 0.3s;
    }

    .feature-badge {
        background-color: #0d6efd;
        color: white;
    }

    .feature-badge:hover {
        background-color: #0b5ed7;
    }

    .platform-badge {
        background-color: #6c757d;
        color: white;
    }

    .platform-badge:hover {
        background-color: #5c636a;
    }

    .list-group-item {
        border: none;
        padding: 10px 0;
        font-size: 1.05rem;
        color: #444;
    }

    .list-group-item strong {
        color: #1a3c34;
        font-weight: 600;
    }

    .breadcrumb {
        background-color: transparent;
        padding: 10px 0;
    }

    .breadcrumb-item a {
        color: #0d6efd;
        text-decoration: none;
    }

    .breadcrumb-item a:hover {
        text-decoration: underline;
    }

    @media (max-width: 768px) {
        h1 {
            font-size: 2rem;
        }

        h5 {
            font-size: 1.25rem;
        }

        .card-img-top {
            height: 200px;
        }
    }
</style>

<main class="page-wrapper top-space bottom-space">
    <div class="container mt-4 mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="game-list.php">Games</a></li>
                <li class="breadcrumb-item active" aria-current="page">
                    <?php echo htmlspecialchars($game['game_name']); ?>
                </li>
            </ol>
        </nav>

        <!-- Back Button -->
        <div class="mb-4">
            <a href="game-list.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Games
            </a>
        </div>

        <div class="game-container">
            <div class="game-header">
                <h1><?php echo htmlspecialchars($game['game_name']); ?></h1>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5>Game Information</h5>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">
                                    <strong>Publishers:</strong>
                                    <?php if ($game['publishers']): ?>
                                        <?php foreach (explode(', ', $game['publishers']) as $publisher): ?>
                                            <span><?= htmlspecialchars($publisher) ?></span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        None listed
                                    <?php endif; ?>
                                </li>
                                <li class="list-group-item">
                                    <strong>Release Date:</strong>
                                    <?php echo isset($game['game_release_date']) ? date("F j, Y", strtotime($game['game_release_date'])) : 'Unknown'; ?>
                                </li>
                                <li class="list-group-item">
                                    <strong>Rating:</strong>
                                    <span class="ms-2 badge bg-warning text-dark">
                                        <i class="bi bi-star-fill"></i>
                                        <?php echo htmlspecialchars($game['game_rating'] ?? 'N/A'); ?>
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5>Available Platforms</h5>
                            <div class="game-card-tag-list">
                                <?php if ($game['platforms']): ?>
                                    <?php foreach (explode(', ', $game['platforms']) as $platform): ?>
                                        <span class="game-platform-tag"><?= htmlspecialchars($platform) ?></span>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-muted">No platforms listed.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-body">
                            <h5>Categories</h5>
                            <div class="game-card-tag-list">
                                <?php if ($game['categories']): ?>
                                    <?php foreach (explode(', ', $game['categories']) as $category): ?>
                                        <?php
                                        $categoryClass = strtolower(str_replace([' ', "'", '(', ')'], '-', $category));
                                        ?>
                                        <span class="game-category-tag <?= htmlspecialchars($categoryClass) ?>">
                                            <?= htmlspecialchars($category) ?>
                                        </span>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-muted">No categories listed.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-body">
                            <h5>Accessibility Features</h5>
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
                                <?php foreach ($featureCategories as $category): ?>
                                    <div class="game-card-tag-list mb-2">
                                        <p class="m-0">
                                            <strong><?= htmlspecialchars($category['cat_name']) ?>:</strong>
                                        </p>
                                        <?php foreach (explode(', ', $category['features']) as $feature): ?>
                                            <span class="game-accessibility-tag"><?= htmlspecialchars($feature) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p>None listed</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require 'footer.php'; ?>