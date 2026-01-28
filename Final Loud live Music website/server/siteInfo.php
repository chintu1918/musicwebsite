<?php
// server/siteInfo.php
// Scans the site directory and returns JSON with pages, assets and simple stats.

header('Content-Type: application/json');

$root = realpath(__DIR__ . '/../');
if ($root === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Unable to resolve project root']);
    exit;
}

function file_info($path, $root) {
    $rel = ltrim(str_replace($root, '', $path), DIRECTORY_SEPARATOR);
    return [
        'path' => $rel,
        'size' => filesize($path),
        'modified' => filemtime($path),
    ];
}

$pages = [];
$htmlFiles = glob($root . DIRECTORY_SEPARATOR . '*.html');
foreach ($htmlFiles as $file) {
    $content = file_get_contents($file);
    preg_match_all('/<img\s+[^>]*src=["\']([^"\']+)["\']/i', $content, $imgs);
    preg_match_all('/<a\s+[^>]*href=["\']([^"\']+)["\']/i', $content, $links);
    $pages[] = array_merge(file_info($file, $root), [
        'images_count' => isset($imgs[1]) ? count($imgs[1]) : 0,
        'links_count' => isset($links[1]) ? count($links[1]) : 0,
    ]);
}

$imageFiles = [];
$imageDir = $root . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'Images';
if (is_dir($imageDir)) {
    $imageFiles = glob($imageDir . DIRECTORY_SEPARATOR . '*');
}

$bookingsFile = $root . DIRECTORY_SEPARATOR . 'server' . DIRECTORY_SEPARATOR . 'bookings.json';
$bookings = null;
if (file_exists($bookingsFile)) {
    $bookings = json_decode(file_get_contents($bookingsFile), true);
}

$snapshot = [
    'timestamp' => time(),
    'pages_count' => count($pages),
    'total_images' => count($imageFiles),
    'total_size_bytes' => array_sum(array_map(function($p){ return $p['size']; }, $pages)),
    'pages' => $pages,
    'images' => array_map(function($p) use ($root){ return file_info($p, $root); }, $imageFiles),
    'bookings_count' => is_array($bookings) ? count($bookings) : 0,
];

$recordsFile = __DIR__ . DIRECTORY_SEPARATOR . 'site_records.json';
$history = [];
if (file_exists($recordsFile)) {
    $json = file_get_contents($recordsFile);
    $history = json_decode($json, true);
    if (!is_array($history)) $history = [];
}
$history[] = $snapshot;
file_put_contents($recordsFile, json_encode($history, JSON_PRETTY_PRINT));

echo json_encode($snapshot);
exit;

?>
