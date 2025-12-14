<?php
session_start();
include '../db.php';

// Security Check
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

$msg = "";

// --- 1. UPDATE RESTAURANT LOGIC ---
if (isset($_POST['update_res'])) {
    $id = (int)$_POST['res_id'];
    $name = mysqli_real_escape_string($conn, $_POST['res_name']);
    $username = mysqli_real_escape_string($conn, $_POST['res_username']); 
    
    $sql = "UPDATE restaurants SET name='$name', username='$username' WHERE id='$id'";
    
    if($conn->query($sql)){
        header("Location: admin_dashboard.php?msg=res_updated");
        exit();
    } else {
        $msg = "Error updating record: " . $conn->error;
    }
}

// --- 2. DELETE RESTAURANT LOGIC ---
if (isset($_GET['delete_res'])) {
    $rid = (int)$_GET['delete_res'];
    $conn->query("DELETE FROM products WHERE restaurant_id='$rid'");
    $conn->query("DELETE FROM restaurants WHERE id='$rid'");
    header("Location: admin_dashboard.php?msg=res_deleted");
    exit();
}

// --- 3. STATS (Safe Counts) ---
$total_restaurants = 0; $total_orders = 0; $total_customers = 0;

$q1 = $conn->query("SELECT count(*) as total FROM restaurants");
if($q1) $total_restaurants = $q1->fetch_assoc()['total'];

$q2 = $conn->query("SELECT count(*) as total FROM orders");
if($q2) $total_orders = $q2->fetch_assoc()['total'];

$q3 = $conn->query("SELECT count(*) as total FROM customers");
if($q3) $total_customers = $q3->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Dashboard | Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #D70F64;
            --secondary: #2c3e50;
            --bg-light: #f3f4f7;
            --card-shadow: 0 10px 30px rgba(0,0,0,0.04);
        }

        body { font-family: 'DM Sans', sans-serif; background-color: var(--bg-light); overflow-x: hidden; }

        /* Sidebar Styling */
        .sidebar { height: 100vh; width: 260px; position: fixed; top: 0; left: 0; background: #1a1c23; color: white; padding-top: 30px; z-index: 1000; transition: 0.3s; }
        .sidebar-brand { font-size: 1.4rem; font-weight: 700; text-align: center; margin-bottom: 40px; color: white; letter-spacing: 1px; }
        .nav-link { color: #a0aec0; padding: 15px 30px; font-size: 0.95rem; font-weight: 500; display: flex; align-items: center; transition: 0.3s; border-left: 4px solid transparent; }
        .nav-link:hover, .nav-link.active { background-color: rgba(255, 255, 255, 0.05); color: white; border-left-color: var(--primary); }
        .nav-link i { margin-right: 15px; font-size: 1.1rem; }

        /* Main Content */
        .main-content { margin-left: 260px; padding: 40px; }

        /* Stats Cards - COMPACT VERSION */
        .stats-card { 
            background: white; 
            border-radius: 12px; 
            padding: 15px 20px; 
            box-shadow: var(--card-shadow); 
            border: none; 
            border-left: 5px solid transparent; 
            transition: transform 0.3s; 
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .stats-card:hover { transform: translateY(-3px); }
        .card-orange { border-left-color: #ea580c; }
        .card-blue { border-left-color: #2563eb; }
        .card-green { border-left-color: #16a34a; }

        .icon-box { width: 45px; height: 45px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; }
        .bg-orange-soft { background: #fff7ed; color: #ea580c; }
        .bg-blue-soft { background: #eff6ff; color: #2563eb; }
        .bg-green-soft { background: #f0fdf4; color: #16a34a; }
        .card-value { font-size: 1.8rem; font-weight: 700; color: #1a1c23; line-height: 1; margin-bottom: 2px; }
        .card-label { color: #64748b; font-size: 0.85rem; font-weight: 500; }

        /* Tables */
        .table-card { background: white; border-radius: 16px; padding: 0; box-shadow: var(--card-shadow); overflow: hidden; margin-bottom: 30px; }
        .card-header-custom { padding: 25px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; }
        .table thead th { background-color: #f8fafc; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; padding: 15px 25px; color: #64748b; font-weight: 700; border: none; }
        .table tbody td { padding: 15px 25px; vertical-align: middle; color: #334155; border-bottom: 1px solid #f1f5f9; }
        .user-avatar { width: 35px; height: 35px; background: #f1f5f9; color: #64748b; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-right: 10px; font-size: 0.8rem; font-weight: bold; }

        /* Search Box */
        .search-input {
            border-radius: 20px;
            padding: 8px 15px 8px 40px;
            border: 1px solid #e2e8f0;
            background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23a0aec0' class='bi bi-search' viewBox='0 0 16 16'%3E%3Cpath d='M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z'%3E%3C/path%3E%3C/svg%3E") no-repeat 13px center;
            outline: none;
            width: 250px;
            transition: all 0.2s;
        }
        .search-input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(215, 15, 100, 0.1); width: 300px; }

        @media (max-width: 768px) {
            .sidebar { width: 70px; padding-top: 20px; }
            .sidebar-brand span, .nav-link span { display: none; }
            .sidebar-brand { font-size: 1rem; margin-bottom: 20px; }
            .nav-link { padding: 15px; justify-content: center; } .nav-link i { margin: 0; }
            .main-content { margin-left: 70px; padding: 20px; }
            .search-input:focus { width: 100%; }
        }
    </style>
</head>

<body>

    <div class="sidebar shadow">
        <div class="sidebar-brand"><i class="bi bi-shield-lock"></i> <span>ADMIN</span></div>
        <nav class="nav flex-column">
            <a href="#" class="nav-link active"><i class="bi bi-grid"></i> <span>Dashboard</span></a>
            <a href="admin_add_restaurant.php" class="nav-link"><i class="bi bi-shop"></i> <span>Add Partner</span></a>
            <a href="admin_orders.php" class="nav-link"><i class="bi bi-receipt"></i> <span>Orders</span></a>
            <a href="logout.php" class="nav-link mt-auto mb-4 text-danger"><i class="bi bi-box-arrow-right"></i> <span>Logout</span></a>
        </nav>
    </div>

    <div class="main-content">

        <div class="d-flex justify-content-between align-items-center mb-5 flex-wrap gap-3">
            <div>
                <h3 class="fw-bold text-dark m-0">Dashboard</h3>
                <p class="text-muted small m-0">Overview & Management</p>
            </div>
            
            <div class="d-flex align-items-center gap-3">
                <input type="text" id="globalSearch" class="search-input" placeholder="Search anything...">
                <a href="admin_add_restaurant.php" class="btn btn-dark rounded-pill px-4 shadow-sm">
                    <i class="bi bi-plus-lg me-1"></i> New
                </a>
            </div>
        </div>

        <div class="row g-3 mb-5"> 
            <div class="col-md-4">
                <div class="stats-card card-orange">
                    <div>
                        <div class="card-value"><?php echo $total_restaurants; ?></div>
                        <div class="card-label">Active Partners</div>
                    </div>
                    <div class="icon-box bg-orange-soft"><i class="bi bi-shop"></i></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card card-blue">
                    <div>
                        <div class="card-value"><?php echo $total_customers; ?></div>
                        <div class="card-label">Registered Users</div>
                    </div>
                    <div class="icon-box bg-blue-soft"><i class="bi bi-people"></i></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card card-green">
                    <div>
                        <div class="card-value"><?php echo $total_orders; ?></div>
                        <div class="card-label">Total Orders</div>
                    </div>
                    <div class="icon-box bg-green-soft"><i class="bi bi-receipt"></i></div>
                </div>
            </div>
        </div>

        <?php if (isset($_GET['msg'])) { ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 rounded-3 mb-4">
                <?php 
                    if($_GET['msg'] == 'res_deleted') echo "Restaurant deleted successfully.";
                    if($_GET['msg'] == 'res_updated') echo "Restaurant details updated successfully.";
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php } ?>

        <div class="row">
            
            <div class="col-12">
                <div class="table-card">
                    <div class="card-header-custom">
                        <h5 class="fw-bold m-0 text-dark">Partner Restaurants</h5>
                        <span class="badge bg-light text-dark border">Manage</span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="resTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Username</th>
                                    <th>Rating</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $res_q = $conn->query("SELECT * FROM restaurants ORDER BY id DESC");
                                if ($res_q && $res_q->num_rows > 0) {
                                    while ($row = $res_q->fetch_assoc()) {
                                        $editModalID = "editModal" . $row['id'];
                                        $res_username = isset($row['username']) ? $row['username'] : 'N/A';
                                        $res_rating = isset($row['rating']) ? $row['rating'] : '0.0';
                                ?>
                                        <tr class="search-item">
                                            <td>#<?php echo $row['id']; ?></td>
                                            <td class="fw-bold text-dark search-text"><?php echo $row['name']; ?></td>
                                            <td><?php echo $res_username; ?></td>
                                            <td><span class="badge bg-warning text-dark rounded-pill"><i class="bi bi-star-fill"></i> <?php echo $res_rating; ?></span></td>
                                            <td class="text-end">
                                                <button class="btn btn-sm btn-light text-primary fw-bold border me-1" data-bs-toggle="modal" data-bs-target="#<?php echo $editModalID; ?>">
                                                    <i class="bi bi-pencil-square"></i> Edit
                                                </button>
                                                
                                                <a href="admin_dashboard.php?delete_res=<?php echo $row['id']; ?>" 
                                                   class="btn btn-sm btn-light text-danger fw-bold border"
                                                   onclick="return confirm('Delete this restaurant and all its items?');">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </td>
                                        </tr>

                                        <div class="modal fade" id="<?php echo $editModalID; ?>" tabindex="-1">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content border-0 shadow-lg rounded-4">
                                                    <div class="modal-header border-0">
                                                        <h5 class="modal-title fw-bold">Edit Restaurant</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form method="POST">
                                                            <input type="hidden" name="res_id" value="<?php echo $row['id']; ?>">
                                                            <div class="mb-3">
                                                                <label class="small fw-bold text-muted mb-1">Restaurant Name</label>
                                                                <input type="text" name="res_name" class="form-control" value="<?php echo $row['name']; ?>" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="small fw-bold text-muted mb-1">Username (Login ID)</label>
                                                                <input type="text" name="res_username" class="form-control" value="<?php echo $res_username; ?>" required>
                                                            </div>
                                                            <div class="d-grid">
                                                                <button type="submit" name="update_res" class="btn btn-primary fw-bold">Update Details</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                <?php
                                    }
                                } else {
                                    echo "<tr><td colspan='5' class='text-center py-5 text-muted'>No restaurants found.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="table-card">
                    <div class="card-header-custom">
                        <h5 class="fw-bold m-0 text-dark">Registered Customers</h5>
                        <span class="badge bg-success bg-opacity-10 text-success border-success border border-opacity-25">Verified List</span>
                    </div>

                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-hover mb-0" id="custTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Customer Name</th>
                                    <th>Phone Number</th>
                                    <th>Joined Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $cust_q = $conn->query("SELECT * FROM customers ORDER BY id DESC");
                                if ($cust_q && $cust_q->num_rows > 0) {
                                    while ($c = $cust_q->fetch_assoc()) {
                                        $display_name = $c['name'];
                                        $initial = strtoupper(substr($display_name, 0, 1));
                                        $join_date = isset($c['created_at']) ? date("M d, Y", strtotime($c['created_at'])) : 'N/A';
                                ?>
                                        <tr class="search-item">
                                            <td><span class="text-muted small">#<?php echo $c['id']; ?></span></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="user-avatar"><?php echo $initial; ?></div>
                                                    <span class="fw-bold text-dark search-text"><?php echo $display_name; ?></span>
                                                </div>
                                            </td>
                                            <td class="search-text"><?php echo $c['phone']; ?></td>
                                            <td class="text-muted small"><?php echo $join_date; ?></td>
                                        </tr>
                                <?php
                                    }
                                } else {
                                    echo "<tr><td colspan='4' class='text-center py-5 text-muted'>No customers found.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Real-Time Search Logic
        document.getElementById('globalSearch').addEventListener('keyup', function() {
            let filter = this.value.toLowerCase();
            let rows = document.querySelectorAll('.search-item');

            rows.forEach(function(row) {
                let text = row.innerText.toLowerCase();
                if (text.includes(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>