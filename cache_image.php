<?php
if (!isset($_GET['url'])) {
    http_response_code(400);
    exit("No URL provided");
}

$imageUrl = $_GET['url'];
$cacheDir = __DIR__ . '/cache/';
$cachedFile = $cacheDir . md5($imageUrl) . '.jpg';

if (file_exists($cachedFile)) {
    header('Content-Type: image/jpeg');
    readfile($cachedFile);
    exit;
}

// If not cached, fetch and save the image
$imageData = file_get_contents($imageUrl);
if ($imageData) {
    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0777, true);
    }
    file_put_contents($cachedFile, $imageData);
    header('Content-Type: image/jpeg');
    readfile($cachedFile);
} else {
    http_response_code(404);
    exit("Image not found");
}
?>
