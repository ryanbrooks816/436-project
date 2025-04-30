<?php
require 'header.php';

// Check if game_id is provided
if (!isset($_GET['game_id']) || empty($_GET['game_id'])) {
    header('Location: game-list.php');
    exit;
}

$game_id = intval($_GET['game_id']);

// Fetch game details from database
try {
    $sql = "SELECT g.*, p.publisher_name 
            FROM Games g
            LEFT JOIN Game_Publishers gp ON g.game_id = gp.game_id
            LEFT JOIN Publishers p ON gp.publisher_id = p.publisher_id
            WHERE g.game_id = ?";
    $stmt = pdo($pdo, $sql, [$game_id]);
    $game = $stmt->fetch();

    if (!$game) {
        $error = "Game not found.";
    }
} catch (PDOException $e) {
    $error = "Error fetching game details: " . htmlspecialchars($e->getMessage());
}

// Fetch game platforms from database
try {
    $sql = "SELECT p.platform_name 
            FROM Platforms p 
            JOIN Game_Platforms gp ON p.platform_id = gp.platform_id 
            WHERE gp.game_id = ?";
    $stmt = pdo($pdo, $sql, [$game_id]);
    $platforms = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_platforms = "Error fetching platforms: " . htmlspecialchars($e->getMessage());
}

// Fetch game accessibility features from database
try {
    $sql = "SELECT af.feature_name 
            FROM Accessibility_Features af 
            JOIN Game_Accessibility_Features gaf ON af.feature_id = gaf.feature_id 
            WHERE gaf.game_id = ?";
    $stmt = pdo($pdo, $sql, [$game_id]);
    $features = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_features = "Error fetching accessibility features: " . htmlspecialchars($e->getMessage());
}

// Function to extract game details from a text file
function getGameDetailsFromText($game_name) {
    $text_path = 'game_descriptions.txt';

    if (!file_exists($text_path)) {
        return [
            'description' => "Game description not available. Text file not found at 'game_descriptions.txt'.",
            'platforms' => [],
            'age_rating' => 'N/A'
        ];
    }

    $file_content = file_get_contents($text_path);
    if ($file_content === false) {
        return [
            'description' => "Error reading text file.",
            'platforms' => [],
            'age_rating' => 'N/A'
        ];
    }

    // Split the file into lines
    $lines = array_filter(array_map('trim', explode("\n", $file_content)));

    $description = "No description available.";
    $txt_platforms = [];
    $age_rating = 'N/A';
    $current_game = '';
    $current_section = '';

    foreach ($lines as $line) {
        // Check if the line is a game name (not a field with a colon)
        if (!empty($line) && strpos($line, ':') === false) {
            $current_game = $line;
            $current_section = '';
            continue;
        }

        // Skip header or empty lines
        if ($line === 'Video Game List' || empty($line)) {
            continue;
        }

        // Check if this line belongs to the game we're looking for
        if ($current_game === $game_name) {
            if (strpos($line, 'Publisher:') === 0) {
                $current_section = 'Publisher';
                continue;
            } elseif (strpos($line, 'Age Rating:') === 0) {
                $current_section = 'Age Rating';
                $age_rating = trim(substr($line, strlen('Age Rating:')));
                continue;
            } elseif (strpos($line, 'Platforms:') === 0) {
                $current_section = 'Platforms';
                $platforms_string = trim(substr($line, strlen('Platforms:')));
                $txt_platforms = array_map('trim', explode(',', $platforms_string));
                continue;
            } elseif (strpos($line, 'Description:') === 0) {
                $current_section = 'Description';
                $description = trim(substr($line, strlen('Description:')));
                continue;
            } elseif ($current_section === 'Description') {
                // Continue appending to description if we're in that section
                $description .= "\n" . $line;
                continue;
            }
        }
    }

    // Combine text file platforms with database platforms, avoiding duplicates
    global $platforms;
    $platform_names = array_column($platforms, 'platform_name');
    $all_platforms = array_unique(array_merge($platform_names, $txt_platforms));

    return [
        'description' => $description,
        'platforms' => $all_platforms,
        'age_rating' => $age_rating
    ];
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
                    <?php echo htmlspecialchars($game['game_name'] ?? 'Game Details'); ?>
                </li>
            </ol>
        </nav>

        <!-- Back Button -->
        <div class="mb-4">
            <a href="game-list.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Games
            </a>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error; ?>
            </div>
        <?php elseif ($game): ?>
            <?php 
                $game_details = getGameDetailsFromText($game['game_name']);
                $txt_platforms = $game_details['platforms'];
                $age_rating = $game_details['age_rating'];
            ?>
            <div class="game-container">
                <div class="game-header">
                    <h1><?php echo htmlspecialchars($game['game_name']); ?></h1>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <img src="images/placeholder.jpg" class="card-img-top"
                                alt="Image for <?php echo htmlspecialchars($game['game_name']); ?>">
                            <div class="card-body">
                                <h5>Game Information</h5>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item">
                                        <strong>Publisher:</strong>
                                        <?php echo htmlspecialchars($game['publisher_name'] ?? 'Unknown'); ?>
                                    </li>
                                    <li class="list-group-item">
                                        <strong>Release Date:</strong>
                                        <?php echo isset($game['game_release_date']) ? date("F j, Y", strtotime($game['game_release_date'])) : 'Unknown'; ?>
                                    </li>
                                    <li class="list-group-item">
                                        <strong>Age Rating:</strong>
                                        <?php echo htmlspecialchars($age_rating); ?>
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
                                <?php if (isset($error_platforms)): ?>
                                    <div class="alert alert-warning" role="alert">
                                        <?php echo $error_platforms; ?>
                                    </div>
                                <?php elseif (empty($txt_platforms)): ?>
                                    <p class="text-muted">No platform information available.</p>
                                <?php else: ?>
                                    <div>
                                        <?php foreach ($txt_platforms as $platform): ?>
                                            <span class="platform-badge"><?php echo htmlspecialchars($platform); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-body">
                                <h5>Accessibility Features</h5>
                                <?php if (isset($error_features)): ?>
                                    <div class="alert alert-warning" role="alert">
                                        <?php echo $error_features; ?>
                                    </div>
                                <?php elseif (empty($features)): ?>
                                    <p class="text-muted">No accessibility features listed.</p>
                                <?php else: ?>
                                    <div>
                                        <?php foreach ($features as $feature): ?>
                                            <span class="feature-badge"><?php echo htmlspecialchars($feature['feature_name']); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require 'footer.php'; ?>