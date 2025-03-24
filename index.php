<?php
include 'config.php'; // Connect to the database
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GiantBomb Game Database</title>
    <link rel="stylesheet" href="main.css">
    <script>
        function scrollHorizontally(containerId, direction) {
            const container = document.getElementById(containerId);
            const scrollAmount = 300; // Adjust for how much it scrolls
            container.scrollBy({ left: direction * scrollAmount, behavior: 'smooth' });
        }
    </script>
</head>
<body>

    <h1>Welcome to the GiantBomb Game Database</h1>
    <p><a href="import.php">Import Data</a> | <a href="gameslis.php">View All Games</a></p>

    <div class="container">
        
        <!-- Latest Games -->
        <div class="section">
            <h2>Latest Games</h2>
            <button class="scroll-btn" onclick="scrollHorizontally('latestGames', -1)">⬅️</button>
            <button class="scroll-btn" onclick="scrollHorizontally('latestGames', 1)">➡️</button>
            <div class="scroll-container" id="latestGames">
                <?php
                $sql = "SELECT id, name, image_url FROM games ORDER BY release_date DESC LIMIT 20";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<div class='game'>";
                        if ($row["image_url"]) {
                            echo "<img src='" . $row["image_url"] . "' alt='" . $row["name"] . "'>";
                        }
                        echo "<p><a href='games.php?id=" . $row["id"] . "'>" . $row["name"] . "</a></p>";
                        echo "</div>";
                    }
                } else {
                    echo "<p>No games found.</p>";
                }
                ?>
            </div>
        </div>

        <!-- Most Popular Games -->
        <div class="section">
            <h2>Most Popular Games</h2>
            <button class="scroll-btn" onclick="scrollHorizontally('popularGames', -1)">⬅️</button>
            <button class="scroll-btn" onclick="scrollHorizontally('popularGames', 1)">➡️</button>
            <div class="scroll-container" id="popularGames">
                <?php
                $sql = "SELECT id, name, image_url FROM games ORDER BY id DESC LIMIT 20";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<div class='game'>";
                        if ($row["image_url"]) {
                            echo "<img src='" . $row["image_url"] . "' alt='" . $row["name"] . "'>";
                        }
                        echo "<p><a href='games.php?id=" . $row["id"] . "'>" . $row["name"] . "</a></p>";
                        echo "</div>";
                    }
                } else {
                    echo "<p>No games found.</p>";
                }
                ?>
            </div>
        </div>

        <!-- Random Games -->
        <div class="section">
            <h2>Discover a Random Game</h2>
            <button class="scroll-btn" onclick="scrollHorizontally('randomGames', -1)">⬅️</button>
            <button class="scroll-btn" onclick="scrollHorizontally('randomGames', 1)">➡️</button>
            <div class="scroll-container" id="randomGames">
                <?php
                $sql = "SELECT id, name, image_url FROM games ORDER BY RAND() LIMIT 20";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<div class='game'>";
                        if ($row["image_url"]) {
                            echo "<img src='" . $row["image_url"] . "' alt='" . $row["name"] . "'>";
                        }
                        echo "<p><a href='games.php?id=" . $row["id"] . "'>" . $row["name"] . "</a></p>";
                        echo "</div>";
                    }
                } else {
                    echo "<p>No games found.</p>";
                }
                ?>
            </div>
        </div>

    </div>

</body>
</html>

<?php $conn->close(); ?>
                