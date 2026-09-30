<?php
session_start();
include 'db.php';

// Fetch Cart Count for Navbar
$cart_count = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
$is_logged_in = isset($_SESSION['customer_id']);
$customer_name = isset($_SESSION['customer_name']) ? $_SESSION['customer_name'] : "Guest";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Placed! - Foodies Hub</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <style>
        body {
            font-family: 'Open Sans', sans-serif;
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            flex-direction: column; /* Stacks Nav, Content, Footer */
        }
        :root { --panda-pink: #D70F64; --dark-grey: #333333; }

        /* NAVBAR STYLES */
        .navbar-custom { background: white; padding: 12px 0; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .nav-btn-cart { background: var(--panda-pink); color: white; border: none; font-weight: 700; border-radius: 8px; }
        
        /* SUCCESS CARD STYLES */
        .main-content {
            flex-grow: 1; /* Pushes footer down */
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 50px 0;
        }
        .success-card {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            max-width: 400px;
            width: 90%;
            position: relative;
            z-index: 10;
            text-align: center;
        }
        .check-icon {
            width: 80px; height: 80px;
            background: #d4edda; color: #28a745;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 40px; margin: 0 auto 20px;
        }
        .btn-panda {
            background-color: var(--panda-pink); color: white;
            font-weight: bold; border: none; padding: 12px 30px;
            border-radius: 8px; text-decoration: none;
            display: inline-block; margin-top: 20px; transition: 0.3s;
        }
        .btn-panda:hover { background-color: #b00c50; color: white; }

        /* FOOTER STYLES */
        .main-footer { background-color: white; border-top: 1px solid #eee; padding-top: 60px; margin-top: auto; }
        .footer-logo { font-weight: 800; font-size: 1.5rem; color: var(--panda-pink); text-decoration: none; display: inline-block; margin-bottom: 15px; }
        .footer-heading { font-weight: 700; color: var(--dark-grey); margin-bottom: 20px; font-size: 1.1rem; }
        .footer-link { display: block; color: #666; text-decoration: none; margin-bottom: 12px; font-size: 0.95rem; transition: 0.3s; }
        .footer-link:hover { color: var(--panda-pink); transform: translateX(5px); }
        .social-btn { width: 40px; height: 40px; border-radius: 50%; background: #f8f9fa; display: inline-flex; align-items: center; justify-content: center; color: #555; text-decoration: none; margin-right: 10px; transition: 0.3s; }
        .social-btn:hover { background: var(--panda-pink); color: white; }
        .footer-bottom { border-top: 1px solid #eee; padding: 25px 0; margin-top: 40px; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container">
            <a class="navbar-brand" href="main.php">
                <img src="assets/original_logo.png" alt="Foodies Hub" style="height: 40px;">
            </a>
            
            <div class="d-flex gap-3 align-items-center">
                <?php if($is_logged_in): ?>
                    <div class="d-none d-md-block fw-bold small">Hi, <?php echo $customer_name; ?></div>
                <?php endif; ?>
                <a href="cart.php" class="btn nav-btn-cart position-relative">
                    <i class="bi bi-bag"></i> 
                    <?php if($cart_count > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:10px;">
                            <?php echo $cart_count; ?>
                        </span>
                    <?php endif; ?>
                </a>
            </div>
        </div>
    </nav>

    <div class="main-content">
        <div class="success-card animate__animated animate__fadeInUp">
            <div class="check-icon animate__animated animate__bounceIn animate__delay-1s">
                <i class="bi bi-check-lg"></i>
            </div>
            <h2 class="fw-bold text-dark">Order Placed!</h2>
            <p class="text-muted mt-2">Thank you for your purchase. Your food is being prepared and will be with you shortly.</p>
            
            <a href="my_orders.php" class="btn-panda">Track Order</a>
            <br>
            <a href="main.php" class="text-muted small mt-3 d-inline-block text-decoration-none">Back to Home</a>
        </div>
    </div>

    <footer class="main-footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4">
                    <a href="main.php" class="footer-logo">foodieshub.</a>
                    <p class="text-muted small">
                        Order delicious food from your favorite local restaurants. Fast delivery, fresh food, and best prices.
                    </p>
                    <div class="mt-3">
                        <a href="#" class="social-btn"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="social-btn"><i class="bi bi-instagram"></i></a>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <h5 class="footer-heading">Company</h5>
                    <a href="#" class="footer-link">About Us</a>
                    <a href="#" class="footer-link">Careers</a>
                    <a href="#" class="footer-link">Privacy Policy</a>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <h5 class="footer-heading">Get Help</h5>
                    <a href="#" class="footer-link">Help Center</a>
                    <a href="#" class="footer-link">FAQs</a>
                    <a href="#" class="footer-link">Contact Support</a>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <h5 class="footer-heading">Install App</h5>
                    <p class="text-muted small">Available on iOS and Android</p>
                    <a href="#" class="btn btn-outline-dark btn-sm"><i class="bi bi-apple"></i> App Store</a>
                    <a href="#" class="btn btn-outline-dark btn-sm"><i class="bi bi-google-play"></i> Play Store</a>
                </div>
            </div>

            <div class="footer-bottom text-center">
                <p class="text-muted small mb-0">&copy; 2025 Foodies Hub. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

    <script>
        // Run Confetti
        window.onload = function() {
            var duration = 3 * 1000;
            var animationEnd = Date.now() + duration;
            var defaults = { startVelocity: 30, spread: 360, ticks: 60, zIndex: 0 };

            function randomInRange(min, max) { return Math.random() * (max - min) + min; }

            var interval = setInterval(function() {
                var timeLeft = animationEnd - Date.now();
                if (timeLeft <= 0) return clearInterval(interval);
                var particleCount = 50 * (timeLeft / duration);
                confetti(Object.assign({}, defaults, { particleCount, origin: { x: randomInRange(0.1, 0.3), y: Math.random() - 0.2 } }));
                confetti(Object.assign({}, defaults, { particleCount, origin: { x: randomInRange(0.7, 0.9), y: Math.random() - 0.2 } }));
            }, 250);
        };
    </script>

</body>
</html>