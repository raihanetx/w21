<?php
header('Content-Type: application/json');

// Define the time periods
$today = new DateTime();
$periods = [
    'Last Day' => (clone $today)->modify('-1 day'),
    'Last 7 Days' => (clone $today)->modify('-7 days'),
    'Last 15 Days' => (clone $today)->modify('-15 days'),
    'Last 30 Days' => (clone $today)->modify('-30 days'),
    'Last 90 Days' => (clone $today)->modify('-90 days'),
    'Last 6 Months' => (clone $today)->modify('-6 months'),
    'Last 1 Year' => (clone $today)->modify('-1 year')
];

// Initialize counts
$confirmed_counts = array_fill_keys(array_keys($periods), 0);
$cancelled_counts = array_fill_keys(array_keys($periods), 0);

$orders_file = __DIR__ . '/orders.json';

if (!file_exists($orders_file)) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Orders file not found.']);
    exit;
}

$file_content = file_get_contents($orders_file);
if ($file_content === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Could not read orders file.']);
    exit;
}

$orders = json_decode($file_content, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error decoding orders JSON.']);
    exit;
}

// Process each order
foreach ($orders as $order) {
    if (!isset($order['received_at']) || !isset($order['status'])) {
        continue;
    }

    try {
        $order_date = new DateTime($order['received_at']);
    } catch (Exception $e) {
        // Skip orders with invalid date format
        continue;
    }

    $status = strtolower($order['status']);

    if ($status !== 'confirmed' && $status !== 'cancelled') {
        continue;
    }

    foreach ($periods as $label => $start_date) {
        if ($order_date >= $start_date) {
            if ($status === 'confirmed') {
                $confirmed_counts[$label]++;
            } elseif ($status === 'cancelled') {
                $cancelled_counts[$label]++;
            }
        }
    }
}

// Prepare the final data structure for the chart
$chart_data = [
    'success' => true,
    'labels' => array_keys($periods),
    'datasets' => [
        [
            'label' => 'Confirmed Orders',
            'data' => array_values($confirmed_counts),
            'backgroundColor' => 'rgba(75, 192, 192, 0.5)',
            'borderColor' => 'rgba(75, 192, 192, 1)',
            'borderWidth' => 1
        ],
        [
            'label' => 'Cancelled Orders',
            'data' => array_values($cancelled_counts),
            'backgroundColor' => 'rgba(255, 99, 132, 0.5)',
            'borderColor' => 'rgba(255, 99, 132, 1)',
            'borderWidth' => 1
        ]
    ]
];

echo json_encode($chart_data);
?>
