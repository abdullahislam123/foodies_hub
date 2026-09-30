<?php
session_start();
include 'db.php';
include 'sidebar.php';

$cart_count = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Menu - Foodies Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Open Sans', sans-serif; background-color: #F7F7F7; scroll-behavior: smooth; }
        :root { --panda-pink: #D70F64; --dark-grey: #333333; }

        /* NAVBAR */
        .navbar-custom { background: white; padding: 12px 0; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .nav-btn-cart { background: var(--panda-pink); color: white; border: none; font-weight: 700; border-radius: 8px; position: relative; }
        
        /* STICKY CATEGORY NAV */
        .cat-nav-container { background: white; border-bottom: 1px solid #eee; position: sticky; top: 0; z-index: 999; box-shadow: 0 4px 10px rgba(0,0,0,0.03); }
        .cat-nav { display: flex; overflow-x: auto; white-space: nowrap; padding: 15px 0; gap: 10px; }
        .cat-link { 
            display: inline-block; padding: 8px 16px; 
            color: #333; font-weight: 600; text-decoration: none; border-radius: 20px; 
            background: #f5f5f5; transition: 0.2s; font-size: 0.9rem;
        }
        .cat-link:hover, .cat-link.active { background-color: var(--panda-pink); color: white; }

        /* PRODUCT LIST */
        .menu-category-title { margin-top: 40px; margin-bottom: 20px; font-weight: 800; color: #333; border-left: 4px solid var(--panda-pink); padding-left: 10px; }
        .panda-card {
            background: white; border: 1px solid #eee; border-radius: 12px;
            overflow: hidden; transition: 0.3s; cursor: pointer; height: 100%;
            position: relative; display: flex; flex-direction: column;
        }
        .panda-card:hover { box-shadow: 0 5px 15px rgba(0,0,0,0.08); border-color: transparent; }
        .img-box { position: relative; height: 160px; overflow: hidden; }
        .prod-img { width: 100%; height: 100%; object-fit: cover; transition: 0.5s; }
        .panda-card:hover .prod-img { transform: scale(1.05); }
        .top-tag { position: absolute; top: 10px; left: 10px; background: var(--panda-pink); color: white; font-size: 0.7rem; font-weight: bold; padding: 4px 8px; border-radius: 4px; }
        .card-body { padding: 15px; flex-grow: 1; }
        .prod-title { font-weight: 700; color: var(--dark-grey); font-size: 1rem; margin-bottom: 5px; }
        .prod-desc { font-size: 0.85rem; color: #666; display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; margin-bottom: 10px; }
        .btn-add-mini {
            width: 32px; height: 32px; border-radius: 50%;
            background: #fff; border: 1px solid #ddd; color: var(--dark-grey);
            display: flex; align-items: center; justify-content: center; transition: 0.2s;
        }
        .panda-card:hover .btn-add-mini { background: var(--panda-pink); color: white; border-color: var(--panda-pink); }

        /* MODAL STYLES */
        .modal-content { border-radius: 16px; border: none; }
        .modal-header-img { width: 100%; height: 200px; object-fit: cover; }
        .btn-modal-panda { background: var(--panda-pink); color: white; width: 100%; font-weight: 700; border-radius: 8px; padding: 12px; border:none; transition: 0.3s; }
        .btn-modal-panda:hover { background: #b00c50; }

        /* FOOTER */
        .main-footer { background-color: white; border-top: 1px solid #eee; padding-top: 60px; margin-top: 50px; }
        .footer-logo { font-weight: 800; font-size: 1.5rem; color: var(--panda-pink); text-decoration: none; display: inline-block; margin-bottom: 15px; }
        .footer-heading { font-weight: 700; color: var(--dark-grey); margin-bottom: 20px; font-size: 1.1rem; }
        .footer-link { display: block; color: #666; text-decoration: none; margin-bottom: 12px; font-size: 0.95rem; transition: 0.3s; }
        .footer-link:hover { color: var(--panda-pink); transform: translateX(5px); }
        .social-btn { width: 40px; height: 40px; border-radius: 50%; background: #f8f9fa; display: inline-flex; align-items: center; justify-content: center; color: #555; text-decoration: none; margin-right: 10px; transition: 0.3s; }
        .social-btn:hover { background: var(--panda-pink); color: white; }
        .app-store-btn { border: 1px solid #ddd; border-radius: 8px; padding: 8px 15px; display: inline-flex; align-items: center; text-decoration: none; color: #333; margin-bottom: 10px; margin-right: 5px; transition: 0.3s; }
        .app-store-btn:hover { border-color: var(--panda-pink); background: #fff5f8; color: var(--panda-pink); }
        .footer-bottom { border-top: 1px solid #eee; padding: 25px 0; margin-top: 40px; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <a class="navbar-brand" href="main.php">
                    <img src="assets/original_logo.png" alt="Foodies Hub Logo" style="height: 40px;">
                </a>
            </div>
            <div class="d-flex gap-3">
                <button class="btn border-0" data-bs-toggle="offcanvas" data-bs-target="#accountSidebar"><i class="bi bi-person fs-5"></i></button>
                <a href="cart.php" class="btn nav-btn-cart">
                    <i class="bi bi-bag"></i> <span id="cart-count-badge"><?php echo $cart_count; ?></span>
                </a>
            </div>
        </div>
    </nav>

    <div class="cat-nav-container">
        <div class="container">
            <div class="cat-nav">
                <?php 
                $cat_res = $conn->query("SELECT * FROM categories");
                while($cat = $cat_res->fetch_assoc()) {
                    echo '<a href="#cat-'.$cat['id'].'" class="cat-link">'.$cat['name'].'</a>';
                }
                ?>
            </div>
        </div>
    </div>

    <div class="container pb-5">
        <?php
        $cat_res = $conn->query("SELECT * FROM categories");
        while($cat = $cat_res->fetch_assoc()) {
            $cat_id = $cat['id'];
            $prod_res = $conn->query("SELECT * FROM products WHERE category_id = $cat_id");
            if($prod_res->num_rows > 0) {
        ?>
            <div id="cat-<?php echo $cat_id; ?>" style="scroll-margin-top: 140px;">
                <h3 class="menu-category-title"><?php echo $cat['name']; ?></h3>
                
                <div class="row g-4">
                    <?php while($prod = $prod_res->fetch_assoc()) { 
                        $img = !empty($prod['image']) ? "assets/uploads/".$prod['image'] : "https://placehold.co/400x300";
                        $modalID = "modal" . $prod['id'];
                    ?>
                    <div class="col-md-3 col-sm-6">
                        <div class="panda-card" data-bs-toggle="modal" data-bs-target="#<?php echo $modalID; ?>">
                            <div class="img-box">
                                <?php if(isset($prod['is_popular']) && $prod['is_popular'] == 1) { ?>
                                    <span class="top-tag">Popular</span>
                                <?php } ?>
                                <img src="<?php echo $img; ?>" class="prod-img">
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <h6 class="prod-title text-truncate"><?php echo $prod['name']; ?></h6>
                                    <h6 class="fw-bold" style="color:#333;">Rs. <?php echo $prod['price']; ?></h6>
                                </div>
                                <p class="prod-desc"><?php echo $prod['description']; ?></p>
                                <div class="d-flex justify-content-end mt-auto">
                                    <div class="btn-add-mini"><i class="bi bi-plus-lg"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="<?php echo $modalID; ?>" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div style="position:relative;">
                                    <img src="<?php echo $img; ?>" class="modal-header-img">
                                    <button type="button" class="btn-close bg-white p-2 rounded-circle" data-bs-dismiss="modal" style="position:absolute; top:15px; right:15px; opacity:1;"></button>
                                </div>
                                <div class="modal-body p-4">
                                    <div class="d-flex justify-content-between">
                                        <h4 class="fw-bold"><?php echo $prod['name']; ?></h4>
                                        <h4 class="fw-bold" style="color:var(--panda-pink);">Rs. <?php echo $prod['price']; ?></h4>
                                    </div>
                                    <p class="text-muted small mb-4"><?php echo $prod['description']; ?></p>
                                    
                                    <hr class="opacity-10">
                                    
                                    <form onsubmit="addToCartAjax(event, this)">
                                        <input type="hidden" name="product_id" value="<?php echo $prod['id']; ?>">
                                        <input type="hidden" name="from_page" value="menu.php">
                                        
                                        <div class="d-flex justify-content-between align-items-center mb-3 p-2 border rounded bg-light">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" checked>
                                                <label class="form-check-label fw-bold">Standard Serving</label>
                                            </div>
                                        </div>

                                        <button type="submit" class="btn-modal-panda">Add to Cart</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>
        <?php } } ?>
    </div>

    <footer class="main-footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4">
                    <a href="main.php" class="footer-logo">foodieshub.</a>
                    <p class="text-muted small">
                        Order delicious food from your favorite local restaurants.
                    </p>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5 class="footer-heading">Company</h5>
                    <a href="#" class="footer-link">About Us</a>
                    <a href="#" class="footer-link">Careers</a>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5 class="footer-heading">Get Help</h5>
                    <a href="#" class="footer-link">Help Center</a>
                    <a href="#" class="footer-link">FAQs</a>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5 class="footer-heading">Install App</h5>
                    <a href="#" class="app-store-btn"><i class="bi bi-apple fs-4 me-2"></i> App Store</a>
                    <a href="#" class="app-store-btn"><i class="bi bi-google-play fs-4 me-2"></i> Play Store</a>
                </div>
            </div>
            <div class="footer-bottom text-center">
                <p class="text-muted small mb-0">&copy; 2025 Foodies Hub. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // --- AJAX FUNCTION to prevent page redirect ---
        function addToCartAjax(event, form) {
            event.preventDefault(); // Stop page from refreshing/redirecting

            const formData = new FormData(form);
            const btn = form.querySelector('button[type="submit"]');
            const originalText = btn.innerText;

            btn.innerText = "Adding...";
            btn.disabled = true;

            fetch('cart_action.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                let result = data.trim();

                if (result === 'login_required') {
                    window.location.href = 'login.php';
                } else {
                    // Update the Cart Badge in Navbar
                    document.getElementById('cart-count-badge').innerText = result;

                    // Close the Modal
                    const modalEl = form.closest('.modal');
                    const modalInstance = bootstrap.Modal.getInstance(modalEl);
                    if (modalInstance) {
                        modalInstance.hide();
                    }

                    // Reset Button
                    btn.innerText = "Added!";
                    setTimeout(() => {
                        btn.innerText = originalText;
                        btn.disabled = false;
                    }, 1000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                btn.innerText = originalText;
                btn.disabled = false;
            });
        }
    </script>
</body>
</html>