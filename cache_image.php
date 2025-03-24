<?php
function getCachedImage($imageUrl) {
    $cacheDir = 'cache/'; // Directory to store cached images
    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0777, true);
    }

    $imageExt = pathinfo($imageUrl, PATHINFO_EXTENSION);
    $validExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    // Ensure extension is valid
    if (!in_array(strtolower($imageExt), $validExtensions)) {
        $imageExt = 'jpg'; // Default to JPG if unknown
    }

    $imageName = md5($imageUrl) . '.' . $imageExt;
    $cachedFile = $cacheDir . $imageName;

    // Serve cached image if available
    if (file_exists($cachedFile)) {
        return $cachedFile;
    }

    // Use cURL to fetch the image
    $ch = curl_init($imageUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Follow redirects
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
    $imageData = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Save image if request was successful
    if ($httpCode == 200 && $imageData) {
        file_put_contents($cachedFile, $imageData);
        return $cachedFile;
    }

    return false; // Failed to download
}

// Handle image request
if (isset($_GET['url'])) {
    $imageUrl = $_GET['url'];
    $cachedImage = getCachedImage($imageUrl);

    if ($cachedImage) {
        $mimeType = mime_content_type($cachedImage);
        header("Content-Type: $mimeType");
        readfile($cachedImage);
    } else {
        http_response_code(404);
        echo "Image not found.";
    }
}
?>
