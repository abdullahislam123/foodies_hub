<?php
include 'db.php';

if(!isset($_GET['oid'])){ echo "Invalid Request"; exit(); }

$oid = $_GET['oid'];

// Fetch Order Details
$sql = "SELECT o.*, c.name as cust_name, c.phone as cust_phone, r.name as rest_name 
        FROM orders o 
        JOIN customers c ON o.customer_id = c.id 
        JOIN restaurants r ON o.restaurant_id = r.id 
        WHERE o.id = '$oid'";
$res = $conn->query($sql);

if($res->num_rows == 0) { echo "Order not found"; exit(); }
$order = $res->fetch_assoc();

// Items
$items = $conn->query("SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = '$oid'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Receipt #<?php echo $oid; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; font-family: 'Poppins', sans-serif; }
        
        /* Colorful Header */
        .top-header {
            background: linear-gradient(135deg, #D70F64 0%, #ff4757 100%);
            color: white;
            padding: 40px 20px 80px 20px;
            text-align: center;
            border-bottom-left-radius: 30px;
            border-bottom-right-radius: 30px;
        }
        
        /* Floating Card */
        .receipt-card {
            background: white;
            margin: -60px 20px 20px 20px;
            padding: 25px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            text-align: center;
        }

        .status-badge {
            background: #eaffea; color: #00b894;
            padding: 5px 15px; border-radius: 50px;
            font-size: 12px; font-weight: 700; text-transform: uppercase;
            letter-spacing: 1px; display: inline-block; margin-bottom: 15px;
        }
        
        .amount-box { margin: 20px 0; border-top: 2px dashed #eee; border-bottom: 2px dashed #eee; padding: 20px 0; }
        .item-row { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 14px; color: #555; text-align: left; }
        .info-label { font-size: 12px; color: #aaa; text-transform: uppercase; letter-spacing: 0.5px; }
        .info-val { font-size: 15px; font-weight: 600; color: #333; margin-bottom: 15px; }
    </style>
</head>
<body>

    <div class="top-header">
        <i class="bi bi-fire fs-1 mb-2"></i>
        <h4 class="fw-bold">FoodiesHub</h4>
        <p class="opacity-75 small">Authentic Taste, Delivered.</p>
    </div>

    <div class="receipt-card">
        <div class="status-badge"><i class="bi bi-check-circle-fill me-1"></i> <?php echo $order['status']; ?></div>
        
        <div class="row text-start">
            <div class="col-6">
                <div class="info-label">Customer</div>
                <div class="info-val"><?php echo explode(' ', $order['cust_name'])[0]; ?></div>
            </div>
            <div class="col-6 text-end">
                <div class="info-label">Order ID</div>
                <div class="info-val">#<?php echo $order['id']; ?></div>
            </div>
            <div class="col-12">
                <div class="info-label">Restaurant</div>
                <div class="info-val"><i class="bi bi-shop me-1 text-danger"></i> <?php echo $order['rest_name']; ?></div>
            </div>
        </div>

        <div class="amount-box">
            <h6 class="text-muted small fw-bold mb-3 text-start">ORDER ITEMS</h6>
            <?php while($item = $items->fetch_assoc()){ ?>
                <div class="item-row">
                    <span><b class="text-dark me-2"><?php echo $item['quantity']; ?>x</b> <?php echo $item['name']; ?></span>
                    <span class="fw-bold text-dark">Rs. <?php echo $item['price']*$item['quantity']; ?></span>
                </div>
            <?php } ?>
        </div>

        <div class="d-flex justify-content-between align-items-center">
            <span class="text-muted fw-bold">Total Amount</span>
            <h2 class="text-danger fw-bold m-0">Rs. <?php echo number_format($order['total_amount']); ?></h2>
        </div>
        
        <p class="text-muted mt-4 small mb-0"><i class="bi bi-clock me-1"></i> <?php echo date("d M Y, h:i A", strtotime($order['created_at'] ?? 'now')); ?></p>
    </div>

    <div class="text-center pb-5">
        <small class="text-muted">Thank you for your order!</small>
    </div>

</body>
</html>