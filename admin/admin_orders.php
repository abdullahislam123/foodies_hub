<?php
session_start();
include '../db.php';

// Security Check
if (!isset($_SESSION['admin_logged_in'])) { header("Location: admin_login.php"); exit(); }

// NOTE: Status Update Logic Removed (Read Only)

// FETCH STATS
$pending_count = $conn->query("SELECT count(*) as c FROM orders WHERE status='Pending'")->fetch_assoc()['c'];
$delivered_count = $conn->query("SELECT count(*) as c FROM orders WHERE status='Delivered'")->fetch_assoc()['c'];
$cancelled_count = $conn->query("SELECT count(*) as c FROM orders WHERE status='Cancelled'")->fetch_assoc()['c'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Global Orders (View Only)</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    
    <style>
        :root { --primary: #D70F64; --bg-light: #f3f4f7; --card-shadow: 0 10px 30px rgba(0,0,0,0.04); }
        body { font-family: 'DM Sans', sans-serif; background-color: var(--bg-light); color: #4a5568; }
        
        /* Sidebar */
        .sidebar { height: 100vh; width: 260px; position: fixed; top: 0; left: 0; background: #1a1c23; color: white; padding-top: 30px; z-index: 1000; }
        .sidebar-brand { font-size: 1.4rem; font-weight: 700; text-align: center; margin-bottom: 40px; letter-spacing: 1px; }
        .nav-link { color: #a0aec0; padding: 15px 30px; font-weight: 500; display: flex; align-items: center; border-left: 4px solid transparent; transition: 0.3s; }
        .nav-link:hover, .nav-link.active { background-color: rgba(255, 255, 255, 0.05); color: white; border-left-color: var(--primary); }
        .nav-link i { margin-right: 15px; font-size: 1.1rem; }

        /* Main Content */
        .main-content { margin-left: 260px; padding: 40px; }
        
        /* Stats Cards */
        .stats-card { background: white; border-radius: 12px; padding: 20px; box-shadow: var(--card-shadow); border: none; display: flex; align-items: center; justify-content: space-between; transition: 0.3s; }
        .stats-card:hover { transform: translateY(-3px); }
        .card-value { font-size: 1.8rem; font-weight: 700; color: #1a1c23; margin-bottom: 2px; }
        .card-label { color: #64748b; font-size: 0.85rem; font-weight: 500; }
        .icon-box { width: 45px; height: 45px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; }
        
        /* Colors */
        .bg-orange-soft { background: #fff7ed; color: #ea580c; }
        .bg-green-soft { background: #f0fdf4; color: #16a34a; }
        .bg-red-soft { background: #fef2f2; color: #dc2626; }

        /* Table Card */
        .table-card { background: white; border-radius: 16px; padding: 0; box-shadow: var(--card-shadow); overflow: hidden; margin-top: 30px; }
        .card-header-custom { padding: 25px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; }
        .table thead th { background-color: #f8fafc; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; padding: 15px 25px; color: #64748b; font-weight: 700; border: none; }
        .table tbody td { padding: 15px 25px; vertical-align: middle; color: #334155; border-bottom: 1px solid #f1f5f9; }

        /* Badges */
        .badge-soft { padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 700; border: 1px solid transparent; }
        .badge-pending { background-color: #fff7ed; color: #ea580c; border-color: #ffedd5; }
        .badge-confirmed { background-color: #eff6ff; color: #2563eb; border-color: #dbeafe; }
        .badge-preparing { background-color: #e0e7ff; color: #4338ca; border-color: #c7d2fe; }
        .badge-delivered { background-color: #f0fdf4; color: #16a34a; border-color: #dcfce7; }
        .badge-cancelled { background-color: #fef2f2; color: #dc2626; border-color: #fee2e2; }

        @media (max-width: 768px) {
            .sidebar { width: 70px; } .sidebar-brand span, .nav-link span { display: none; }
            .main-content { margin-left: 70px; padding: 20px; }
        }
    </style>
</head>
<body>

    <div class="sidebar shadow">
        <div class="sidebar-brand"><i class="bi bi-shield-lock"></i> <span>ADMIN</span></div>
        <nav class="nav flex-column">
            <a href="admin_dashboard.php" class="nav-link"><i class="bi bi-grid"></i> <span>Dashboard</span></a>
            <a href="admin_add_restaurant.php" class="nav-link"><i class="bi bi-shop"></i> <span>Add Partner</span></a>
            <a href="admin_orders.php" class="nav-link active"><i class="bi bi-receipt"></i> <span>Orders</span></a>
            <a href="logout.php" class="nav-link mt-auto mb-4 text-danger"><i class="bi bi-box-arrow-right"></i> <span>Logout</span></a>
        </nav>
    </div>

    <div class="main-content">
        
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h3 class="fw-bold text-dark m-0">Global Orders</h3>
                <p class="text-muted small m-0">Monitor all incoming orders (View Only)</p>
            </div>
            <a href="admin_dashboard.php" class="btn btn-outline-dark rounded-pill px-4 btn-sm fw-bold">
                <i class="bi bi-arrow-left me-1"></i> Dashboard
            </a>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="stats-card">
                    <div>
                        <div class="card-value"><?php echo $pending_count; ?></div>
                        <div class="card-label">Pending Orders</div>
                    </div>
                    <div class="icon-box bg-orange-soft"><i class="bi bi-hourglass-split"></i></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div>
                        <div class="card-value"><?php echo $delivered_count; ?></div>
                        <div class="card-label">Delivered</div>
                    </div>
                    <div class="icon-box bg-green-soft"><i class="bi bi-check-circle"></i></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div>
                        <div class="card-value"><?php echo $cancelled_count; ?></div>
                        <div class="card-label">Cancelled</div>
                    </div>
                    <div class="icon-box bg-red-soft"><i class="bi bi-x-circle"></i></div>
                </div>
            </div>
        </div>

        <div class="table-card">
            <div class="card-header-custom">
                <h5 class="fw-bold m-0 text-dark">Order History</h5>
            </div>

            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Order ID</th>
                            <th>Restaurant</th>
                            <th>Customer Info</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT o.*, c.name as cust_name, c.phone, r.name as rest_name, r.image as rest_image 
                                FROM orders o 
                                JOIN customers c ON o.customer_id = c.id 
                                LEFT JOIN restaurants r ON o.restaurant_id = r.id 
                                ORDER BY o.id DESC";
                        $res = $conn->query($sql);

                        if($res->num_rows > 0) {
                            while ($row = $res->fetch_assoc()) {
                                $order_id = $row['id'];
                                $rest_img = !empty($row['rest_image']) ? "../assets/uploads/".$row['rest_image'] : "https://placehold.co/40";
                                
                                $badgeClass = 'badge-pending';
                                if($row['status'] == 'Confirmed') $badgeClass = 'badge-confirmed';
                                if($row['status'] == 'Preparing') $badgeClass = 'badge-preparing';
                                if($row['status'] == 'Delivered') $badgeClass = 'badge-delivered';
                                if($row['status'] == 'Cancelled') $badgeClass = 'badge-cancelled';
                        ?>
                        <tr>
                            <td class="ps-4"><span class="fw-bold text-dark">#<?php echo $row['id']; ?></span></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="<?php echo $rest_img; ?>" style="width:35px; height:35px; border-radius:8px; margin-right:12px; object-fit:cover; border:1px solid #eee;">
                                    <span class="fw-bold text-dark small"><?php echo $row['rest_name'] ? $row['rest_name'] : 'Deleted'; ?></span>
                                </div>
                            </td>
                            <td>
                                <div class="fw-bold text-dark small"><?php echo $row['cust_name']; ?></div>
                                <div class="text-muted" style="font-size: 0.75rem;"><?php echo $row['phone']; ?></div>
                            </td>
                            <td class="fw-bold text-success">Rs. <?php echo number_format($row['total_amount']); ?></td>
                            <td><span class="badge badge-soft <?php echo $badgeClass; ?>"><?php echo $row['status']; ?></span></td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-light border shadow-sm text-secondary" data-bs-toggle="modal" data-bs-target="#viewOrder<?php echo $order_id; ?>">
                                    View <i class="bi bi-arrow-right ms-1"></i>
                                </button>
                            </td>
                        </tr>

                        <div class="modal fade" id="viewOrder<?php echo $order_id; ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow-lg rounded-4">
                                    <div class="modal-header border-bottom-0 pb-0">
                                        <h5 class="modal-title fw-bold">Order #<?php echo $order_id; ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <div class="p-3 bg-light rounded mb-3">
                                            <small class="text-muted fw-bold text-uppercase">Delivery Address</small>
                                            <p class="mb-0 mt-1 small"><i class="bi bi-geo-alt-fill text-danger me-1"></i> <?php echo $row['address']; ?></p>
                                        </div>
                                        
                                        <h6 class="fw-bold mb-3">Items Ordered</h6>
                                        <ul class="list-group list-group-flush border rounded-3 overflow-hidden">
                                            <?php
                                            $item_sql = "SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE order_id='$order_id'";
                                            $item_res = $conn->query($item_sql);
                                            while($item = $item_res->fetch_assoc()) {
                                            ?>
                                            <li class="list-group-item d-flex justify-content-between align-items-center px-3 py-2">
                                                <span><?php echo $item['quantity']; ?>x <?php echo $item['name']; ?></span>
                                                <span class="fw-bold small">Rs. <?php echo $item['price']*$item['quantity']; ?></span>
                                            </li>
                                            <?php } ?>
                                        </ul>
                                        
                                        <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                                            <span class="text-muted">Total Amount</span>
                                            <span class="fs-5 fw-bold text-primary">Rs. <?php echo number_format($row['total_amount']); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php 
                            } 
                        } else { echo "<tr><td colspan='6' class='text-center py-5 text-muted'>No orders found.</td></tr>"; }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>