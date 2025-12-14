<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['customer_id'])) {
    echo json_encode(['status' => 'login_required']);
    exit();
}

if (isset($_POST['product_id'])) {
    $user_id = $_SESSION['customer_id'];
    $product_id = (int)$_POST['product_id'];

    // Check if already favorite
    $check = $conn->query("SELECT * FROM favorites WHERE user_id='$user_id' AND product_id='$product_id'");

    if ($check->num_rows > 0) {
        // Already liked -> Remove it (Unlike)
        $conn->query("DELETE FROM favorites WHERE user_id='$user_id' AND product_id='$product_id'");
        echo json_encode(['status' => 'removed']);
    } else {
        // Not liked -> Add it (Like)
        $conn->query("INSERT INTO favorites (user_id, product_id) VALUES ('$user_id', '$product_id')");
        echo json_encode(['status' => 'added']);
    }
}
?>