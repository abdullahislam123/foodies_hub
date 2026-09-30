<?php
session_start();
include 'db.php';

// Auth Check
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['customer_id'];
$customer_name = $_SESSION['customer_name'] ?? "User";
$cart_count = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Orders | FoodiesHub</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f4f6f9; }
        .navbar-brand { color: #D70F64 !important; font-weight: 800; font-size: 1.5rem; }
        .nav-icon-btn { width: 45px; height: 45px; transition: all 0.2s; }
        .nav-icon-btn:hover { background-color: #f8f9fa; transform: translateY(-2px); }
        .order-card { border: none; border-radius: 16px; margin-bottom: 20px; background: white; box-shadow: 0 2px 15px rgba(0,0,0,0.03); transition: 0.3s; overflow: hidden; }
        .order-header { padding: 20px 25px; cursor: pointer; border-bottom: 1px solid #f0f0f0; background: #fff; }
        .status-badge { padding: 6px 14px; border-radius: 30px; font-size: 12px; font-weight: 600; text-transform: uppercase; }
        
        /* Classes for JS switching */
        .status-pending { background: #fff3cd; color: #856404; }
        .status-delivered { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .status-default { background: #e2e3e5; color: #383d41; }

        .items-container { background: #fcfcfc; padding: 25px; }
        .item-img { width: 60px; height: 60px; border-radius: 10px; object-fit: cover; border: 1px solid #eee; }
        
        /* Buttons */
        .btn-rate { background-color: #ffc107; color: #333; border-radius: 20px; font-size: 12px; padding: 6px 15px; border:none; font-weight: 600; transition: 0.2s; }
        .btn-rate:hover { background-color: #ffca2c; transform: translateY(-2px); }
        
        .btn-receipt { background-color: #198754; color: white; border-radius: 20px; font-size: 12px; padding: 6px 15px; border:none; font-weight: 600; transition: 0.2s; text-decoration:none; display:inline-block; }
        .btn-receipt:hover { background-color: #157347; color: white; transform: translateY(-2px); }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg bg-white shadow-sm sticky-top">
      <div class="container">
        <a class="navbar-brand" href="main.php"><i class="bi bi-fire text-danger me-2"></i>foodieshub</a>
        <div class="d-flex align-items-center">
            <a href="cart.php" class="btn border rounded-circle nav-icon-btn d-flex align-items-center justify-content-center me-3 position-relative">
                <i class="bi bi-bag"></i>
                <?php if($cart_count > 0): ?><span class="badge rounded-pill bg-danger position-absolute top-0 start-100 translate-middle border border-light p-1" style="font-size: 10px;"><?php echo $cart_count; ?></span><?php endif; ?>
            </a>
            <div class="d-none d-sm-block text-end lh-1">
                <span class="d-block small text-muted">Hello,</span>
                <span class="fw-bold text-dark"><?php echo htmlspecialchars($customer_name); ?></span>
            </div>
        </div>
      </div>
    </nav>

    <div class="container my-5">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <h3 class="fw-bold mb-4">Order History</h3>

                <?php
                $sql = "SELECT * FROM orders WHERE customer_id = ? ORDER BY id DESC";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $orders_shown = 0;

                if ($result && $result->num_rows > 0) {
                    while($order = $result->fetch_assoc()) {
                        $oid = $order['id'];
                        
                        // Check Empty Orders
                        $check_items = $conn->query("SELECT id FROM order_items WHERE order_id = '$oid'");
                        if($check_items->num_rows == 0) { continue; }
                        $orders_shown++;

                        $time_raw = $order['created_at'] ?? $order['order_date'] ?? 'now';
                        $date_display = date("d M, Y • h:i A", strtotime($time_raw));
                        $status = ucfirst(strtolower($order['status']));
                        $grand_total = number_format($order['total_amount'], 0);
                        
                        // Status Colors
                        $status_class = 'status-default';
                        if(strpos($status, 'Pending') !== false) $status_class = 'status-pending';
                        elseif(strpos($status, 'Deliver') !== false) $status_class = 'status-delivered';
                        elseif(strpos($status, 'Cancel') !== false) $status_class = 'status-cancelled';

                        // Check Review
                        $has_reviewed = false;
                        $r_check = $conn->query("SELECT id FROM reviews WHERE order_id = '$oid'");
                        if($r_check->num_rows > 0) { $has_reviewed = true; }
                ?>
                
                <div class="card order-card" data-oid="<?php echo $oid; ?>">
                    <div class="order-header">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                    <i class="bi bi-receipt text-secondary fs-5"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold m-0 text-dark">Order #<?php echo $oid; ?></h6>
                                    <small class="text-muted"><?php echo $date_display; ?></small>
                                </div>
                            </div>
                            <div class="text-end">
                                <span id="status-badge-<?php echo $oid; ?>" class="status-badge <?php echo $status_class; ?>"><?php echo $status; ?></span>
                                <div class="fw-bold mt-1">Rs. <?php echo $grand_total; ?></div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <button class="btn btn-sm btn-link text-decoration-none text-muted p-0" type="button" data-bs-toggle="collapse" data-bs-target="#detail-<?php echo $oid; ?>">
                                View Details <i class="bi bi-chevron-down"></i>
                            </button>
                            
                            <div id="action-btn-<?php echo $oid; ?>" class="d-flex gap-2 align-items-center">
                                <?php if($status == 'Delivered'): ?>
                                    
                                    <button class="btn-receipt shadow-sm" onclick="viewReceipt('<?php echo $oid; ?>')">
                                        <i class="bi bi-receipt me-1"></i> Receipt
                                    </button>

                                    <?php if(!$has_reviewed): ?>
                                        <button class="btn-rate shadow-sm" onclick="openReviewModal('<?php echo $oid; ?>')">
                                            <i class="bi bi-star-fill me-1"></i> Rate
                                        </button>
                                    <?php else: ?>
                                        <span class="badge bg-light text-success border"><i class="bi bi-check-circle-fill"></i> Rated</span>
                                    <?php endif; ?>

                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div id="detail-<?php echo $oid; ?>" class="collapse">
                        <div class="items-container border-top">
                             <?php 
                                $subtotal = 0;
                                $item_sql = "SELECT oi.*, p.name, p.image FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = '$oid'";
                                $items_res = $conn->query($item_sql);
                                if($items_res){
                                    while($item = $items_res->fetch_assoc()){
                                        $subtotal += ($item['price'] * $item['quantity']);
                                        $img = "assets/uploads/" . ($item['image'] ?? 'default.jpg');
                                        echo "<div class='d-flex justify-content-between align-items-center mb-2'>";
                                        echo "<div class='d-flex align-items-center gap-2'><img src='$img' class='item-img' onerror=\"this.src='https://placehold.co/60'\">";
                                        echo "<div><div class='small fw-bold'>{$item['name']}</div><div class='small text-muted'>Qty: {$item['quantity']}</div></div></div>";
                                        echo "<div class='small fw-bold'>Rs. ".($item['price']*$item['quantity'])."</div></div>";
                                    }
                                }
                             ?>
                        </div>
                    </div>
                </div>

                <?php 
                    } 
                } 
                if ($orders_shown == 0) { echo "<div class='text-center py-5'>No orders found.</div>"; }
                ?>
            </div>
        </div>
    </div>

    <div class="modal fade" id="reviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Rate your Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-0">
                    <form action="submit_review.php" method="POST">
                        <input type="hidden" name="order_id" id="modal_order_id">
                        <div class="text-center my-4">
                            <p class="text-muted small mb-2">How was your food?</p>
                            <select name="rating" class="form-select text-center fw-bold text-warning" style="font-size: 1.2rem;" required>
                                <option value="5">⭐⭐⭐⭐⭐ (Excellent)</option>
                                <option value="4">⭐⭐⭐⭐ (Good)</option>
                                <option value="3">⭐⭐⭐ (Average)</option>
                                <option value="2">⭐⭐ (Poor)</option>
                                <option value="1">⭐ (Terrible)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <textarea name="review_text" class="form-control" rows="3" placeholder="Write your feedback here..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-warning w-100 rounded-pill fw-bold">Submit Review</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="receiptModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered"> 
            <div class="modal-content rounded-4 border-0 shadow-lg" style="background-color: #f8f9fa;">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-0 px-4 pb-4" id="receiptContent">
                    <div class="text-center py-5">
                        <div class="spinner-border text-success" role="status"><span class="visually-hidden">Loading...</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function openReviewModal(orderId) {
            document.getElementById('modal_order_id').value = orderId;
            var myModal = new bootstrap.Modal(document.getElementById('reviewModal'));
            myModal.show();
        }

        // --- VIEW RECEIPT LOGIC ---
        function viewReceipt(orderId) {
            var myModal = new bootstrap.Modal(document.getElementById('receiptModal'));
            myModal.show();

            // Fetch Receipt Content via AJAX
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "receipt_view.php", true); // Ensure this file exists
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            
            xhr.onreadystatechange = function() {
                if (this.readyState === 4 && this.status === 200) {
                    document.getElementById('receiptContent').innerHTML = this.responseText;
                }
            };
            xhr.send("order_id=" + orderId);
        }

        // --- AUTO UPDATE STATUS LOGIC ---
        document.addEventListener("DOMContentLoaded", function() {
            function updateOrderStatuses() {
                let orderCards = document.querySelectorAll('.order-card');
                let orderIds = [];
                
                orderCards.forEach(card => {
                    let oid = card.getAttribute('data-oid');
                    if(oid) orderIds.push(oid);
                });

                if(orderIds.length === 0) return;

                fetch('check_status.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ order_ids: orderIds })
                })
                .then(response => response.json())
                .then(data => {
                    data.forEach(order => {
                        let badge = document.getElementById('status-badge-' + order.id);
                        let actionDiv = document.getElementById('action-btn-' + order.id);
                        
                        if (badge) {
                            let currentStatus = badge.innerText;
                            
                            if(currentStatus !== order.status) {
                                // 1. Update Status Text
                                badge.innerText = order.status;

                                // 2. Update Color
                                badge.className = 'status-badge'; 
                                if(order.status.includes('Pending')) badge.classList.add('status-pending');
                                else if(order.status.includes('Deliver')) badge.classList.add('status-delivered');
                                else if(order.status.includes('Cancel')) badge.classList.add('status-cancelled');
                                else badge.classList.add('status-default');

                                // 3. Show Buttons if Delivered
                                if(order.status === 'Delivered' && actionDiv) {
                                    // Only add if not already there
                                    if(!actionDiv.innerHTML.includes('Receipt')) {
                                        actionDiv.innerHTML = `
                                            <button class="btn-receipt shadow-sm me-2" onclick="viewReceipt('${order.id}')">
                                                <i class="bi bi-receipt me-1"></i> Receipt
                                            </button>
                                            <button class="btn-rate shadow-sm" onclick="openReviewModal('${order.id}')">
                                                <i class="bi bi-star-fill me-1"></i> Rate
                                            </button>
                                        `;
                                    }
                                }
                            }
                        }
                    });
                })
                .catch(err => console.error('Error fetching status:', err));
            }

            // Check every 5 seconds
            setInterval(updateOrderStatuses, 5000);
        });
    </script>
</body>
</html>