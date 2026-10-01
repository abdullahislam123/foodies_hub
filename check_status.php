<?php
date_default_timezone_set("Asia/Karachi");
session_start();
include "db.php";
header("Content-Type: application/json");
if(!isset($_SESSION["rest_id"])) { echo json_encode(["error"=>"unauth"]); exit(); }
$rest_id = (int)$_SESSION["rest_id"];
$r = $conn->query("SELECT COUNT(*) as cnt, MAX(id) as lid FROM orders WHERE restaurant_id=$rest_id AND status='Pending'");
$row = $r ? $r->fetch_assoc() : ["cnt"=>0,"lid"=>0];
echo json_encode(["pending_count"=>(int)$row["cnt"],"latest_order_id"=>(int)$row["lid"]]);
?>
