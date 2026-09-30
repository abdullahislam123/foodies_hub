<?php
session_start();
include 'db.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['customer_id'];
$customer_name = $_SESSION['customer_name'];
$cart_count = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Favorites - Foodies Hub</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Open Sans', sans-serif; background-color: #fafafa; }
        :root { --panda-pink: #D70F64; }
        .navbar-brand { color: var(--panda-pink) !important; font-weight: bold; }
        .fp-card { background: white; border: 1px solid #f0f0f0; border-radius: 16px; overflow: hidden; transition: 0.2s; }
        .img-box { height: 160px; overflow: hidden; position: relative; }
        .img-box img { width: 100%; height: 100%; object-fit: cover; }
        .card-content { padding: 12px; }
        .btn-panda { background-color: var(--panda-pink); color: white; border: none; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
      <div class="container">
        <a class="navbar-brand fw-bold" href="main.php">foodieshub</a>
        <div class="d-flex align-items-center">
            <a href="cart.php" class="btn btn-light position-relative me-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; border: 1px solid #eee;">
                <i class="bi bi-bag"></i>
                <?php if($cart_count > 0): echo "<span class='position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger' style='font-size:10px;'>$cart_count</span>"; endif; ?>
            </a>
            <div class="dropdown">
              <button class="btn btn-light dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-person-circle fs-5"></i> <span class="fw-bold small"><?php echo $customer_name; ?></span>
              </button>
              <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2 p-2">
                <li><a class="dropdown-item p-2 active" href="#">Favorites</a></li> <li><a class="dropdown-item p-2" href="my_orders.php">Orders</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item p-2 text-danger" href="logout.php">Logout</a></li>
              </ul>
            </div>
        </div>
      </div>
    </nav>

    <div class="container my-5">
        <h3 class="fw-bold mb-4">My Favorites <i class="bi bi-heart-fill text-danger"></i></h3>

        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-4">
            <?php
            // JOIN query to get products from favorites table
            $sql = "SELECT p.* FROM products p JOIN favorites f ON p.id = f.product_id WHERE f.user_id = '$user_id' ORDER BY f.id DESC";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while($prod = $result->fetch_assoc()) {
                    $img = !empty($prod['image']) ? "assets/uploads/".$prod['image'] : "https://placehold.co/400";
            ?>
            <div class="col">
                <div class="fp-card">
                    <div class="img-box">
                        <img src="<?php echo $img; ?>">
                    </div>
                    <div class="card-content">
                        <h6 class="fw-bold mb-1 text-truncate"><?php echo $prod['name']; ?></h6>
                        <p class="text-muted small mb-2 text-truncate"><?php echo $prod['description']; ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold">Rs. <?php echo $prod['price']; ?></span>
                            
                            <form action="cart_action.php" method="POST">
                                <input type="hidden" name="product_id" value="<?php echo $prod['id']; ?>">
                                <input type="hidden" name="from_page" value="favorites.php">
                                <button type="submit" name="add_to_cart" class="btn btn-sm btn-panda">Add to Cart</button>
                            </form>
                        </div>
                        <div class="mt-2 text-center border-top pt-2">
                            <button onclick="toggleFavorite(<?php echo $prod['id']; ?>, this)" class="btn btn-sm text-danger w-100"><i class="bi bi-trash"></i> Remove</button>
                        </div>
                    </div>
                </div>
            </div>
            <?php 
                }
            } else {
                echo "<div class='col-12 text-center py-5'><h5 class='text-muted'>No favorites yet.</h5><a href='main.php' class='btn btn-outline-dark mt-2'>Explore Food</a></div>";
            }
            ?>
        </div>
    </div>

    <script>
        function toggleFavorite(pid, btn) {
            const formData = new FormData();
            formData.append('product_id', pid);
            fetch('favorite_action.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => { location.reload(); }); // Reload to remove item from list
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>