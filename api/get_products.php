<?php
header('Content-Type: application/json');

$products_file = __DIR__ . '/products.json';

if (file_exists($products_file)) {
    $file_content = file_get_contents($products_file);
    echo $file_content;
} else {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Products file not found.']);
}
?>
