<?php
include 'db.php';

if(isset($_GET['order_id'])){
    $id = $_GET['order_id'];
    $res = $conn->query("SELECT status FROM orders WHERE id='$id'");
    if($row = $res->fetch_assoc()){
        echo $row['status'];
    }
}
?>