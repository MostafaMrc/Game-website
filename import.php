<?php
set_time_limit(1000);
require 'config.php'; // Load database configuration

$api_key = "d0857161568198fed9c0d896bf5cef0c3ec6bbbf"; 
$categories = ["games", "game", "characters", "character", ""]; // Define categories
$limit = 450; // API request limit per page

function fetch_api_data($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        echo "Failed to fetch API data from $url (HTTP Code: $httpCode)\n";
        return false;
    }

    // Remove JSONP callback wrapper
    $response = preg_replace('/^myCallbackFunction\((.*)\);$/', '$1', $response);
    
    return json_decode($response, true);
}

// Function to cache images
function cache_image($imageUrl) {
    global $conn;

    $cacheDir = 'cache/';
    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0777, true);
    }

    // Check if image already exists in database
    $stmt = $conn->prepare("SELECT cached_path FROM cached_images WHERE image_url = ?");
    $stmt->bind_param("s", $imageUrl);
    $stmt->execute();
    $stmt->bind_result($cachedPath);
    if ($stmt->fetch()) {
        $stmt->close();
        return $cachedPath; // Return existing cached image
    }
    $stmt->close();

    // Get image extension
    $imageExt = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION);
    $validExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array(strtolower($imageExt), $validExtensions)) {
        $imageExt = 'jpg';
    }

    // Generate a unique file name
    $imageName = md5($imageUrl) . '.' . $imageExt;
    $cachedFile = $cacheDir . $imageName;

    // Download the image
    $ch = curl_init($imageUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
    $imageData = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 200 && $imageData) {
        file_put_contents($cachedFile, $imageData);

        // Store the cached image path in database
        $stmt = $conn->prepare("INSERT INTO cached_images (image_url, cached_path) VALUES (?, ?)");
        $stmt->bind_param("ss", $imageUrl, $cachedFile);
        $stmt->execute();
        $stmt->close();

        return $cachedFile;
    }

    return null; // Return null if download failed
}

foreach ($categories as $category) {
    $page = 1;
    while (true) {
        $games_url = "https://www.giantbomb.com/api/games/?api_key=$api_key&format=jsonp&callback=myCallbackFunction&filter=genres:$category&limit=$limit&page=$page";
        $games_data = fetch_api_data($games_url);

        sleep(5);

        if (!$games_data || empty($games_data['results'])) {
            echo "No more data for category: $category\n";
            break;
        }

        $stmt = $conn->prepare("INSERT INTO games (id, name, description, release_date, image_url, platform, genre, developer, publisher)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
                name=VALUES(name), 
                description=VALUES(description), 
                release_date=VALUES(release_date), 
                image_url=VALUES(image_url),
                platform=VALUES(platform),
                genre=VALUES(genre),
                developer=VALUES(developer),
                publisher=VALUES(publisher)");

        if (!$stmt) {
            die("Database Error: " . $conn->error);
        }

        $stmt->bind_param("issssssss", $id, $name, $description, $release_date, $image_url, $platform, $genre, $developer, $publisher);

        foreach ($games_data['results'] as $game) {
            if (!isset($game['id'], $game['name'])) {
                continue;
            }

            $id = (int) $game['id'];
            $name = $game['name'];
            $description = $game['deck'] ?? null;
            $release_date = $game['original_release_date'] ?? null;
            $original_image_url = $game['image']['medium_url'] ?? null;
            $image_url = $original_image_url ? cache_image($original_image_url) : null;
            $platform = $game['platforms'][0]['name'] ?? null;
            $genre = $category;
            $developer = $game['developers'][0]['name'] ?? null;
            $publisher = $game['publishers'][0]['name'] ?? null;

            $stmt->execute();

            // Fetch characters for this game
            $game_character_url = "https://www.giantbomb.com/api/game/{$id}/?api_key=$api_key&format=jsonp&callback=myCallbackFunction";
            $game_data = fetch_api_data($game_character_url);

            sleep(5);

            if (isset($game_data['results']['characters'])) {
                $char_stmt = $conn->prepare("INSERT INTO characters (game_id, character_name, character_image, description) 
                    VALUES (?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE 
                        character_name=VALUES(character_name), 
                        character_image=VALUES(character_image), 
                        description=VALUES(description)");

                if (!$char_stmt) {
                    die("Character DB Error: " . $conn->error);
                }

                $char_stmt->bind_param("isss", $game_id, $character_name, $character_image, $char_description);

                foreach ($game_data['results']['characters'] as $character) {
                    $game_id = $id;
                    $character_name = $character['name'] ?? "Unknown";
                    $original_character_image = $character['image']['medium_url'] ?? null;
                    $character_image = $original_character_image ? cache_image($original_character_image) : null;
                    $char_description = $character['deck'] ?? null;

                    $char_stmt->execute();
                }
                $char_stmt->close();
            }
        }

        $stmt->close();
        
        echo "Inserted " . count($games_data['results']) . " records for category: $category (Page: $page)\n";

        if (count($games_data['results']) < $limit) {
            break;
        }

        $page++;
    }
}

$conn->close();
echo "All data imported successfully!";
?>
