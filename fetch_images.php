<?php
include 'config.php';

function fetchImage($characterName) {
    $searchQuery = urlencode($characterName . " wrestler");
    $apiKey = "YOUR_BING_API_KEY"; // Replace with actual API key
    $apiUrl = "https://api.bing.microsoft.com/v7.0/images/search?q={$searchQuery}&count=1";

    $headers = [
        "Ocp-Apim-Subscription-Key: $apiKey"
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Avoid hanging requests
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Ignore SSL errors

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        error_log("cURL Error: " . curl_error($ch));
        curl_close($ch);
        return null;
    }

    curl_close($ch);

    $data = json_decode($response, true);

    // Debugging: Log the API response
    file_put_contents("debug_log.txt", print_r($data, true), FILE_APPEND);

    if (!empty($data['value'][0]['contentUrl'])) {
        return $data['value'][0]['contentUrl'];
    }

    return null;
}

// Get characters without images
$sql = "SELECT id, character_name FROM characters WHERE character_image IS NULL OR character_image = ''";
$result = $conn->query($sql);

if (!$result) {
    die("Database Error: " . $conn->error);
}

while ($row = $result->fetch_assoc()) {
    $imageUrl = fetchImage($row['character_name']);

    if ($imageUrl) {
        // Debugging: Log the SQL update queries
        file_put_contents("debug_log.txt", "Updating: " . $row['character_name'] . " -> " . $imageUrl . "\n", FILE_APPEND);

        $updateSql = "UPDATE characters SET character_image = ? WHERE id = ?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("si", $imageUrl, $row['id']);
        
        if ($stmt->execute()) {
            echo "Updated: " . htmlspecialchars($row['character_name']) . "<br>";
        } else {
            echo "Error updating " . htmlspecialchars($row['character_name']) . ": " . $stmt->error . "<br>";
            file_put_contents("debug_log.txt", "SQL Error: " . $stmt->error . "\n", FILE_APPEND);
        }

        $stmt->close();
    } else {
        echo "No image found for: " . htmlspecialchars($row['character_name']) . "<br>";
    }
}

echo "Done!";
?>
