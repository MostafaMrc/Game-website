<?php
include 'config.php';

function fetchImage($characterName) {
    $searchQuery = urlencode($characterName . " wrestler");
    $apiKey = "YOUR_BING_API_KEY"; // Replace with your API key
    $apiUrl = "https://www.giantbomb.com/api/search/?api_key=&format=json&resources=character&query=";

    $headers = [
        "Ocp-Apim-Subscription-Key: $apiKey"
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    
    if (!empty($data['value'][0]['contentUrl'])) {
        return $data['value'][0]['contentUrl'];
    }

    return null;
}

// Get characters without images
$sql = "SELECT id, character_name FROM characters WHERE character_image IS NULL OR character_image = ''";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $imageUrl = fetchImage($row['character_name']);
    
    if ($imageUrl) {
        $updateSql = "UPDATE characters SET character_image = '$imageUrl' WHERE id = " . $row['id'];
        $conn->query($updateSql);
        echo "Updated: " . $row['character_name'] . "<br>";
    } else {
        echo "No image found for: " . $row['character_name'] . "<br>";
    }
}

echo "Done!";
?>
