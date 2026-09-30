<?php
include 'db.php';

if(isset($_POST['order_id'])){
    $oid = $_POST['order_id'];
    
    // Fetch Order Data
    $sql = "SELECT o.*, c.name as cust_name, c.phone as cust_phone, r.name as rest_name 
            FROM orders o 
            JOIN customers c ON o.customer_id = c.id 
            JOIN restaurants r ON o.restaurant_id = r.id 
            WHERE o.id = '$oid'";
            
    $res = $conn->query($sql);
    
    if($res && $res->num_rows > 0) {
        $order = $res->fetch_assoc();
        $date_time = isset($order['order_date']) ? $order['order_date'] : date('Y-m-d H:i:s');
        
        // Fetch Items
        $items = $conn->query("SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = '$oid'");
        
        $order_items = [];
        
        // --- QR CODE: FULL DETAILS GENERATION ---
        $qr_text = "--- FOODIESHUB RECEIPT ---\n";
        $qr_text .= "Order ID: #" . $order['id'] . "\n";
        $qr_text .= "Date: " . date("d M Y, h:i A", strtotime($date_time)) . "\n";
        $qr_text .= "Customer: " . $order['cust_name'] . " (" . $order['cust_phone'] . ")\n";
        $qr_text .= "Restaurant: " . $order['rest_name'] . "\n";
        $qr_text .= "------------------------\n";
        $qr_text .= "ITEMS ORDERED:\n";
        
        while($row = $items->fetch_assoc()){ 
            $order_items[] = $row; 
            // Add item to QR text
            $qr_text .= "- " . $row['quantity'] . "x " . $row['name'] . " (Rs." . ($row['price']*$row['quantity']) . ")\n";
        }
        
        $qr_text .= "------------------------\n";
        $qr_text .= "TOTAL AMOUNT: Rs. " . number_format($order['total_amount']) . "\n";
        $qr_text .= "Payment: Cash on Delivery\n";
        $qr_text .= "------------------------\n";
        $qr_text .= "Thank you for ordering!";

        // Generate QR URL with Full Data
        $qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($qr_text);
?>

<style>
    /* RECEIPT STYLES */
    .receipt-card { 
        background: white; 
        border-radius: 12px; 
        overflow: hidden; 
        font-family: 'Poppins', sans-serif; 
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        border: 1px solid #eee;
    }
    .receipt-header { 
        background: #007bff; 
        color: white; 
        padding: 15px; 
        text-align: center; 
    }
    .dashed-line { 
        border-bottom: 1px dashed #e0e0e0;
        margin: 15px 0; 
    }
    .info-row { 
        display: flex; 
        margin-bottom: 5px; 
        font-size: 0.85rem; 
        align-items: center; 
    }
    .info-label { 
        color: #6c757d; 
        font-weight: 500;
        min-width: 90px; 
    }
    .info-value { 
        font-weight: 600; 
        color: #212529;
        text-align: left; 
        flex-grow: 1; 
        word-break: break-word; 
    }
    .item-list { 
        max-height: 200px; /* Thora height barha diya taake list clear ho */
        overflow-y: auto; 
        padding-right: 5px; 
    } 
    .item-row { 
        display: flex; 
        justify-content: space-between; 
        font-size: 0.85rem; 
        padding: 6px 0;
        border-bottom: 1px solid #f8f9fa; 
    }
</style>

<div class="receipt-card">
    
    <div class="receipt-header">
        <h6 class="fw-bold mb-0"><i class="bi bi-check-circle-fill me-1"></i> Order Details</h6>
        <small><?php echo date("d M Y • h:i A", strtotime($date_time)); ?></small>
    </div>

    <div class="p-4 bg-white"> 
        <div class="text-center mb-4">
            <h1 class="fw-bold text-dark m-0">Rs. <?php echo number_format($order['total_amount']); ?></h1> 
            <span class="badge bg-success text-white rounded-pill mt-1" style="font-size: 10px;">Payment: Cash on Delivery</span>
        </div>
        
        <div class="row g-2 mb-3">
            <div class="col-12">
                <div class="info-row"><span class="info-label">Order ID:</span><span class="info-value">#<?php echo $order['id']; ?></span></div>
                <div class="info-row"><span class="info-label">Customer:</span><span class="info-value"><?php echo $order['cust_name']; ?></span></div>
                <div class="info-row"><span class="info-label">Phone:</span><span class="info-value"><?php echo $order['cust_phone']; ?></span></div>
                <div class="info-row"><span class="info-label">Restaurant:</span><span class="info-value"><?php echo $order['rest_name']; ?></span></div>
            </div>
        </div>
        <div class="dashed-line"></div>

        <h6 class="fw-bold small text-muted mb-2">ORDER ITEMS</h6>
        <div class="item-list">
            <?php foreach($order_items as $row){ ?>
            <div class="item-row">
                <span class="text-dark"><?php echo $row['quantity']; ?>x - <?php echo $row['name']; ?></span>
                <span class="fw-bold text-dark">Rs. <?php echo number_format($row['price'] * $row['quantity']); ?></span>
            </div>
            <?php } ?>
        </div>

        <div class="dashed-line"></div>

        <div class="d-flex align-items-center justify-content-between mt-3">
            <div class="text-start">
                <p class="text-muted small mb-0" style="font-size: 11px; line-height:1.2;">Scan for<br>Full Receipt Details</p>
            </div>
            <img src="<?php echo $qr_url; ?>" width="100" height="100" class="border p-1 rounded shadow-sm">
        </div>
    </div>
</div>

<?php 
    } else {
        echo "<div class='text-center text-danger py-3 small'>Error loading order details.</div>";
    }
} 
?>