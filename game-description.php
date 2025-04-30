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

// Fetch game platforms
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

// Fetch game accessibility features
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

// Function to extract game description from PDF
function getGameDescriptionFromPDF($game_name)
{
    $pdf_path = 'Game Descriptions.pdf';

    if (!file_exists($pdf_path)) {
        $pdf_path = 'Game Descriptions.pdf';
        if (!file_exists($pdf_path)) {
            return "Game description not available. PDF file not found at 'Game Descriptions.pdf'.";
        }
    }

    if (extension_loaded('pdfparser') || extension_loaded('imagick') || class_exists('FPDI')) {
        return "This game description would be extracted from 'Game Descriptions.pdf' using a PHP PDF library.";
    } elseif (function_exists('exec') && strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
        $output_file = tempnam(sys_get_temp_dir(), 'pdf_');
        exec("pdftotext '$pdf_path' '$output_file'", $output, $return_var);

        if ($return_var != 0) {
            return "Error extracting text from PDF. Make sure the PDF file is properly formatted and poppler-utils is installed.";
        }

        $text = file_get_contents($output_file);
        unlink($output_file);

        $description = "";
        $game_name_pattern = preg_quote($game_name, '/');

        if (preg_match('/' . $game_name_pattern . '\s*(.*?)(?=\n\s*[A-Z]|\z)/s', $text, $matches)) {
            $description = trim($matches[1]);
        }

        if (!empty($description)) {
            return $description;
        }
    }

    global $features, $platforms, $game_id, $pdo;

    $feature_list = !empty($features) ? "Accessibility features include: " . implode(", ", array_column($features, 'feature_name')) . ".\n\n" : "";
    $platform_list = !empty($platforms) ? "Available on: " . implode(", ", array_column($platforms, 'platform_name')) . ".\n\n" : "";

    try {
        $sql = "SELECT c.category_name 
                FROM Game_Categories gc 
                JOIN Categories c ON gc.category_id = c.category_id 
                WHERE gc.game_id = ?";
        $stmt = pdo($pdo, $sql, [$game_id]);
        $categories = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        $category_list = !empty($categories) ? "Game categories: " . implode(", ", $categories) . ".\n\n" : "";
    } catch (PDOException $e) {
        $category_list = "";
    }

    return "This is a detailed description for " . htmlspecialchars($game_name) . ".\n\n" .
        $category_list .
        $feature_list .
        $platform_list .
        "The full description would be loaded from 'Game Descriptions.pdf'. In production, this PDF file would contain comprehensive information about the game including gameplay, storyline, accessibility options, and more.";
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

    .game-description {
        white-space: pre-line;
        line-height: 1.8;
        font-size: 1.1rem;
        color: #333;
        background-color: #f9f9f9;
        padding: 25px;
        border-radius: 8px;
        border-left: 4px solid #0d6efd;
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

    .pdf-viewer {
        width: 100%;
        height: 60vh;
        max-height: 600px;
        border: 1px solid #dee2e6;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        margin: 20px 0;
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

        .pdf-viewer {
            height: 40vh;
            max-height: 400px;
        }

        .game-description {
            padding: 20px;
            font-size: 1rem;
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
                                <h5>Game Description</h5>
                                <div class="game-description">
                                    <?php echo nl2br(htmlspecialchars(getGameDescriptionFromPDF($game['game_name']))); ?>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-body">
                                <h5>Game Description Document</h5>
                                <p>You can also view the full game descriptions document below:</p>
                                <iframe src="Game Descriptions.pdf#search=<?php echo urlencode($game['game_name']); ?>"
                                    class="pdf-viewer"></iframe>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-body">
                                <h5>Available Platforms</h5>
                                <?php if (isset($error_platforms)): ?>
                                    <div class="alert alert-warning" role="alert">
                                        <?php echo $error_platforms; ?>
                                    </div>
                                <?php elseif (empty($platforms)): ?>
                                    <p class="text-muted">No platform information available.</p>
                                <?php else: ?>
                                    <div>
                                        <?php foreach ($platforms as $platform): ?>
                                            <span
                                                class="platform-badge"><?php echo htmlspecialchars($platform['platform_name']); ?></span>
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
                                            <span
                                                class="feature-badge"><?php echo htmlspecialchars($feature['feature_name']); ?></span>
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