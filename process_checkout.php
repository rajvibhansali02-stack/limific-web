<?php
require_once 'admin/config.php';

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['cart']) || empty($data['cart'])) {
    echo json_encode(["status" => "error", "message" => "Empty cart"]);
    exit;
}

// Start database transaction
$conn->begin_transaction();

try {
    $customer_name = $_SESSION['user_name'] ?? "Web Customer";
    
    // Prepare SQL statement
    $stmt = $conn->prepare("INSERT INTO sales (product_id, product_name, quantity, total_amount, customer_name) VALUES (?, ?, ?, ?, ?)");
    
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . $conn->error);
    }
    
    foreach ($data['cart'] as $item) {
        $product_id = intval($item['id']);
        $product_name = $item['name'];
        $quantity = intval($item['qty']);
        $total_amount = floatval($item['price']) * $quantity;
        
        $stmt->bind_param("isids", $product_id, $product_name, $quantity, $total_amount, $customer_name);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to execute statement: " . $stmt->error);
        }
    }
    
    $stmt->close();
    
    // Commit transaction - all items saved safely!
    $conn->commit();
    
    echo json_encode(["status" => "success", "count" => count($data['cart'])]);
} catch (Exception $e) {
    // Rollback transaction on failure - no partial data saved!
    $conn->rollback();
    echo json_encode(["status" => "error", "message" => "Checkout failed: " . $e->getMessage()]);
}
?>
