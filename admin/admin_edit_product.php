<?php
session_start();
include '../db.php';

// Security Check
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

$id = $_GET['id'];
$msg = "";

// --- UPDATE LOGIC ---
if (isset($_POST['update_product'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    $price = (int)$_POST['price'];
    $restaurant_id = (int)$_POST['restaurant_id'];
    
    // Image Handling
    if (!empty($_FILES['image']['name'])) {
        $image = time() . "_" . basename($_FILES['image']['name']);
        $target = "../assets/uploads/" . $image;
        if(move_uploaded_file($_FILES['image']['tmp_name'], $target)){
            $sql = "UPDATE products SET name='$name', description='$desc', price='$price', restaurant_id='$restaurant_id', image='$image' WHERE id='$id'";
        }
    } else {
        // Agar image change nahi ki to purani hi rahegi
        $sql = "UPDATE products SET name='$name', description='$desc', price='$price', restaurant_id='$restaurant_id' WHERE id='$id'";
    }

    if ($conn->query($sql)) {
        header("Location: admin_dashboard.php?msg=updated");
        exit();
    } else {
        $msg = "❌ Error updating record.";
    }
}

// Fetch Current Data
$sql = "SELECT * FROM products WHERE id='$id'";
$result = $conn->query($sql);
$product = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Product | Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; font-family: sans-serif; }
        .card { border-radius: 15px; border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold">Edit Item Details</h4>
                    <a href="admin_dashboard.php" class="btn btn-sm btn-outline-secondary">Cancel</a>
                </div>

                <?php if($msg) echo "<div class='alert alert-danger'>$msg</div>"; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3 text-center">
                        <label class="small text-muted mb-2 d-block">Current Image</label>
                        <img src="../assets/uploads/<?php echo $product['image']; ?>" class="rounded border" width="100" height="100" style="object-fit:cover;">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Assign Restaurant</label>
                        <select name="restaurant_id" class="form-select">
                            <?php
                            $res = $conn->query("SELECT * FROM restaurants");
                            while($r = $res->fetch_assoc()){
                                $selected = ($r['id'] == $product['restaurant_id']) ? "selected" : "";
                                echo "<option value='".$r['id']."' $selected>".$r['name']."</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Item Name</label>
                        <input type="text" name="name" class="form-control" value="<?php echo $product['name']; ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Description</label>
                        <textarea name="description" class="form-control" rows="3"><?php echo $product['description']; ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Price (Rs)</label>
                        <input type="number" name="price" class="form-control" value="<?php echo $product['price']; ?>" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small">Change Image (Optional)</label>
                        <input type="file" name="image" class="form-control">
                    </div>

                    <button type="submit" name="update_product" class="btn btn-primary w-100 fw-bold">Update Product</button>
                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>