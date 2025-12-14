<?php
session_start();
include 'db.php';

// Security Check
if (!isset($_SESSION['customer_phone'])) {
    header("Location: login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];
$msg = "";

// --- UPDATE PROFILE LOGIC ---
if (isset($_POST['update_profile'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    
    // Image Upload Logic
    $image_query = "";
    if (!empty($_FILES['profile_pic']['name'])) {
        $target_dir = "assets/uploads/users/";
        if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
        
        $image_name = time() . "_" . basename($_FILES["profile_pic"]["name"]);
        $target_file = $target_dir . $image_name;
        
        if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file)) {
            $image_query = ", image='$image_name'";
        }
    }

    $sql = "UPDATE customers SET name='$name' $image_query WHERE id='$customer_id'";
    if ($conn->query($sql)) {
        $_SESSION['customer_name'] = $name; // Session update
        $msg = "<div class='alert alert-success'>Profile Updated Successfully! ✅</div>";
    } else {
        $msg = "<div class='alert alert-danger'>Error updating profile.</div>";
    }
}

// --- FETCH CUSTOMER DATA ---
$user_sql = "SELECT * FROM customers WHERE id='$customer_id'";
$user_res = $conn->query($user_sql);
$user = $user_res->fetch_assoc();

// --- FETCH ORDER HISTORY ---
$order_sql = "SELECT * FROM orders WHERE customer_id='$customer_id' ORDER BY id DESC";
$order_res = $conn->query($order_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Account - Foodies Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        /* Cheezious Style Tabs */
        .nav-tabs { border-bottom: 1px solid #ddd; }
        .nav-link { color: #555; font-weight: 600; border: none; padding: 15px 20px; }
        .nav-link.active { 
            color: black; 
            border-bottom: 3px solid black !important; 
            background: transparent;
        }
        .nav-link:hover { color: #FF5722; }

        /* Profile Image */
        .profile-wrapper { position: relative; width: 120px; margin: 0 auto; }
        .profile-img { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 2px solid #eee; }
        .camera-icon {
            position: absolute; bottom: 5px; right: 5px;
            background: #2d3436; color: white;
            width: 35px; height: 35px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
        }
        .form-control-custom {
            background-color: #f8f9fa; border: none; padding: 12px; border-radius: 8px;
        }
    </style>
</head>
<body style="background-color: white;">

    <nav class="navbar navbar-dark bg-dark shadow">
        <div class="container">
            <a class="navbar-brand fw-bold text-warning" href="index.php">🍔 Foodies Hub</a>
            <a href="index.php" class="btn btn-outline-light btn-sm">Back to Home</a>
        </div>
    </nav>

    <div class="container mt-5">
        <h2 class="fw-bold mb-4">My Account</h2>

        <ul class="nav nav-tabs mb-4" id="myTab" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button">EDIT PROFILE</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="orders-tab" data-bs-toggle="tab" data-bs-target="#orders" type="button">ORDER HISTORY</button>
            </li>
        </ul>

        <div class="tab-content" id="myTabContent">
            
            <div class="tab-pane fade show active" id="profile">
                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <?php echo $msg; ?>
                        
                        <form method="POST" enctype="multipart/form-data" class="text-center">
                            
                            <div class="profile-wrapper mb-4">
                                <?php 
                                    $img_src = !empty($user['image']) ? "assets/uploads/users/".$user['image'] : "https://placehold.co/150x150/png?text=User";
                                ?>
                                <img src="<?php echo $img_src; ?>" class="profile-img">
                                <label for="upload_pic" class="camera-icon">
                                    <i class="bi bi-camera"></i>
                                </label>
                                <input type="file" name="profile_pic" id="upload_pic" style="display: none;">
                            </div>

                            <div class="text-start mb-3">
                                <label class="fw-bold small">Full Name</label>
                                <input type="text" name="name" class="form-control form-control-custom" value="<?php echo $user['name']; ?>" required>
                            </div>

                            <div class="text-start mb-4">
                                <label class="fw-bold small">Phone Number (Read Only)</label>
                                <input type="text" class="form-control form-control-custom text-muted" value="<?php echo $user['phone']; ?>" readonly>
                            </div>

                            <button type="submit" name="update_profile" class="btn btn-foodie w-100 py-2">SAVE CHANGES</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="orders">
                <?php if($order_res->num_rows > 0) { ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Order ID</th>
                                    <th>Date</th>
                                    <th>Total Bill</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($order = $order_res->fetch_assoc()) { 
                                    $status_color = ($order['status'] == 'Pending') ? 'bg-warning text-dark' : 'bg-success';
                                ?>
                                    <tr>
                                        <td>#<?php echo $order['id']; ?></td>
                                        <td><?php echo date("d M Y", strtotime($order['order_date'])); ?></td>
                                        <td class="fw-bold">Rs. <?php echo $order['total_amount']; ?></td>
                                        <td><span class="badge <?php echo $status_color; ?>"><?php echo $order['status']; ?></span></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php } else { ?>
                    <div class="text-center p-5 text-muted">
                        <h4>No orders yet! 🍛</h4>
                        <a href="index.php" class="btn btn-outline-dark mt-2">Order Now</a>
                    </div>
                <?php } ?>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>