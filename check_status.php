<?php
// File Name: check_status.php
include 'db.php'; // Apni database connection file include karein

header('Content-Type: application/json');

// Frontend se Order IDs ki list JSON format mein aayegi
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['order_ids']) || empty($input['order_ids'])) {
    echo json_encode([]);
    exit;
}

$ids = array_map('intval', $input['order_ids']);
$ids_string = implode(',', $ids);

// Database se in IDs ka latest status fetch karein
$sql = "SELECT id, status FROM orders WHERE id IN ($ids_string)";
$result = $conn->query($sql);

$updates = [];
while ($row = $result->fetch_assoc()) {
    $updates[] = [
        'id' => $row['id'],
        'status' => ucfirst(strtolower($row['status'])) // Formatting like 'Pending', 'Delivered'
    ];
}

echo json_encode($updates);
?>