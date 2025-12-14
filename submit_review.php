<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['customer_id'])) {
    
    $customer_id = $_SESSION['customer_id'];
    $order_id = $_POST['order_id'];
    $rating = $_POST['rating'];
    $review = $_POST['review_text'];

    // Check karein k kahin pehle se review to nahi diya?
    $check = $conn->query("SELECT * FROM reviews WHERE order_id = '$order_id'");
    if($check->num_rows > 0){
        echo "<script>alert('You have already reviewed this order!'); window.location.href='my_orders.php';</script>";
        exit();
    }

    // Insert Review
    $stmt = $conn->prepare("INSERT INTO reviews (order_id, customer_id, rating, review_text) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $order_id, $customer_id, $rating, $review);

    if ($stmt->execute()) {
        echo "<script>alert('Review Submitted Successfully!'); window.location.href='my_orders.php';</script>";
    } else {
        echo "<script>alert('Error submitting review.'); window.location.href='my_orders.php';</script>";
    }
} else {
    header("Location: my_orders.php");
}
?>