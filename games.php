<?php
include 'config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid game ID.");
}

$game_id = (int) $_GET['id'];

// Fetch game details securely
$stmt = $conn->prepare("SELECT name, image_url, description, release_date, platform, genre FROM games WHERE id = ?");
$stmt->bind_param("i", $game_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Game not found.");
}

$game = $result->fetch_assoc();
$stmt->close();

// Fetch characters related to this game
$char_stmt = $conn->prepare("SELECT character_name, character_image, description FROM characters WHERE game_id = ?");
$char_stmt->bind_param("i", $game_id);
$char_stmt->execute();
$char_result = $char_stmt->get_result();

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
                    <?php 
                    $char_img_url = htmlspecialchars($character['character_image'] ?? '');
                    if (!empty($char_img_url)): 
                    ?>
                        <img src="cache_image.php?url=<?php echo urlencode($char_img_url); ?>" 
                             alt="<?php echo htmlspecialchars($character['character_name']); ?>">
                    <?php else: ?>
                        <p>No image found for: <?php echo htmlspecialchars($character['character_name']); ?></p>
                    <?php endif; ?>
                    <p><strong><?php echo htmlspecialchars($character['character_name']); ?></strong></p>
                    <p><?php echo nl2br(htmlspecialchars($character['description'] ?? "No description available.")); ?></p>
                </div>
            <?php endwhile; ?>
        </div>

        <a href="index.php">⬅ Back to Home</a>
    </div>
</body>
</html>

<?php
$char_stmt->close();
$conn->close();
?>
