<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

// 1. Check Login
if (!isset($_SESSION['customer_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Please login first']);
    exit();
}

$uid = $_SESSION['customer_id'];

// ==========================================
// CASE 1: UPDATE INFO (ONLY NAME)
// ==========================================
if (isset($_POST['action']) && $_POST['action'] == 'update_info') {
    
    $name = trim($_POST['name']);

    if(empty($name)) {
        echo json_encode(['status' => 'error', 'message' => 'Name cannot be empty']);
        exit();
    }

    // --- CHANGE: Sirf Name update kar rahe hain ---
    $sql = "UPDATE customers SET name = ? WHERE id = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("si", $name, $uid); // 's' for string (name), 'i' for integer (id)
        
        if ($stmt->execute()) {
            // Update Session
            $_SESSION['customer_name'] = $name;
            echo json_encode(['status' => 'success', 'message' => 'Name updated successfully!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Update Failed: ' . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Query Error: ' . $conn->error]);
    }
    exit();
}

// ==========================================
// CASE 2: UPDATE ADDRESS (Same as before)
// ==========================================
if (isset($_POST['action']) && $_POST['action'] == 'update_address') {
    
    $address = trim($_POST['address']);

    if(empty($address)) {
        echo json_encode(['status' => 'error', 'message' => 'Address cannot be empty']);
        exit();
    }

    // Note: Make sure 'address' column exists in 'customers' table
    $sql = "UPDATE customers SET address = ? WHERE id = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("si", $address, $uid);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Address updated successfully!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Update Failed: ' . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Query Error: ' . $conn->error]);
    }
    exit();
}

echo json_encode(['status' => 'error', 'message' => 'Invalid Request Action']);
?>