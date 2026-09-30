<?php
session_start();
include 'db.php';

// 1. Check Login
if(!isset($_SESSION['rest_id'])){
    header("Location: restaurent_login.php");
    exit();
}

$rest_id = $_SESSION['rest_id'];

if(isset($_GET['id'])){
    $id = $_GET['id'];

    // 2. SECURITY CHECK: Sirf wohi delete kare jo is restaurant ka ho
    // "AND restaurant_id = '$rest_id'" bohat zaroori hai
    $sql = "DELETE FROM products WHERE id = '$id' AND restaurant_id = '$rest_id'";
    
    if($conn->query($sql)){
        header("Location: restaurent_dashboard.php?msg=deleted");
    } else {
        echo "Error or Permission Denied.";
    }
}
?>