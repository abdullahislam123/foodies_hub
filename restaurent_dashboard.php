<?php
session_start();
include 'db.php';

// Check Login
if (!isset($_SESSION['rest_id'])) {
    header("Location: restaurent_login.php");
    exit();
}

$rest_id = $_SESSION['rest_id'];
$rest_name = $_SESSION['rest_name']; 
$msg = "";

// --- ADD PRODUCT LOGIC ---
if (isset($_POST['add_product'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $desc = mysqli_real_escape_string($conn, $_POST['desc']);
    $price = (int)$_POST['price'];
    $cat_id = (int)$_POST['category_id'];
    
    // Get Discount Value (Default 0 if empty)
    $discount = isset($_POST['discount']) ? (int)$_POST['discount'] : 0;

    // Image Upload
    $image = $_FILES['image']['name'];
    $target = "assets/uploads/" . basename($image);

    if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
        // SQL Insert with discount_percent
        $sql = "INSERT INTO products (name, description, price, image, restaurant_id, category_id, discount_percent) 
                VALUES ('$name', '$desc', '$price', '$image', '$rest_id', '$cat_id', '$discount')";

        if ($conn->query($sql)) {
            $msg = "<div class='alert alert-success alert-dismissible fade show shadow-sm border-0'><i class='bi bi-check-circle-fill me-2'></i> Product Added Successfully! <button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
        } else {
            $msg = "<div class='alert alert-danger shadow-sm border-0'>Error adding product: " . $conn->error . "</div>";
        }
    } else {
        $msg = "<div class='alert alert-danger shadow-sm border-0'>Image upload failed.</div>";
    }
}

// Get Total Items Count
$count_sql = "SELECT count(*) as total FROM products WHERE restaurant_id = '$rest_id'";
$total_items = $conn->query($count_sql)->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Dashboard - <?php echo $rest_name; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #ff6b6b;
            --primary-hover: #ee5253;
            --dark-bg: #2d3436;
            --light-bg: #f8f9fa;
            --card-shadow: 0 10px 20px rgba(0,0,0,0.05);
        }
        body { background-color: var(--light-bg); font-family: 'Poppins', sans-serif; color: #4a4a4a; }
        .navbar { background: #ffffff !important; box-shadow: 0 2px 15px rgba(0,0,0,0.04); padding: 1rem 0; }
        .navbar-brand { color: var(--dark-bg) !important; font-size: 1.5rem; letter-spacing: -0.5px; }
        .card { border: none; border-radius: 16px; box-shadow: var(--card-shadow); overflow: hidden; background: #fff; }
        .order-banner { background: linear-gradient(135deg, #ff9f43 0%, #ff6b6b 100%); color: white; }
        .form-control, .form-select { border-radius: 10px; border: 1px solid #eee; padding: 12px; background-color: #fcfcfc; }
        .form-control:focus, .form-select:focus { border-color: var(--primary-color); box-shadow: 0 0 0 4px rgba(255, 107, 107, 0.1); }
        .btn-primary { background-color: var(--primary-color); border: none; border-radius: 10px; padding: 12px 20px; transition: all 0.3s; }
        .btn-primary:hover { background-color: var(--primary-hover); transform: translateY(-2px); }
        .table-custom thead th { background-color: #f8f9fa; color: #888; font-weight: 600; font-size: 0.85rem; text-transform: uppercase; border-bottom: 2px solid #eee; padding: 15px; }
        .table-custom tbody td { vertical-align: middle; padding: 15px; border-bottom: 1px solid #f1f1f1; }
        .item-img { width: 60px; height: 60px; object-fit: cover; border-radius: 12px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .price-badge { background: #eaffea; color: #00b894; padding: 6px 12px; border-radius: 20px; font-weight: 600; font-size: 0.9rem; }
        #imgPreview { width: 100%; height: 200px; object-fit: cover; border-radius: 12px; display: none; margin-bottom: 15px; border: 2px dashed #ddd; }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">
                <span class="text-primary"><i class="bi bi-shop-window"></i></span> 
                <?php echo $rest_name; ?>
            </a>
            <div class="d-flex align-items-center gap-3">
                <span class="badge bg-light text-dark border d-none d-md-block">
                    <i class="bi bi-circle-fill text-success me-1" style="font-size: 8px;"></i> Online
                </span>
                <a href="restaurent_logout.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    <i class="bi bi-box-arrow-right me-1"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row mb-5">
            <div class="col-12">
                <div class="card order-banner">
                    <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h2 class="fw-bold mb-1"><i class="bi bi-bell-fill me-2"></i>Live Orders</h2>
                            <p class="mb-0 opacity-75">Manage incoming orders efficiently from here.</p>
                        </div>
                        <a href="restaurent_order.php" class="btn btn-light text-danger fw-bold shadow px-4 py-3 rounded-pill">
                            Go to Order Panel <i class="bi bi-arrow-right-circle-fill ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                        <h5 class="fw-bold"><i class="bi bi-plus-square-fill text-primary me-2"></i> Add Item</h5>
                        <p class="text-muted small">Create a new menu item</p>
                    </div>
                    <div class="card-body px-4 pb-4 pt-2">
                        <?php echo $msg; ?>
                        
                        <form method="POST" enctype="multipart/form-data">
                            
                            <div class="mb-3 text-center">
                                <img id="imgPreview" src="#" alt="Preview">
                                <label class="form-label small fw-bold text-muted w-100 text-start">Item Image</label>
                                <input type="file" name="image" class="form-control" accept="image/*" onchange="previewImage(this)" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">Item Name</label>
                                <input type="text" name="name" class="form-control" placeholder="e.g. Cheesy Pizza" required>
                            </div>

                            <div class="row g-2">
                                <div class="col-4 mb-3">
                                    <label class="form-label small fw-bold text-muted">Price</label>
                                    <input type="number" name="price" class="form-control" placeholder="Rs" required>
                                </div>
                                
                                <div class="col-4 mb-3">
                                    <label class="form-label small fw-bold text-muted text-danger">Discount %</label>
                                    <input type="number" name="discount" class="form-control border-danger" placeholder="0" value="0">
                                </div>

                                <div class="col-4 mb-3">
                                    <label class="form-label small fw-bold text-muted">Category</label>
                                    <select name="category_id" class="form-select">
                                        <?php
                                        $cats = $conn->query("SELECT * FROM categories");
                                        if($cats && $cats->num_rows > 0){
                                            while ($c = $cats->fetch_assoc()) {
                                                echo "<option value='" . $c['id'] . "'>" . $c['name'] . "</option>";
                                            }
                                        } else {
                                            echo "<option value='1'>General</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label small fw-bold text-muted">Description</label>
                                <textarea name="desc" class="form-control" rows="3" placeholder="Describe ingredients, taste..."></textarea>
                            </div>

                            <button type="submit" name="add_product" class="btn btn-primary w-100 fw-bold shadow-sm">
                                <i class="bi bi-cloud-arrow-up-fill me-2"></i> Publish Item
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card h-100">
                    <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-end">
                        <div>
                            <h5 class="fw-bold mb-1">Menu Items</h5>
                            <span class="text-muted small">Manage existing food items</span>
                        </div>
                        <span class="badge bg-dark rounded-pill px-3 py-2"><?php echo $total_items; ?> Items Listed</span>
                    </div>
                    
                    <div class="card-body p-0 mt-3">
                        <div class="table-responsive">
                            <table class="table table-custom table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4">Product Details</th>
                                        <th>Price</th>
                                        <th class="text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $res = $conn->query("SELECT * FROM products WHERE restaurant_id = '$rest_id' ORDER BY id DESC");
                                    if ($res->num_rows > 0) {
                                        while ($row = $res->fetch_assoc()) {
                                            $disc = isset($row['discount_percent']) ? $row['discount_percent'] : 0;
                                    ?>
                                            <tr>
                                                <td class="ps-4">
                                                    <div class="d-flex align-items-center">
                                                        <img src="assets/uploads/<?php echo $row['image']; ?>" class="item-img me-3" alt="Food">
                                                        <div>
                                                            <div class="fw-bold text-dark mb-1">
                                                                <?php echo $row['name']; ?>
                                                                <?php if($disc > 0) { ?>
                                                                    <span class="badge bg-danger ms-1" style="font-size: 10px;"><?php echo $disc; ?>% OFF</span>
                                                                <?php } ?>
                                                            </div>
                                                            <small class="text-muted d-block text-truncate" style="max-width: 200px;">
                                                                <?php echo $row['description']; ?>
                                                            </small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php if($disc > 0) { 
                                                        $final_price = $row['price'] - ($row['price'] * $disc / 100);
                                                    ?>
                                                        <div class="d-flex flex-column align-items-start">
                                                            <small class="text-decoration-line-through text-muted" style="font-size: 0.8rem;">Rs. <?php echo $row['price']; ?></small>
                                                            <span class="fw-bold text-success">Rs. <?php echo (int)$final_price; ?></span>
                                                        </div>
                                                    <?php } else { ?>
                                                        <span class="price-badge">Rs. <?php echo $row['price']; ?></span>
                                                    <?php } ?>
                                                </td>
                                                <td class="text-end pe-4">
                                                    <a href="restaurant_edit_product.php?id=<?php echo $row['id']; ?>" class="btn btn-light btn-sm text-primary me-1" title="Edit">
                                                        <i class="bi bi-pencil-fill"></i>
                                                    </a>
                                                    <a href="delete_product.php?id=<?php echo $row['id']; ?>" class="btn btn-light btn-sm text-danger" onclick="return confirm('Are you sure you want to remove this item?');" title="Delete">
                                                        <i class="bi bi-trash-fill"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                    <?php
                                        }
                                    } else {
                                        echo "<tr><td colspan='3' class='text-center py-5 text-muted'><i class='bi bi-basket display-6 d-block mb-3 opacity-25'></i>No items found. Start by adding one!</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var img = document.getElementById('imgPreview');
                    img.src = e.target.result;
                    img.style.display = 'block';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>