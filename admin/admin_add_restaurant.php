<?php
session_start();
include '../db.php';

// Security Check
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

$msg = "";

// --- 1. LOGIC: DELETE RESTAURANT ---
if (isset($_GET['delete_id'])) {
    $del_id = (int)$_GET['delete_id'];
    
    // Pehle us restaurant ki sari products delete karein
    $conn->query("DELETE FROM products WHERE restaurant_id='$del_id'");
    
    // Phir restaurant delete karein
    if ($conn->query("DELETE FROM restaurants WHERE id='$del_id'")) {
        header("Location: admin_add_restaurant.php?msg=deleted");
        exit();
    } else {
        $msg = "<div class='alert alert-danger shadow-sm border-0'>Error deleting: " . $conn->error . "</div>";
    }
}

// Success Message for Delete (After Redirect)
if (isset($_GET['msg']) && $_GET['msg'] == 'deleted') {
    $msg = "<div class='alert alert-success shadow-sm border-0'>Restaurant deleted successfully!</div>";
}

// --- 2. LOGIC: ADD RESTAURANT ---
if (isset($_POST['add_restaurant'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']); 
    
    $target_dir = "../assets/uploads/";
    if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
    $image_name = time() . "_" . basename($_FILES["image"]["name"]);
    
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_dir . $image_name)) {
        $check = $conn->query("SELECT id FROM restaurants WHERE username='$username'");
        if($check->num_rows > 0) {
            $msg = "<div class='alert alert-danger shadow-sm border-0'>Username already exists!</div>";
        } else {
            $sql = "INSERT INTO restaurants (name, phone, address, image, username, password) 
                    VALUES ('$name', '$phone', '$address', '$image_name', '$username', '$password')";
            if ($conn->query($sql)) {
                $msg = "<div class='alert alert-success shadow-sm border-0'>Restaurant Added Successfully!</div>";
            } else {
                $msg = "<div class='alert alert-danger shadow-sm border-0'>Error: " . $conn->error . "</div>";
            }
        }
    } else {
        $msg = "<div class='alert alert-danger shadow-sm border-0'>Failed to upload image.</div>";
    }
}

// --- 3. LOGIC: UPDATE RESTAURANT ---
if (isset($_POST['update_restaurant'])) {
    $id = (int)$_POST['id'];
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    
    $pass_query = "";
    if(!empty($_POST['password'])){
        $password = mysqli_real_escape_string($conn, $_POST['password']);
        $pass_query = ", password='$password'";
    }

    $img_query = "";
    if(!empty($_FILES['image']['name'])){
        $target_dir = "../assets/uploads/";
        $image_name = time() . "_" . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], $target_dir . $image_name);
        $img_query = ", image='$image_name'";
    }

    $sql = "UPDATE restaurants SET name='$name', phone='$phone', address='$address', username='$username' $pass_query $img_query WHERE id='$id'";
    
    if ($conn->query($sql)) {
        $msg = "<div class='alert alert-success shadow-sm border-0'>Restaurant Details Updated!</div>";
    } else {
        $msg = "<div class='alert alert-danger shadow-sm border-0'>Update Failed: " . $conn->error . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Partners | Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">

    <style>
        :root { --primary: #D70F64; --bg-light: #f3f4f7; --card-shadow: 0 10px 30px rgba(0,0,0,0.04); }
        body { font-family: 'DM Sans', sans-serif; background-color: var(--bg-light); color: #4a5568; }
        
        .sidebar { height: 100vh; width: 260px; position: fixed; top: 0; left: 0; background: #1a1c23; color: white; padding-top: 30px; z-index: 1000; }
        .sidebar-brand { font-size: 1.4rem; font-weight: 700; text-align: center; margin-bottom: 40px; letter-spacing: 1px; }
        .nav-link { color: #a0aec0; padding: 15px 30px; font-weight: 500; display: flex; align-items: center; border-left: 4px solid transparent; transition: 0.3s; }
        .nav-link:hover, .nav-link.active { background-color: rgba(255, 255, 255, 0.05); color: white; border-left-color: var(--primary); }
        .nav-link i { margin-right: 15px; font-size: 1.1rem; }

        .main-content { margin-left: 260px; padding: 40px; }
        .custom-card { background: white; border-radius: 16px; padding: 30px; box-shadow: var(--card-shadow); height: 100%; border: none; }
        .form-label { font-weight: 700; font-size: 0.85rem; color: #64748b; margin-bottom: 8px; }
        .form-control { border-radius: 10px; padding: 12px; border: 1px solid #e2e8f0; background-color: #f8fafc; font-size: 0.95rem; }
        .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(215, 15, 100, 0.1); background-color: white; }
        
        .btn-primary { background-color: var(--primary); border: none; border-radius: 10px; padding: 12px; font-weight: 700; width: 100%; transition: 0.3s; }
        .btn-primary:hover { background-color: #b00c50; transform: translateY(-2px); }

        .table-hover tbody tr:hover { background-color: #f8fafc; }
        .img-thumb { width: 45px; height: 45px; object-fit: cover; border-radius: 10px; border: 1px solid #e2e8f0; }
        
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
            <a href="admin_add_restaurant.php" class="nav-link active"><i class="bi bi-shop"></i> <span>Add Partner</span></a>
            <a href="admin_orders.php" class="nav-link"><i class="bi bi-receipt"></i> <span>Orders</span></a>
            <a href="logout.php" class="nav-link mt-auto mb-4 text-danger"><i class="bi bi-box-arrow-right"></i> <span>Logout</span></a>
        </nav>
    </div>

    <div class="main-content">
        
        <div class="d-flex align-items-center mb-5">
            <a href="admin_dashboard.php" class="btn btn-light rounded-circle shadow-sm me-3"><i class="bi bi-arrow-left"></i></a>
            <div>
                <h3 class="fw-bold text-dark m-0">Manage Partners</h3>
                <p class="text-muted small m-0">Register new or edit existing restaurants</p>
            </div>
        </div>

        <div class="row g-4">
            
            <div class="col-lg-5">
                <div class="custom-card">
                    <h5 class="fw-bold mb-4 text-dark"><i class="bi bi-plus-circle-fill me-2 text-secondary"></i>Register New</h5>
                    <?php echo $msg; ?>

                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Restaurant Name</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. KFC, Savor Foods" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone" class="form-control" placeholder="0300-1234567" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="2" placeholder="Complete location address..." required></textarea>
                        </div>
                        <div class="p-3 rounded bg-light border border-dashed mb-4">
                            <h6 class="fw-bold mb-3 text-uppercase small text-muted spacing-1">Login Credentials</h6>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label small">Username</label>
                                    <input type="text" name="username" class="form-control" placeholder="unique_id" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">Password</label>
                                    <input type="text" name="password" class="form-control" placeholder="Key" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Upload Logo</label>
                            <input type="file" name="image" class="form-control" required>
                        </div>
                        <button type="submit" name="add_restaurant" class="btn btn-primary shadow-sm">
                            <i class="bi bi-check-circle-fill me-2"></i> Register Account
                        </button>
                    </form>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="custom-card p-0 overflow-hidden">
                    <div class="p-4 border-bottom bg-light">
                        <h5 class="fw-bold m-0 text-dark">📋 Registered Partners</h5>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-white">
                                <tr class="text-uppercase small text-muted">
                                    <th class="ps-4 py-3">Profile</th>
                                    <th class="py-3">Info</th>
                                    <th class="py-3 text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT * FROM restaurants ORDER BY id DESC LIMIT 10";
                                $result = $conn->query($sql);
                                
                                if ($result && $result->num_rows > 0) {
                                    while($row = $result->fetch_assoc()) {
                                        $img = "../assets/uploads/" . $row['image'];
                                        $editModalID = "editResModal" . $row['id'];
                                ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <img src="<?php echo $img; ?>" class="img-thumb me-3" onerror="this.src='https://placehold.co/50'">
                                            <span class="fw-bold text-dark"><?php echo $row['name']; ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small text-muted"><i class="bi bi-telephone me-1"></i> <?php echo $row['phone']; ?></div>
                                        <span class="badge bg-light text-dark border px-2 py-1 mt-1">@<?php echo $row['username']; ?></span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-light border text-primary shadow-sm me-1" data-bs-toggle="modal" data-bs-target="#<?php echo $editModalID; ?>">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        
                                        <a href="admin_add_restaurant.php?delete_id=<?php echo $row['id']; ?>" 
                                           class="btn btn-sm btn-light border text-danger shadow-sm"
                                           onclick="return confirm('Are you sure? This will delete the restaurant and ALL its food items permanently.');">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>

                                <div class="modal fade" id="<?php echo $editModalID; ?>" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow-lg rounded-4">
                                            <div class="modal-header border-bottom-0 pb-0">
                                                <h5 class="modal-title fw-bold">Edit <?php echo $row['name']; ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body p-4">
                                                <form method="POST" enctype="multipart/form-data">
                                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label small">Name</label>
                                                        <input type="text" name="name" class="form-control" value="<?php echo $row['name']; ?>" required>
                                                    </div>
                                                    <div class="row g-2 mb-3">
                                                        <div class="col-6">
                                                            <label class="form-label small">Phone</label>
                                                            <input type="text" name="phone" class="form-control" value="<?php echo $row['phone']; ?>" required>
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="form-label small">Username</label>
                                                            <input type="text" name="username" class="form-control" value="<?php echo $row['username']; ?>" required>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small">Address</label>
                                                        <input type="text" name="address" class="form-control" value="<?php echo $row['address']; ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small text-danger">New Password (Optional)</label>
                                                        <input type="text" name="password" class="form-control border-danger" placeholder="Leave empty to keep old password">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small">Change Logo (Optional)</label>
                                                        <input type="file" name="image" class="form-control">
                                                    </div>
                                                    <div class="d-grid">
                                                        <button type="submit" name="update_restaurant" class="btn btn-dark fw-bold">Save Changes</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php 
                                    }
                                } else {
                                    echo "<tr><td colspan='3' class='text-center py-5 text-muted'>No restaurants found.</td></tr>";
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
</body>
</html>