<?php
require_once 'admin/config.php';

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['cart']) || empty($data['cart'])) {
    echo json_encode(["status" => "error", "message" => "Empty cart"]);
    exit;
}

$success_count = 0;
$total_items = count($data['cart']);

foreach ($data['cart'] as $item) {
    $product_id = intval($item['id']);
    $product_name = $conn->real_escape_string($item['name']);
    $quantity = intval($item['qty']);
    $total_amount = $item['price'] * $quantity;
    $customer_name = "Web Customer"; // Default since no login session yet

    $sql = "INSERT INTO sales (product_id, product_name, quantity, total_amount, customer_name) 
            VALUES ($product_id, '$product_name', $quantity, '$total_amount', '$customer_name')";
    
    if ($conn->query($sql)) {
        $success_count++;
    }
}

if ($success_count > 0) {
    echo json_encode(["status" => "success", "count" => $success_count]);
} else {
    echo json_encode(["status" => "error", "message" => "Database error: " . $conn->error]);
}
?>
