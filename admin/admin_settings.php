<?php
date_default_timezone_set('Asia/Karachi');
session_start();
include '../db.php';
if (!isset($_SESSION['admin_logged_in'])) { header("Location: admin_login.php"); exit(); }

$msg = '';
if(isset($_POST['save_settings'])) {
    $fee = (int)$_POST['delivery_fee'];
    $conn->query("UPDATE settings SET setting_value='$fee' WHERE setting_key='delivery_fee'");
    if($conn->affected_rows == 0) {
        $conn->query("INSERT INTO settings (setting_key, setting_value) VALUES ('delivery_fee','$fee')");
    }
    $msg = 'saved';
}

$fee_res = $conn->query("SELECT setting_value FROM settings WHERE setting_key='delivery_fee'");
$current_fee = ($fee_res && $fee_res->num_rows > 0) ? (int)$fee_res->fetch_assoc()['setting_value'] : 99;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Settings | Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #D70F64; }
        body { font-family: 'DM Sans', sans-serif; background: #f3f4f7; }
        .sidebar { height:100vh; width:260px; position:fixed; top:0; left:0; background:#1a1c23; color:white; padding-top:30px; z-index:1000; }
        .sidebar-brand { font-size:1.4rem; font-weight:700; text-align:center; margin-bottom:40px; color:white; }
        .nav-link { color:#a0aec0; padding:15px 30px; font-size:0.95rem; font-weight:500; display:flex; align-items:center; transition:0.3s; border-left:4px solid transparent; }
        .nav-link:hover, .nav-link.active { background:rgba(255,255,255,0.05); color:white; border-left-color:var(--primary); }
        .nav-link i { margin-right:15px; font-size:1.1rem; }
        .main-content { margin-left:260px; padding:40px; }
        .settings-card { background:white; border-radius:16px; padding:30px; box-shadow:0 4px 20px rgba(0,0,0,0.04); max-width:550px; }
        .form-control:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(215,15,100,0.1); }
        @media(max-width:768px){ .sidebar{width:70px;padding-top:20px;} .sidebar-brand span,.nav-link span{display:none;} .nav-link{padding:15px;justify-content:center;} .nav-link i{margin:0;} .main-content{margin-left:70px;padding:20px;} }
    </style>
</head>
<body>
<div class="sidebar shadow">
    <div class="sidebar-brand"><i class="bi bi-shield-lock"></i> <span>ADMIN</span></div>
    <nav class="nav flex-column">
        <a href="admin_dashboard.php" class="nav-link"><i class="bi bi-grid"></i> <span>Dashboard</span></a>
        <a href="admin_add_restaurant.php" class="nav-link"><i class="bi bi-shop"></i> <span>Add Partner</span></a>
        <a href="admin_orders.php" class="nav-link"><i class="bi bi-receipt"></i> <span>Orders</span></a>
        <a href="admin_revenue.php" class="nav-link"><i class="bi bi-graph-up-arrow"></i> <span>Revenue</span></a>
        <a href="admin_settings.php" class="nav-link active"><i class="bi bi-gear"></i> <span>Settings</span></a>
        <a href="logout.php" class="nav-link mt-auto mb-4 text-danger"><i class="bi bi-box-arrow-right"></i> <span>Logout</span></a>
    </nav>
</div>

<div class="main-content">
    <h3 class="fw-bold mb-2">⚙️ Platform Settings</h3>
    <p class="text-muted mb-5">Manage platform-wide configuration.</p>

    <?php if($msg === 'saved'): ?>
    <div class="alert alert-success mb-4"><i class="bi bi-check-circle-fill me-2"></i>Settings saved successfully!</div>
    <?php endif; ?>

    <div class="settings-card">
        <h5 class="fw-bold mb-1"><i class="bi bi-bicycle text-success me-2"></i>Delivery Fee</h5>
        <p class="text-muted small mb-4">This fee is added to every order and shown to customers on cart & checkout pages.</p>
        <form method="POST">
            <div class="mb-4">
                <label class="form-label fw-bold small">Delivery Fee (Rs.)</label>
                <div class="input-group" style="max-width:200px;">
                    <span class="input-group-text fw-bold">Rs.</span>
                    <input type="number" name="delivery_fee" class="form-control fw-bold" 
                           value="<?= $current_fee ?>" min="0" max="500" step="1" required>
                </div>
                <small class="text-muted mt-2 d-block">Currently: <strong>Rs. <?= $current_fee ?></strong> per order</small>
            </div>
            <button type="submit" name="save_settings" class="btn btn-dark rounded-pill px-4 fw-bold">
                <i class="bi bi-save me-2"></i>Save Settings
            </button>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
