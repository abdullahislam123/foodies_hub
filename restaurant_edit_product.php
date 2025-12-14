<?php
session_start();
include 'db.php';

// Security: Check Login
if(!isset($_SESSION['rest_id'])){
    header("Location: restaurant_login.php");
    exit();
}

$rest_id = $_SESSION['rest_id'];
$rest_name = $_SESSION['rest_name'] ?? 'Restaurant Panel';
$msg = "";

// 1. Fetch Product Data to Show
if(isset($_GET['id'])){
    $pid = $_GET['id'];
    $sql = "SELECT * FROM products WHERE id='$pid' AND restaurant_id='$rest_id'";
    $result = $conn->query($sql);
    
    if($result->num_rows > 0){
        $product = $result->fetch_assoc();
    } else {
        echo "<div class='container mt-5'><div class='alert alert-danger'>Product not found or access denied. <a href='restaurent_dashboard.php'>Go Back</a></div></div>";
        exit();
    }
}

// 2. Update Logic
if(isset($_POST['update_product'])){
    $pid = $_POST['product_id'];
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $desc = mysqli_real_escape_string($conn, $_POST['desc']);
    $price = (int)$_POST['price'];
    $cat_id = (int)$_POST['category_id'];
    
    // NEW: Get Discount
    $discount = isset($_POST['discount']) ? (int)$_POST['discount'] : 0;
    
    // Image Handling
    if(!empty($_FILES['image']['name'])){
        $image = time() . "_" . basename($_FILES['image']['name']);
        $target = "assets/uploads/" . $image;
        
        if(move_uploaded_file($_FILES['image']['tmp_name'], $target)){
            // Update query with image AND discount
            $sql = "UPDATE products SET name='$name', description='$desc', price='$price', category_id='$cat_id', discount_percent='$discount', image='$image' WHERE id='$pid' AND restaurant_id='$rest_id'";
            // Update the variable for display immediately
            $product['image'] = $image;
        } else {
            $msg = "<div class='alert alert-danger shadow-sm border-0'>Image upload failed.</div>";
        }
    } else {
        // Update query without image BUT with discount
        $sql = "UPDATE products SET name='$name', description='$desc', price='$price', category_id='$cat_id', discount_percent='$discount' WHERE id='$pid' AND restaurant_id='$rest_id'";
    }

    if(isset($sql) && $conn->query($sql)){
        $msg = "<div class='alert alert-success alert-dismissible fade show shadow-sm border-0'><i class='bi bi-check-circle-fill me-2'></i> Product Updated Successfully! <button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
        // Refresh data variables
        $product['name'] = $name;
        $product['description'] = $desc;
        $product['price'] = $price;
        $product['category_id'] = $cat_id;
        $product['discount_percent'] = $discount;
    } else {
        if(empty($msg)) $msg = "<div class='alert alert-danger shadow-sm border-0'>Error updating product.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Item - <?php echo $rest_name; ?></title>
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
        }

        body {
            background-color: var(--light-bg);
            font-family: 'Poppins', sans-serif;
            color: #4a4a4a;
        }

        /* Card Styling */
        .card-edit {
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            background: #fff;
            overflow: hidden;
        }

        /* Image Preview Area */
        .img-preview-container {
            position: relative;
            width: 150px;
            height: 150px;
            margin: 0 auto;
        }
        .img-preview {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 20px;
            border: 4px solid #fff;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .img-upload-btn {
            position: absolute;
            bottom: -10px;
            right: -10px;
            background: var(--dark-bg);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            border: 3px solid #fff;
        }
        .img-upload-btn:hover {
            transform: scale(1.1);
            background: var(--primary-color);
        }

        /* Form Controls */
        .form-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .form-control, .form-select {
            border-radius: 10px;
            padding: 12px;
            border: 1px solid #eee;
            background-color: #fcfcfc;
            font-size: 0.95rem;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(255, 107, 107, 0.1);
        }
        .input-group-text {
            border-radius: 10px 0 0 10px;
            border: 1px solid #eee;
            background: #f8f9fa;
            color: #888;
        }

        /* Buttons */
        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-primary:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
        }
        .btn-back {
            color: #888;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            transition: color 0.2s;
        }
        .btn-back:hover { color: var(--dark-bg); }
    </style>
</head>
<body>

<div class="container py-5">
    
    <div class="row justify-content-center">
        <div class="col-lg-7 col-md-9">
            
            <div class="d-flex justify-content-between align-items-center mb-4 px-2">
                <a href="restaurent_dashboard.php" class="btn-back">
                    <i class="bi bi-arrow-left me-2"></i> Back to Menu
                </a>
                <h5 class="fw-bold m-0 text-dark">Edit Product</h5>
            </div>

            <div class="card card-edit">
                <div class="card-body p-4 p-md-5">
                    
                    <?php echo $msg; ?>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        
                        <div class="text-center mb-5">
                            <div class="img-preview-container">
                                <img id="preview" src="assets/uploads/<?php echo $product['image']; ?>" class="img-preview" alt="Product Image">
                                <label for="fileInput" class="img-upload-btn shadow-sm" title="Change Image">
                                    <i class="bi bi-camera-fill"></i>
                                </label>
                            </div>
                            <input type="file" id="fileInput" name="image" class="d-none" accept="image/*" onchange="previewImage(this)">
                            <div class="small text-muted mt-3">Click the camera icon to change image</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Item Name</label>
                            <input type="text" name="name" class="form-control" value="<?php echo $product['name']; ?>" required placeholder="e.g. Zinger Burger">
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Price</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rs.</span>
                                    <input type="number" name="price" class="form-control" value="<?php echo $product['price']; ?>" required>
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label text-danger">Discount %</label>
                                <div class="input-group">
                                    <input type="number" name="discount" class="form-control border-danger" value="<?php echo isset($product['discount_percent']) ? $product['discount_percent'] : 0; ?>" placeholder="0">
                                    <span class="input-group-text border-danger bg-white text-danger">%</span>
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Category</label>
                                <select name="category_id" class="form-select">
                                    <?php
                                    $cats = $conn->query("SELECT * FROM categories");
                                    while($c = $cats->fetch_assoc()){
                                        $selected = ($c['id'] == $product['category_id']) ? "selected" : "";
                                        echo "<option value='".$c['id']."' $selected>".$c['name']."</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Description</label>
                            <textarea name="desc" class="form-control" rows="4" placeholder="Describe the item..."><?php echo $product['description']; ?></textarea>
                        </div>

                        <button type="submit" name="update_product" class="btn btn-primary w-100 shadow-sm">
                            <i class="bi bi-save me-2"></i> Save Changes
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Live Image Preview Script
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('preview').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>

</body>
</html>