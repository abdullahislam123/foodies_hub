<?php
date_default_timezone_set('Asia/Karachi');
session_start();
include '../db.php';
if (!isset($_SESSION['admin_logged_in'])) { header("Location: admin_login.php"); exit(); }

// Update commission %
if(isset($_POST['update_commission'])) {
    $rid = (int)$_POST['rest_id'];
    $comm = (float)$_POST['commission'];
    $conn->query("UPDATE restaurants SET commission_percent='$comm' WHERE id='$rid'");
    header("Location: admin_revenue.php?saved=1"); exit();
}

// Date ranges
$today = date('Y-m-d');
$week_start = date('Y-m-d', strtotime('monday this week'));
$month_start = date('Y-m-01');

// Summary stats
function getSales($conn, $from, $to = null) {
    $to = $to ?? date('Y-m-d');
    $r = $conn->query("SELECT COALESCE(SUM(total_amount),0) as total, COUNT(*) as cnt FROM orders WHERE status='Delivered' AND DATE(order_date) BETWEEN '$from' AND '$to'");
    return $r ? $r->fetch_assoc() : ['total'=>0,'cnt'=>0];
}

$today_stats   = getSales($conn, $today);
$week_stats    = getSales($conn, $week_start);
$month_stats   = getSales($conn, $month_start);

// Per-restaurant breakdown
$rests = $conn->query("SELECT r.id, r.name, r.commission_percent,
    COALESCE(SUM(CASE WHEN DATE(o.order_date)='$today' AND o.status='Delivered' THEN o.total_amount ELSE 0 END),0) as today_sales,
    COALESCE(SUM(CASE WHEN DATE(o.order_date) >= '$week_start' AND o.status='Delivered' THEN o.total_amount ELSE 0 END),0) as week_sales,
    COALESCE(SUM(CASE WHEN DATE(o.order_date) >= '$month_start' AND o.status='Delivered' THEN o.total_amount ELSE 0 END),0) as month_sales,
    COUNT(CASE WHEN o.status='Delivered' THEN 1 END) as total_orders
    FROM restaurants r
    LEFT JOIN orders o ON o.restaurant_id = r.id
    GROUP BY r.id, r.name, r.commission_percent
    ORDER BY month_sales DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Revenue Report | Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #D70F64; }
        body { font-family: 'DM Sans', sans-serif; background: #f3f4f7; overflow-x: hidden; }
        .sidebar { height: 100vh; width: 260px; position: fixed; top:0; left:0; background: #1a1c23; color:white; padding-top:30px; z-index:1000; }
        .sidebar-brand { font-size:1.4rem; font-weight:700; text-align:center; margin-bottom:40px; color:white; }
        .nav-link { color:#a0aec0; padding:15px 30px; font-size:0.95rem; font-weight:500; display:flex; align-items:center; transition:0.3s; border-left:4px solid transparent; }
        .nav-link:hover, .nav-link.active { background:rgba(255,255,255,0.05); color:white; border-left-color:var(--primary); }
        .nav-link i { margin-right:15px; font-size:1.1rem; }
        .main-content { margin-left:260px; padding:40px; }
        .stat-card { background:white; border-radius:14px; padding:22px 25px; box-shadow:0 4px 20px rgba(0,0,0,0.05); border-left:5px solid transparent; }
        .stat-card.pink { border-left-color:#D70F64; }
        .stat-card.blue { border-left-color:#2563eb; }
        .stat-card.green { border-left-color:#16a34a; }
        .stat-val { font-size:1.7rem; font-weight:800; color:#1a1c23; }
        .stat-label { color:#64748b; font-size:0.85rem; }
        .table-card { background:white; border-radius:16px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,0.04); }
        .table thead th { background:#f8fafc; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.5px; padding:14px 20px; color:#64748b; font-weight:700; border:none; }
        .table tbody td { padding:14px 20px; vertical-align:middle; border-bottom:1px solid #f1f5f9; }
        .comm-input { width:80px; border:1.5px solid #eee; border-radius:8px; padding:5px 10px; font-weight:700; text-align:center; }
        .comm-input:focus { border-color:var(--primary); outline:none; }
        .save-btn { background:var(--primary); color:white; border:none; border-radius:8px; padding:5px 14px; font-weight:700; font-size:0.85rem; }
        .payout-box { background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; padding:6px 12px; }
        .comm-box { background:#fff0f6; border:1px solid #fbcfe8; border-radius:8px; padding:6px 12px; }
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
        <a href="admin_revenue.php" class="nav-link active"><i class="bi bi-graph-up-arrow"></i> <span>Revenue</span></a>
        <a href="admin_settings.php" class="nav-link"><i class="bi bi-gear"></i> <span>Settings</span></a>
        <a href="logout.php" class="nav-link mt-auto mb-4 text-danger"><i class="bi bi-box-arrow-right"></i> <span>Logout</span></a>
    </nav>
</div>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-5 flex-wrap gap-3">
        <div>
            <h3 class="fw-bold mb-0">📊 Revenue & Commission</h3>
            <p class="text-muted small">Platform earnings from all cafe partners · <?= date('d M Y, h:i A') ?></p>
        </div>
        <?php if(isset($_GET['saved'])): ?>
        <div class="alert alert-success py-2 px-3 mb-0"><i class="bi bi-check-circle me-1"></i>Commission saved!</div>
        <?php endif; ?>
    </div>

    <!-- Summary Stats -->
    <div class="row g-4 mb-5">
        <?php
        $periods = [
            ['Today', $today_stats, 'pink', 'bi-sun'],
            ['This Week', $week_stats, 'blue', 'bi-calendar-week'],
            ['This Month', $month_stats, 'green', 'bi-calendar-month'],
        ];
        foreach($periods as [$label, $stats, $cls, $icon]):
            $comm = $conn->query("SELECT AVG(commission_percent) as avg FROM restaurants")->fetch_assoc()['avg'] ?? 10;
            $your_cut = ($stats['total'] * $comm) / 100;
        ?>
        <div class="col-md-4">
            <div class="stat-card <?= $cls ?>">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <div class="stat-label"><?= $label ?> Sales</div>
                        <div class="stat-val">Rs. <?= number_format($stats['total']) ?></div>
                        <small class="text-muted"><?= $stats['cnt'] ?> delivered orders</small>
                    </div>
                    <i class="bi <?= $icon ?> fs-2 text-muted opacity-50"></i>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <span class="badge bg-light text-success border fw-bold">Your Cut: Rs. <?= number_format($your_cut) ?></span>
                    <span class="badge bg-light text-secondary border">Cafe: Rs. <?= number_format($stats['total'] - $your_cut) ?></span>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Per-Cafe Breakdown -->
    <div class="table-card">
        <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold mb-0">Per-Cafe Breakdown</h5>
                <small class="text-muted">Set commission % per cafe — your cut from each order</small>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Cafe / Restaurant</th>
                        <th>Commission %</th>
                        <th>Today Sales</th>
                        <th>This Week</th>
                        <th>This Month</th>
                        <th>Your Commission (Month)</th>
                        <th>Cafe Payout (Month)</th>
                        <th>Total Orders</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($rests && $rests->num_rows > 0): while($r = $rests->fetch_assoc()):
                        $comm = (float)$r['commission_percent'];
                        $your_month = ($r['month_sales'] * $comm) / 100;
                        $cafe_month = $r['month_sales'] - $your_month;
                    ?>
                    <tr>
                        <td class="fw-bold"><?= htmlspecialchars($r['name']) ?></td>
                        <td>
                            <form method="POST" class="d-inline-flex align-items-center gap-2">
                                <input type="hidden" name="rest_id" value="<?= $r['id'] ?>">
                                <input type="number" name="commission" class="comm-input" value="<?= $comm ?>" min="0" max="100" step="0.5">
                                <span class="text-muted small">%</span>
                                <button type="submit" name="update_commission" class="save-btn">Save</button>
                            </form>
                        </td>
                        <td>Rs. <?= number_format($r['today_sales']) ?></td>
                        <td>Rs. <?= number_format($r['week_sales']) ?></td>
                        <td class="fw-bold">Rs. <?= number_format($r['month_sales']) ?></td>
                        <td>
                            <div class="comm-box fw-bold" style="color:#be185d;">Rs. <?= number_format($your_month) ?></div>
                        </td>
                        <td>
                            <div class="payout-box fw-bold" style="color:#15803d;">Rs. <?= number_format($cafe_month) ?></div>
                        </td>
                        <td><span class="badge bg-light text-dark border"><?= $r['total_orders'] ?></span></td>
                        <td></td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="9" class="text-center py-5 text-muted">No cafe partners yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
