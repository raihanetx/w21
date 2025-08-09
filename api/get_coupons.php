<?php
header('Content-Type: application/json');

$coupons_file = __DIR__ . '/coupons.json';

if (file_exists($coupons_file)) {
    $file_content = file_get_contents($coupons_file);
    // To ensure it's a valid json array even if file is empty
    $coupons = json_decode($file_content, true);
    if (json_last_error() !== JSON_ERROR_NONE || $coupons === null) {
        echo json_encode([]);
    } else {
        echo $file_content;
    }
} else {
    // If file doesn't exist, return an empty array
    echo json_encode([]);
}
?>
