<?php
session_start();
include 'db.php';

if (!isset($_SESSION['customer_id'])) { header("Location: login.php"); exit(); }

$cid = $_SESSION['customer_id'];

// Fetch ALL Orders for this Customer (Latest First)
$sql = "SELECT * FROM orders WHERE customer_id='$cid' ORDER BY id DESC";
$res = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Orders - Foodies Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        body { background-color: #F7F7F7; }
        .order-card { border: none; border-radius: 15px; background: white; box-shadow: 0 4px 15px rgba(0,0,0,0.05); transition: 0.3s; }
        .order-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .progress { height: 10px; border-radius: 10px; background-color: #e9ecef; }
        .status-icon { font-size: 1.5rem; margin-bottom: 10px; display: block; }
        .btn-back { position: fixed; bottom: 20px; right: 20px; z-index: 100; border-radius: 50px; padding: 10px 25px; font-weight: bold; }
    </style>
</head>
<body>

    <div class="container mt-5 pb-5">
        <h2 class="fw-bold mb-4 text-center" style="color:#D70F64;">📦 My Orders & Tracking</h2>
        
        <div class="row justify-content-center">
            <div class="col-md-8">
                
                <?php
                if ($res->num_rows > 0) {
                    while ($order = $res->fetch_assoc()) {
                        $oid = $order['id'];
                        $status = $order['status'];
                        
                        // Progress Logic
                        $progress = 0;
                        $color = "bg-warning";
                        $icon = "bi-hourglass-split";
                        
                        if($status == "Pending") { $progress = 10; $icon="bi-clipboard-data"; }
                        elseif($status == "Confirmed") { $progress = 25; $icon="bi-check-circle"; }
                        elseif($status == "Preparing") { $progress = 50; $icon="bi-fire"; $color="bg-info"; }
                        elseif($status == "On Way") { $progress = 75; $icon="bi-scooter"; $color="bg-primary"; }
                        elseif($status == "Delivered") { $progress = 100; $icon="bi-emoji-smile"; $color="bg-success"; }
                        elseif($status == "Cancelled") { $progress = 100; $icon="bi-x-circle"; $color="bg-danger"; }

                        // Items Fetch Karna (Modal k liye)
                        $item_sql = "SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE order_id='$oid'";
                        $item_res = $conn->query($item_sql);
                ?>

                <div class="card order-card mb-4 p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="fw-bold mb-1">Order #<?php echo $oid; ?></h5>
                            <small class="text-muted"><?php echo date("d M Y - h:i A", strtotime($order['order_date'])); ?></small>
                        </div>
                        <div class="text-end">
                            <h5 class="fw-bold text-dark">Rs. <?php echo $order['total_amount']; ?></h5>
                            <span class="badge <?php echo $color; ?>"><?php echo $status; ?></span>
                        </div>
                    </div>

                    <div class="progress mb-3">
                        <div class="progress-bar <?php echo $color; ?> progress-bar-striped progress-bar-animated" role="progressbar" style="width: <?php echo $progress; ?>%"></div>
                    </div>
                    <p class="small text-muted mb-3"><i class="bi <?php echo $icon; ?>"></i> Status: <strong><?php echo $status; ?></strong></p>

                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-dark btn-sm w-100" type="button" data-bs-toggle="collapse" data-bs-target="#details<?php echo $oid; ?>">
                            View Items <i class="bi bi-chevron-down"></i>
                        </button>
                        <?php if($status == "Pending") { ?>
                            <?php } ?>
                    </div>

                    <div class="collapse mt-3" id="details<?php echo $oid; ?>">
                        <div class="card card-body bg-light border-0">
                            <h6 class="fw-bold">Items Ordered:</h6>
                            <ul class="list-group list-group-flush bg-transparent">
                                <?php while($item = $item_res->fetch_assoc()) { ?>
                                    <li class="list-group-item bg-transparent d-flex justify-content-between small">
                                        <span><?php echo $item['quantity']; ?>x <?php echo $item['name']; ?></span>
                                        <span>Rs. <?php echo $item['price'] * $item['quantity']; ?></span>
                                    </li>
                                <?php } ?>
                            </ul>
                            <div class="mt-2 pt-2 border-top d-flex justify-content-between small">
                                <span>Delivery Address:</span>
                                <span class="text-end text-muted" style="max-width: 60%;"><?php echo $order['address']; ?></span>
                            </div>
                        </div>
                    </div>

                </div>

                <?php 
                    } 
                } else {
                    echo "<div class='text-center py-5'><h4 class='text-muted'>No orders found! 🍔</h4><a href='index.php' class='btn btn-dark mt-3'>Order Now</a></div>";
                }
                ?>

            </div>
        </div>
    </div>

    <a href="main.php" class="btn btn-dark btn-back shadow">Back to Menu</a>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>