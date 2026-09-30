<?php
session_start();
include 'db.php';

// Security: Check Login
if(!isset($_SESSION['rest_id'])){ header("Location: restaurent_login.php"); exit(); }

$rest_id = $_SESSION['rest_id']; 
$rest_name = $_SESSION['rest_name'];
$msg = "";

// --- STATUS UPDATE LOGIC (UNCHANGED) ---
if(isset($_POST['update_status'])){
    $oid = $_POST['order_id'];
    $status = $_POST['status'];
    
    // Ensure order belongs to this restaurant
    $sql = "UPDATE orders SET status='$status' WHERE id='$oid' AND restaurant_id='$rest_id'";
    if($conn->query($sql)){
        $msg = "<div class='alert alert-success alert-dismissible fade show shadow-sm border-0'><i class='bi bi-check-circle-fill me-2'></i> Status updated to: <strong>$status</strong> <button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
    } else {
        $msg = "<div class='alert alert-danger shadow-sm border-0'>Error updating status</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Orders - <?php echo $rest_name; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #ff6b6b;
            --dark-bg: #2d3436;
            --light-bg: #f8f9fa;
        }

        body { 
            background-color: var(--light-bg); 
            font-family: 'Poppins', sans-serif; 
            color: #4a4a4a;
        }

        /* Navbar */
        .navbar {
            background: #ffffff !important;
            box-shadow: 0 2px 15px rgba(0,0,0,0.04);
            padding: 1rem 0;
        }
        .navbar-brand {
            color: var(--dark-bg) !important;
            font-size: 1.5rem;
        }

        /* Order Card Styling */
        .card-order { 
            border: none; 
            border-radius: 16px; 
            box-shadow: 0 4px 20px rgba(0,0,0,0.03); 
            transition: all 0.3s ease;
            overflow: hidden;
            background: #fff;
            margin-bottom: 25px;
        }
        .card-order:hover { 
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.08); 
        }

        /* Status Header Colors */
        .status-header { padding: 15px 20px; border-bottom: 1px solid rgba(0,0,0,0.05); }
        .status-Pending { background-color: #fff3cd; color: #856404; }
        .status-Confirmed { background-color: #cce5ff; color: #004085; }
        .status-Preparing { background-color: #e2e6ea; color: #343a40; border-left: 5px solid #ff6b6b; }
        .status-OnWay { background-color: #d1ecf1; color: #0c5460; }
        .status-Delivered { background-color: #d4edda; color: #155724; }
        .status-Cancelled { background-color: #f8d7da; color: #721c24; }

        /* Content Styling */
        .info-label { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; color: #888; font-weight: 600; margin-bottom: 8px; }
        .customer-icon { width: 35px; height: 35px; background: #f0f2f5; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--primary-color); }
        
        .list-group-item { border-color: #f1f1f1; padding: 10px 0; }
        .total-box { background: #fdfdfd; border: 1px dashed #ddd; border-radius: 10px; padding: 15px; }

        /* Form Elements */
        .form-select { border-radius: 10px; padding: 10px; background-color: #f8f9fa; border: 1px solid #eee; cursor: pointer; }
        .btn-update { background-color: var(--dark-bg); color: white; border-radius: 10px; padding: 10px; }
        .btn-update:hover { background-color: #000; color: white; }

        /* Empty State */
        .empty-state { text-align: center; padding: 60px 20px; opacity: 0.7; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg sticky-top">
  <div class="container">
    <a class="navbar-brand fw-bold" href="#">
        <span class="text-primary"><i class="bi bi-shop"></i></span> <?php echo $rest_name; ?>
    </a>
    <div class="d-flex gap-2">
        <a href="restaurent_dashboard.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="bi bi-grid-fill me-1"></i> Menu Dashboard
        </a>
        <a href="restaurent_logout.php" class="btn btn-danger btn-sm rounded-pill px-3">Logout</a>
    </div>
  </div>
</nav>

<div class="container py-5">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">Incoming Orders</h2>
            <p class="text-muted small">Manage live orders and update statuses.</p>
        </div>
        <button class="btn btn-light shadow-sm rounded-circle" onclick="location.reload();" title="Refresh Orders">
            <i class="bi bi-arrow-clockwise"></i>
        </button>
    </div>

    <?php echo $msg; ?>

    <div class="row">
        <div class="col-12">
            <?php
            // Fetch Orders
            $sql = "SELECT o.*, o.address as cust_address, c.name as cust_name, c.phone as cust_phone 
                    FROM orders o 
                    JOIN customers c ON o.customer_id = c.id 
                    WHERE o.restaurant_id = '$rest_id' 
                    ORDER BY o.id DESC";
            
            $result = $conn->query($sql);

            if($result && $result->num_rows > 0){
                while($order = $result->fetch_assoc()){
                    $oid = $order['id'];
                    $status = $order['status'];
                    $time = $order['order_date'] ?? $order['created_at'] ?? 'now';
                    
                    // Helper for class name
                    $statusClean = str_replace(' ', '', $status);
            ?>
            
            <div class="card card-order">
                <div class="status-header status-<?php echo $statusClean; ?> d-flex justify-content-between align-items-center">
                    <div>
                        <span class="fw-bold fs-5">#ORDER-<?php echo $oid; ?></span>
                        <span class="ms-2 small opacity-75"><i class="bi bi-clock"></i> <?php echo date("d M, h:i A", strtotime($time)); ?></span>
                    </div>
                    <div class="fw-bold text-uppercase small">
                        <?php echo $status == 'On Way' ? '<i class="bi bi-scooter"></i> On Way' : $status; ?>
                    </div>
                </div>

                <div class="card-body p-4">
                    <div class="row g-4">
                        
                        <div class="col-lg-4 col-md-6 border-end-md">
                            <div class="info-label">Customer Details</div>
                            
                            <div class="d-flex align-items-center mb-3">
                                <div class="customer-icon me-3"><i class="bi bi-person-fill fs-5"></i></div>
                                <div>
                                    <div class="fw-bold text-dark"><?php echo $order['cust_name']; ?></div>
                                    <a href="tel:<?php echo $order['cust_phone']; ?>" class="text-decoration-none text-muted small">
                                        <i class="bi bi-telephone me-1"></i> <?php echo $order['cust_phone']; ?>
                                    </a>
                                </div>
                            </div>

                            <div class="d-flex align-items-start">
                                <div class="customer-icon me-3 text-danger"><i class="bi bi-geo-alt-fill fs-5"></i></div>
                                <div>
                                    <div class="small text-muted" style="line-height: 1.6;"><?php echo $order['cust_address']; ?></div>
                                </div>
                            </div>

                            <?php if(!empty($order['instructions'])): ?>
                                <div class="alert alert-warning small mt-3 mb-0 py-2 border-0">
                                    <i class="bi bi-exclamation-circle-fill me-1"></i> <strong>Note:</strong> <?php echo $order['instructions']; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="col-lg-5 col-md-6 border-end-md">
                            <div class="info-label">Order Summary</div>
                            <ul class="list-group list-group-flush mb-3">
                                <?php
                                $item_sql = "SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = '$oid'";
                                $items = $conn->query($item_sql);
                                if($items){
                                    while($item = $items->fetch_assoc()){
                                ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        <div>
                                            <span class="badge bg-light text-dark border me-2 rounded-1"><?php echo $item['quantity']; ?>x</span>
                                            <span class="text-dark fw-medium"><?php echo $item['name']; ?></span>
                                        </div>
                                        <span class="text-muted small fw-bold">Rs. <?php echo $item['price'] * $item['quantity']; ?></span>
                                    </li>
                                <?php 
                                    }
                                }
                                ?>
                            </ul>
                            
                            <div class="total-box d-flex justify-content-between align-items-center">
                                <span class="text-muted small fw-bold">TOTAL AMOUNT</span>
                                <span class="fs-5 fw-bold text-success">Rs. <?php echo number_format($order['total_amount']); ?></span>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-12 d-flex flex-column justify-content-center">
                            <div class="bg-light p-3 rounded-3">
                                <div class="info-label mb-2">Update Status</div>
                                <form method="POST">
                                    <input type="hidden" name="order_id" value="<?php echo $oid; ?>">
                                    
                                    <div class="mb-3">
                                        <select name="status" class="form-select fw-bold text-dark">
                                            <option value="Pending" <?php if($status=='Pending') echo 'selected'; ?>>🟡 Pending</option>
                                            <option value="Confirmed" <?php if($status=='Confirmed') echo 'selected'; ?>>🔵 Confirm</option>
                                            <option value="Preparing" <?php if($status=='Preparing') echo 'selected'; ?>>🔥 Cooking</option>
                                            <option value="On Way" <?php if($status=='On Way') echo 'selected'; ?>>🛵 On Way</option>
                                            <option value="Delivered" <?php if($status=='Delivered') echo 'selected'; ?>>✅ Delivered</option>
                                            <option value="Cancelled" <?php if($status=='Cancelled') echo 'selected'; ?>>❌ Cancel</option>
                                        </select>
                                    </div>

                                    <button type="submit" name="update_status" class="btn btn-update w-100 fw-bold shadow-sm">
                                        Update <i class="bi bi-check-lg ms-1"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <?php 
                } 
            } else {
            ?>
                <div class="empty-state">
                    <i class="bi bi-clipboard-x display-1 text-muted mb-3 d-block"></i>
                    <h3 class="fw-bold text-dark">No Orders Yet</h3>
                    <p class="text-muted">Wait for customers to place orders. They will appear here instantly.</p>
                </div>
            <?php
            }
            ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>