<?php
set_time_limit(1000);

require 'config.php'; // Load database and API configuration

$api_key = "d0857161568198fed9c0d896bf5cef0c3ec6bbbf"; 
$categories = ["games", "game", "characters", "character", ""]; // Define categories
$limit = 450; // API request limit per page (adjust based on API restrictions)

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

    // Remove the callback function wrapper to get the raw JSON data
    $response = preg_replace('/^myCallbackFunction\((.*)\);$/', '$1', $response);
    
    return json_decode($response, true);
}

foreach ($categories as $category) {
    $page = 1;
    while (true) {
        // Modified to request JSONP by adding "callback=myCallbackFunction"
        $games_url = "https://www.giantbomb.com/api/games/?api_key=$api_key&format=jsonp&callback=myCallbackFunction&filter=genres:$category&limit=$limit&page=$page";
        $games_data = fetch_api_data($games_url);

        sleep(5);

        if (!$games_data || !isset($games_data['results']) || empty($games_data['results'])) {
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
            $image_url = $game['image']['medium_url'] ?? null;
            $platform = $game['platforms'][0]['name'] ?? null;
            $genre = $category; // Assigning the category we are fetching
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
                    $character_image = $character['image']['medium_url'] ?? null;
                    $char_description = $character['deck'] ?? null;

                    $char_stmt->execute();
                }
                $char_stmt->close();
            }
        }

        $stmt->close();
        
        echo "Inserted " . count($games_data['results']) . " records for category: $category (Page: $page)\n";

        // If fewer than the limit, stop (last page reached)
        if (count($games_data['results']) < $limit) {
            break;
        }

        $page++; // Move to next page
    }
}

$conn->close();
echo "All data imported successfully!";
?>
