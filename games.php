<?php
include 'config.php';

if (!isset($_GET['id'])) {
    die("No game selected.");
}

$game_id = (int) $_GET['id'];

$sql = "SELECT * FROM games WHERE id = $game_id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    die("Game not found.");
}

$game = $result->fetch_assoc();

// Fetch characters related to this game
$char_sql = "SELECT character_name, character_image, description FROM characters WHERE game_id = $game_id";
$char_result = $conn->query($char_sql);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($game['name']); ?></title>
    <link rel="stylesheet" href="main.css">
</head>
<body>
    <div class="game-details">
        <h1><?php echo htmlspecialchars($game['name']); ?></h1>

        <div class="game-info">
            <?php if (!empty($game['image_url'])): ?>
                <img src="cache_image.php?url=<?php echo urlencode($game['image_url']); ?>" 
                     alt="<?php echo htmlspecialchars($game['name']); ?>">
            <?php endif; ?>

            <div class="details">
                <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($game['description'] ?? "No description available.")); ?></p>
                <p><strong>Release Date:</strong> <?php echo htmlspecialchars($game['release_date'] ?? "Unknown"); ?></p>
                <p><strong>Platform:</strong> <?php echo htmlspecialchars($game['platform'] ?? "Unknown"); ?></p>
                <p><strong>Genre:</strong> <?php echo htmlspecialchars($game['genre'] ?? "Unknown"); ?></p>
            </div>
        </div>

        <h2>Characters</h2>
        <div class="character-list">
            <?php while ($character = $char_result->fetch_assoc()): ?>
                <div class="character">
                    <img src="cache_image.php?url=<?php echo urlencode($character['character_image']); ?>" 
                         alt="<?php echo htmlspecialchars($character['character_name']); ?>">
                    <p><strong><?php echo htmlspecialchars($character['character_name']); ?></strong></p>
                    <p><?php echo nl2br(htmlspecialchars($character['description'] ?? "No description available.")); ?></p>
                </div>
            <?php endwhile; ?>
        </div>

        <a href="index.php">⬅ Back to Home</a>
    </div>
</body>
</html>
