<?php
date_default_timezone_set('Asia/Karachi'); // PKT Fix
session_start();
include 'db.php';

if(!isset($_SESSION['rest_id'])){ header("Location: restaurent_login.php"); exit(); }

$rest_id = $_SESSION['rest_id']; 
$rest_name = $_SESSION['rest_name'];
$msg = "";

// --- ONLINE/OFFLINE TOGGLE ---
if(isset($_POST['toggle_online'])) {
    $new_status = (int)$_POST['online_val'];
    $conn->query("UPDATE restaurants SET is_online='$new_status' WHERE id='$rest_id'");
    header("Location: restaurent_order.php");
    exit();
}

// --- STATUS UPDATE ---
if(isset($_POST['update_status'])){
    $oid = (int)$_POST['order_id'];
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $sql = "UPDATE orders SET status='$status' WHERE id='$oid' AND restaurant_id='$rest_id'";
    if($conn->query($sql)){
        $msg = "<div class='alert alert-success alert-dismissible fade show shadow-sm border-0 rounded-3'><i class='bi bi-check-circle-fill me-2'></i>Status updated to: <strong>$status</strong> <button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
    } else {
        $msg = "<div class='alert alert-danger shadow-sm border-0'>Error: ".$conn->error."</div>";
    }
}

// Get restaurant online status
$rest_res = $conn->query("SELECT is_online FROM restaurants WHERE id='$rest_id'");
$rest_data = $rest_res ? $rest_res->fetch_assoc() : ['is_online' => 1];
$is_online = $rest_data['is_online'] ?? 1;

// Get new order count for notification
$new_count_res = $conn->query("SELECT COUNT(*) as cnt FROM orders WHERE restaurant_id='$rest_id' AND status='Pending'");
$pending_count = $new_count_res ? (int)$new_count_res->fetch_assoc()['cnt'] : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Orders — <?php echo $rest_name; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root { --primary-color: #ff6b6b; --dark-bg: #2d3436; --light-bg: #f4f6f8; }
        body { background: var(--light-bg); font-family: 'Poppins', sans-serif; color: #4a4a4a; }
        .navbar { background: #fff !important; box-shadow: 0 2px 15px rgba(0,0,0,0.06); padding: 1rem 0; }
        .navbar-brand { color: var(--dark-bg) !important; font-size: 1.4rem; }

        /* Online/Offline toggle */
        .online-toggle-wrap { display: flex; align-items: center; gap: 10px; background: #f8f9fa; border-radius: 50px; padding: 6px 16px; }
        .online-dot { width: 10px; height: 10px; border-radius: 50%; }
        .online-dot.on { background: #00b894; box-shadow: 0 0 0 3px rgba(0,184,148,0.25); }
        .online-dot.off { background: #d63031; box-shadow: 0 0 0 3px rgba(214,48,49,0.2); }
        .form-check-input.online-sw { width: 2.5em; height: 1.3em; cursor: pointer; }
        .form-check-input.online-sw:checked { background-color: #00b894; border-color: #00b894; }

        /* Notification bar */
        .notif-bar { background: linear-gradient(135deg, #ff6b6b, #ee5253); color: white; border-radius: 12px; padding: 14px 20px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; animation: pulse-bar 2s ease infinite; }
        @keyframes pulse-bar { 0%,100%{opacity:1} 50%{opacity:0.85} }
        .notif-bar.hidden { display: none; }

        /* Order Cards */
        .card-order { border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.04); background: #fff; margin-bottom: 20px; overflow: hidden; transition: all 0.3s; }
        .card-order:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.08); }
        .card-order.new-order { border-left: 4px solid #ff6b6b; animation: flash-new 1s ease 3; }
        @keyframes flash-new { 0%,100%{border-left-color:#ff6b6b} 50%{border-left-color:#ffd32a} }

        /* Status headers */
        .status-header { padding: 14px 20px; border-bottom: 1px solid rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; }
        .status-Pending { background: #fff3cd; color: #856404; }
        .status-Accepted { background: #cce5ff; color: #004085; }
        .status-Preparing { background: #fff0e1; color: #d35400; border-left: 4px solid #ff9f43; }
        .status-Ready { background: #d1f2eb; color: #0e6655; }
        .status-Delivered { background: #d4edda; color: #155724; }
        .status-Cancelled { background: #f8d7da; color: #721c24; }

        /* Status pills */
        .status-pill { display: inline-flex; align-items: center; gap: 6px; padding: 5px 14px; border-radius: 50px; font-size: 0.8rem; font-weight: 700; }
        .pill-Pending { background: #ffeaa7; color: #856404; }
        .pill-Accepted { background: #74b9ff; color: #004085; }
        .pill-Preparing { background: #fdcb6e; color: #6d4c41; }
        .pill-Ready { background: #55efc4; color: #00695c; }
        .pill-Delivered { background: #00b894; color: white; }
        .pill-Cancelled { background: #ff7675; color: white; }

        .info-label { font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.8px; color: #999; font-weight: 700; margin-bottom: 8px; }
        .customer-icon { width: 36px; height: 36px; background: #f0f2f5; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--primary-color); }
        .total-box { background: #f8f9fa; border: 1.5px dashed #ddd; border-radius: 10px; padding: 12px 16px; }
        .form-select { border-radius: 10px; padding: 10px; border: 1.5px solid #eee; cursor: pointer; background: #f9f9f9; }
        .btn-update { background: var(--dark-bg); color: white; border-radius: 10px; padding: 10px 16px; border: none; width: 100%; font-weight: 600; transition: 0.2s; }
        .btn-update:hover { background: #000; color: white; transform: translateY(-1px); }
        .address-box { background: #eaf4ff; border-radius: 8px; padding: 10px 14px; font-size: 0.85rem; color: #1565c0; margin-top: 10px; }
        .address-box i { color: #1976d2; }
        .empty-state { text-align: center; padding: 60px 20px; opacity: 0.6; }

        /* Auto-refresh indicator */
        .refresh-indicator { display: inline-flex; align-items: center; gap: 6px; background: #e8f5e9; color: #2e7d32; border-radius: 50px; padding: 4px 12px; font-size: 0.75rem; font-weight: 600; }
        .refresh-dot { width: 8px; height: 8px; border-radius: 50%; background: #2e7d32; animation: blink 1.5s infinite; }
        @keyframes blink { 0%,100%{opacity:1} 50%{opacity:0.2} }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg sticky-top">
  <div class="container">
    <a class="navbar-brand fw-bold" href="#">
        <i class="bi bi-shop text-primary me-1"></i> <?php echo $rest_name; ?>
    </a>
    <div class="d-flex align-items-center gap-3 flex-wrap">

        <!-- Online/Offline Toggle -->
        <form method="POST" class="mb-0">
            <div class="online-toggle-wrap">
                <div class="online-dot <?= $is_online ? 'on' : 'off' ?>"></div>
                <span class="small fw-bold"><?= $is_online ? 'Online' : 'Offline' ?></span>
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input online-sw" type="checkbox" 
                           id="onlineSwitch" name="online_val" value="<?= $is_online ? 0 : 1 ?>"
                           <?= $is_online ? 'checked' : '' ?>
                           onchange="this.form.submit()" title="Toggle Online/Offline">
                    <input type="hidden" name="toggle_online" value="1">
                </div>
            </div>
        </form>

        <!-- Auto-refresh indicator -->
        <div class="refresh-indicator">
            <div class="refresh-dot"></div>
            <span id="refreshCountdown">Auto-refresh: 30s</span>
        </div>

        <a href="restaurent_dashboard.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="bi bi-grid-fill me-1"></i> Menu
        </a>
        <a href="restaurent_logout.php" class="btn btn-danger btn-sm rounded-pill px-3">Logout</a>
    </div>
  </div>
</nav>

<div class="container py-4">
    
    <!-- New Order Alert Bar -->
    <div class="notif-bar <?= $pending_count == 0 ? 'hidden' : '' ?>" id="notifBar">
        <div class="d-flex align-items-center gap-3">
            <i class="bi bi-bell-fill fs-4"></i>
            <div>
                <div class="fw-bold">🔔 <?= $pending_count ?> New Order<?= $pending_count > 1 ? 's' : '' ?> Waiting!</div>
                <div class="small opacity-85">Accept quickly to keep customers happy.</div>
            </div>
        </div>
        <button class="btn btn-light btn-sm fw-bold" onclick="document.getElementById('notifBar').classList.add('hidden')">Dismiss</button>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h2 class="fw-bold mb-0">Incoming Orders</h2>
            <p class="text-muted small mb-0">Manage live orders · <?= date('d M Y, h:i A') ?></p>
        </div>
        <span id="pendingBadge" class="badge bg-danger fs-6 px-3 py-2 <?= $pending_count == 0 ? 'd-none' : '' ?>">
            <?= $pending_count ?> Pending
        </span>
    </div>

    <?php echo $msg; ?>

    <div id="ordersContainer">
    <?php
    $sql = "SELECT o.*, c.name as cust_name, c.phone as cust_phone 
            FROM orders o 
            JOIN customers c ON o.customer_id = c.id 
            WHERE o.restaurant_id = '$rest_id' 
            ORDER BY o.id DESC";
    $result = $conn->query($sql);

    if($result && $result->num_rows > 0){
        while($order = $result->fetch_assoc()){
            $oid = $order['id'];
            $status = $order['status'];
            $statusClean = str_replace(' ', '', $status);
            $time_str = $order['order_date'] ?? $order['created_at'] ?? null;
            $time_display = $time_str ? date("d M, h:i A", strtotime($time_str)) : '—';
            $is_new = ($status === 'Pending');
            
            // Parse NASTP address
            $addr = $order['address'] ?? '';
    ?>
    <div class="card-order <?= $is_new ? 'new-order' : '' ?>">
        <div class="status-header status-<?= $statusClean ?>">
            <div>
                <span class="fw-bold fs-5">#ORDER-<?= $oid ?></span>
                <span class="ms-3 small opacity-75"><i class="bi bi-clock me-1"></i><?= $time_display ?></span>
            </div>
            <span class="status-pill pill-<?= $statusClean ?>">
                <?php
                $icons = ['Pending'=>'⏳','Accepted'=>'✅','Preparing'=>'🔥','Ready'=>'📦','Delivered'=>'✅','Cancelled'=>'❌'];
                echo ($icons[$status] ?? '') . ' ' . $status;
                ?>
            </span>
        </div>

        <div class="card-body p-4">
            <div class="row g-4">
                
                <!-- Customer + Address -->
                <div class="col-lg-4">
                    <div class="info-label">Customer Details</div>
                    <div class="d-flex align-items-center mb-3">
                        <div class="customer-icon me-3"><i class="bi bi-person-fill fs-5"></i></div>
                        <div>
                            <div class="fw-bold"><?= htmlspecialchars($order['cust_name']) ?></div>
                            <a href="tel:<?= $order['cust_phone'] ?>" class="text-muted small text-decoration-none">
                                <i class="bi bi-telephone me-1"></i><?= $order['cust_phone'] ?>
                            </a>
                        </div>
                    </div>

                    <!-- NASTP Address Display -->
                    <div class="address-box">
                        <i class="bi bi-building me-2"></i>
                        <strong>Delivery Location:</strong><br>
                        <?= htmlspecialchars($addr) ?>
                    </div>

                    <?php if(!empty($order['instructions'])): ?>
                    <div class="alert alert-warning small mt-3 mb-0 py-2 border-0 rounded-3">
                        <i class="bi bi-exclamation-circle-fill me-1"></i>
                        <strong>Rider Note:</strong> <?= htmlspecialchars($order['instructions']) ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Order Items -->
                <div class="col-lg-5">
                    <div class="info-label">Order Items</div>
                    <ul class="list-group list-group-flush mb-3">
                        <?php
                        $items = $conn->query("SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = '$oid'");
                        if($items){ while($item = $items->fetch_assoc()): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0" style="border-color:#f1f1f1; padding:10px 0;">
                            <div>
                                <span class="badge bg-light text-dark border me-2"><?= $item['quantity'] ?>x</span>
                                <span class="fw-medium"><?= htmlspecialchars($item['name']) ?></span>
                            </div>
                            <span class="text-muted small fw-bold">Rs. <?= number_format($item['price'] * $item['quantity']) ?></span>
                        </li>
                        <?php endwhile; } ?>
                    </ul>
                    <div class="total-box d-flex justify-content-between align-items-center">
                        <span class="text-muted small fw-bold">TOTAL</span>
                        <span class="fs-5 fw-bold text-success">Rs. <?= number_format($order['total_amount']) ?></span>
                    </div>
                </div>

                <!-- Status Update -->
                <div class="col-lg-3 d-flex flex-column justify-content-center">
                    <div class="bg-light p-3 rounded-3">
                        <div class="info-label mb-2">Update Status</div>
                        <form method="POST">
                            <input type="hidden" name="order_id" value="<?= $oid ?>">
                            <div class="mb-3">
                                <select name="status" class="form-select fw-bold">
                                    <option value="Pending"    <?= $status=='Pending'   ?'selected':'' ?>>⏳ Pending</option>
                                    <option value="Accepted"   <?= $status=='Accepted'  ?'selected':'' ?>>✅ Accepted</option>
                                    <option value="Preparing"  <?= $status=='Preparing' ?'selected':'' ?>>🔥 Preparing</option>
                                    <option value="Ready"      <?= $status=='Ready'     ?'selected':'' ?>>📦 Ready for Pickup</option>
                                    <option value="Delivered"  <?= $status=='Delivered' ?'selected':'' ?>>🚀 Delivered</option>
                                    <option value="Cancelled"  <?= $status=='Cancelled' ?'selected':'' ?>>❌ Cancelled</option>
                                </select>
                            </div>
                            <button type="submit" name="update_status" class="btn-update">
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
    } else { ?>
        <div class="empty-state">
            <i class="bi bi-clipboard-x display-1 text-muted mb-3 d-block"></i>
            <h3 class="fw-bold">No Orders Yet</h3>
            <p class="text-muted">Orders will appear here automatically when placed.</p>
        </div>
    <?php } ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // ===== AUTO-REFRESH WITH SOUND =====
    let lastPendingCount = <?= $pending_count ?>;
    let countdown = 30;
    
    // Generate notification sound using Web Audio API
    function playNotificationSound() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            [880, 1100, 880].forEach((freq, i) => {
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.frequency.value = freq;
                osc.type = 'sine';
                gain.gain.setValueAtTime(0.3, ctx.currentTime + i * 0.15);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + i * 0.15 + 0.2);
                osc.start(ctx.currentTime + i * 0.15);
                osc.stop(ctx.currentTime + i * 0.15 + 0.25);
            });
        } catch(e) { console.log('Audio not supported'); }
    }

    // Update countdown display
    function updateCountdown() {
        const el = document.getElementById('refreshCountdown');
        if(el) el.textContent = 'Auto-refresh: ' + countdown + 's';
    }

    // Check for new orders via AJAX
    function checkNewOrders() {
        fetch('check_status.php?rest_id=<?= $rest_id ?>&check_new=1')
            .then(r => r.json())
            .then(data => {
                if(data.pending_count > lastPendingCount) {
                    // New order arrived!
                    playNotificationSound();
                    const bar = document.getElementById('notifBar');
                    if(bar) { bar.classList.remove('hidden'); }
                    const badge = document.getElementById('pendingBadge');
                    if(badge) { badge.textContent = data.pending_count + ' Pending'; badge.classList.remove('d-none'); }
                    
                    // Show browser notification if allowed
                    if(Notification.permission === 'granted') {
                        new Notification('🍔 New Order!', { body: 'Order #' + data.latest_order_id + ' received!', icon: '/foodies_hub/assets/foodies_hub_logo.jpeg' });
                    }
                }
                lastPendingCount = data.pending_count;
            })
            .catch(() => {});
    }

    // Ask for notification permission
    if(Notification.permission === 'default') {
        Notification.requestPermission();
    }

    // Countdown timer + auto-refresh
    setInterval(() => {
        countdown--;
        updateCountdown();
        if(countdown <= 0) {
            location.reload();
        }
    }, 1000);

    // Check for new orders every 15 seconds
    setInterval(checkNewOrders, 15000);
</script>
</body>
</html>