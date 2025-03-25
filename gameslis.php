<?php
include 'config.php'; // Connect to the database

$letter = isset($_GET['letter']) && preg_match('/^[A-Z]$/', $_GET['letter']) ? $_GET['letter'] . '%' : '%';

$sql = "SELECT id, name FROM games WHERE name LIKE ? ORDER BY name ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $letter);
$stmt->execute();
$result = $stmt->get_result();

// If this is an AJAX request, return only the table rows
if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row["name"]) . "</td>";
        echo "<td><a href='games.php?id=" . $row["id"] . "' class='game-link'>View</a></td>";
        echo "</tr>";
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Games</title>
    <link rel="stylesheet" href="main.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<h1>All Games</h1>

<!-- Alphabet Filter -->
<p>
    <strong>Filter by letter:</strong>
    <?php
    foreach (range('A', 'Z') as $letter) {
        echo "<a href='#' class='filter-link' data-letter='$letter'>$letter</a> ";
    }
    ?>
    | <a href="#" class="filter-link" data-letter="">Show All</a>
</p>

<table border="1">
    <tr>
        <th>Game</th>
        <th>Action</th>
    </tr>
    <tbody id="game-table">
        <?php
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row["name"]) . "</td>";
            echo "<td><a href='games.php?id=" . $row["id"] . "' class='game-link'>View</a></td>";
            echo "</tr>";
        }
        ?>
    </tbody>
</table>

<script>
$(document).ready(function () {
    $(".filter-link").click(function (e) {
        e.preventDefault();
        var letter = $(this).data("letter");

        $.ajax({
            url: "gameslis.php",
            type: "GET",
            data: { letter: letter, ajax: 1 },
            success: function (response) {
                $("#game-table").html(response);
                // Rebind click events to newly added game links
                $(".game-link").off("click").on("click", function (e) {
                    window.location.href = $(this).attr("href");
                });
            }
        });
    });

    // Ensure game links remain functional
    $(document).on("click", ".game-link", function (e) {
        window.location.href = $(this).attr("href");
    });
});
</script>

</body>
</html>

<?php
$conn->close();
?>
