<?php
// =======================================
// LOCAL (XAMPP) - Testing ke liye
// =======================================
// $servername = "localhost";
// $username   = "root";
// $password   = "";
// $dbname     = "foodies_hub_db";

// =======================================
// LIVE (InfinityFree) - Production
// =======================================
$servername = "sql110.infinityfree.com";

$username   = "if0_43049502";
$password   = "dv4ApDTB4WSy";   
$dbname     = "if0_43049502_foodies_hub"; 

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>