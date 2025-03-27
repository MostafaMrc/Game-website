<?php
if (!isset($_GET['url']) || empty($_GET['url'])) {
    http_response_code(400);
    exit("No URL provided");
}

$imageUrl = filter_var($_GET['url'], FILTER_VALIDATE_URL);
if (!$imageUrl) {
    http_response_code(400);
    exit("Invalid URL");
}

$cacheDir = __DIR__ . '/cache/';
$hash = md5($imageUrl);
$cachedFile = $cacheDir . $hash;

// Ensure cache directory exists
if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0777, true);
}

// If cached file exists, serve it
if (file_exists($cachedFile)) {
    $mimeType = mime_content_type($cachedFile);
    header("Content-Type: $mimeType");
    readfile($cachedFile);
    exit;
}

// Fetch image using cURL (more reliable than file_get_contents)
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $imageUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Avoid hanging requests
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Ignore SSL issues
$imageData = curl_exec($ch);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
curl_close($ch);

if (!$imageData) {
    http_response_code(404);
    exit("Image not found or cannot be fetched");
}

// Determine the correct file extension
$ext = "";
if (strpos($contentType, 'jpeg') !== false) {
    $ext = '.jpg';
} elseif (strpos($contentType, 'png') !== false) {
    $ext = '.png';
} elseif (strpos($contentType, 'gif') !== false) {
    $ext = '.gif';
} elseif (strpos($contentType, 'webp') !== false) {
    $ext = '.webp';
} else {
    http_response_code(415);
    exit("Unsupported image type");
}

// Save image with correct extension
$cachedFile .= $ext;
if (!file_put_contents($cachedFile, $imageData)) {
    http_response_code(500);
    exit("Failed to save image");
}

// Serve image
header("Content-Type: $contentType");
readfile($cachedFile);
?>
