<?php
include 'config.php'; // Connect to the database
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Games</title>
    <link rel="stylesheet" href="main.css">
</head>
<body>

<h1>All Games</h1>

<p>
    <strong>Filter by letter:</strong>
    <?php
    foreach (range('A', 'Z') as $letter) {
        echo "<a href='gamelis.php?letter=$letter'>$letter</a> ";
    }
    ?>
    | <a href="gamelis.php">Show All</a>
</p>

<table border="1">
    <tr>
        <th>Game</th>
        <th>Action</th>
    </tr>

    <?php
    // Check if filtering by letter
    if (isset($_GET['letter']) && preg_match('/^[A-Z]$/', $_GET['letter'])) {
        $letter = $_GET['letter'] . '%';

        // Use prepared statement
        $stmt = $conn->prepare("SELECT id, name FROM games WHERE name LIKE ?");
        $stmt->bind_param("s", $letter);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $sql = "SELECT id, name FROM games ORDER BY name ASC";
        $result = $conn->query($sql);
    }

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row["name"]) . "</td>";
            echo "<td><a href='games.php?id=" . $row["id"] . "'>View</a></td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='2'>No games found.</td></tr>";
    }
    ?>

</table>

</body>
</html>

<?php
$conn->close();
?>
