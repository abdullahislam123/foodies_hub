<?php
session_start();
include 'db.php';

// Check Login & Cart
if (!isset($_SESSION['customer_id'])) { header("Location: login.php"); exit(); }
if (empty($_SESSION['cart'])) { header("Location: main.php"); exit(); }

$user_id = $_SESSION['customer_id'];
$user_res = $conn->query("SELECT * FROM customers WHERE id='$user_id'");
$user = $user_res->fetch_assoc();

// --- 1. CALCULATE TOTAL WITH DISCOUNT LOGIC ---
$total_amount = 0;
$cart_items = [];
$ids = implode(',', array_keys($_SESSION['cart']));

// Fetch products
$res = $conn->query("SELECT * FROM products WHERE id IN ($ids)");

while ($row = $res->fetch_assoc()) {
    $qty = $_SESSION['cart'][$row['id']];
    $row['qty'] = $qty; // Store quantity in row array
    
    // Calculate Price based on Discount
    $price = $row['price'];
    $discount = isset($row['discount_percent']) ? $row['discount_percent'] : 0;
    $final_price = $price;

    if ($discount > 0) {
        $saved = ($price * $discount) / 100;
        $final_price = $price - $saved;
    }
    
    // Save final calculated price in array to use in HTML later
    $row['final_price'] = $final_price; 

    $cart_items[] = $row;
    $total_amount += $final_price * $qty; // Add discounted price to total
}

$delivery_fee = 99;
$grand_total = $total_amount + $delivery_fee;

$cart_count = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout - Foodies Hub</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Open Sans', sans-serif; background-color: #f7f7f7; display: flex; flex-direction: column; min-height: 100vh; }
        :root { --panda-pink: #D70F64; --dark-grey: #333333; }
        
        /* NAVBAR */
        .navbar-custom { background: white; padding: 12px 0; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .nav-btn-cart { background: var(--panda-pink); color: white; border: none; font-weight: 700; border-radius: 8px; }

        /* CHECKOUT STYLES */
        .checkout-card { background: white; border: 1px solid #eee; border-radius: 12px; padding: 25px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.02); }
        .section-title { font-weight: 700; font-size: 1.1rem; margin-bottom: 20px; color: var(--dark-grey); display: flex; align-items: center; }
        .section-title i { font-size: 1.2rem; color: var(--panda-pink); margin-right: 10px; }
        .form-control:focus { border-color: var(--panda-pink); box-shadow: none; }
        
        /* Map Style */
        #map { height: 250px; width: 100%; border-radius: 12px; z-index: 1; border: 2px solid #eee; }
        
        .summary-card { position: sticky; top: 100px; background: white; border: 1px solid #eee; border-radius: 12px; padding: 25px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
        .item-row { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 14px; align-items: flex-start; }
        .total-row { display: flex; justify-content: space-between; margin-top: 15px; padding-top: 15px; border-top: 1px dashed #ddd; font-weight: 800; font-size: 1.2rem; color: var(--dark-grey); }
        
        .btn-place-order { background-color: var(--panda-pink); color: white; font-weight: 700; width: 100%; padding: 14px; border-radius: 8px; border: none; font-size: 16px; transition: 0.2s; margin-top: 20px; }
        .btn-place-order:hover { background-color: #b00c50; }

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
            <a class="navbar-brand d-flex align-items-center gap-2" href="main.php">
                <img src="assets/original_logo.png" alt="Foodies Hub" style="height: 40px;">
                <span class="badge bg-success fw-normal" style="font-size: 10px; letter-spacing: 1px;">SECURE CHECKOUT</span>
            </a>
            
            <div class="d-flex gap-3 align-items-center">
                <div class="d-none d-md-block fw-bold small text-muted">Hi, <?php echo $user['name']; ?></div>
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

    <div class="container my-5 flex-grow-1">
        <form action="place_order.php" method="POST">
            <div class="row">
                
                <div class="col-lg-8">
                    <h3 class="fw-bold mb-4">Review and place order</h3>
                    
                    <div class="checkout-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="section-title m-0"><i class="bi bi-geo-alt-fill"></i> Delivery Address</h5>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="getLocation()">
                                <i class="bi bi-crosshair"></i> Locate Me
                            </button>
                        </div>
                        
                        <div id="map" class="mb-3"></div>
                        <small class="text-muted d-block mb-3">* You can drag the pin to adjust location</small>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Complete Address</label>
                            <input type="text" name="address" id="addressInput" class="form-control" placeholder="Click 'Locate Me' or drag pin..." required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Floor / Apartment</label>
                                <input type="text" name="floor" class="form-control" placeholder="e.g. 2nd Floor">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Note to Rider</label>
                                <input type="text" name="instructions" class="form-control" placeholder="e.g. Don't ring bell">
                            </div>
                        </div>
                    </div>

                    <div class="checkout-card">
                        <h5 class="section-title"><i class="bi bi-person-lines-fill"></i> Personal Details</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Name</label>
                                <input type="text" name="receiver_name" class="form-control" value="<?php echo $user['name']; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Phone Number</label>
                                <input type="text" name="receiver_phone" class="form-control" value="<?php echo $user['phone']; ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="checkout-card">
                        <h5 class="section-title"><i class="bi bi-wallet2"></i> Payment Method</h5>
                        <div class="form-check p-3 border rounded mb-2 d-flex align-items-center justify-content-between" style="border-color: var(--panda-pink) !important; background-color: #fff0f6;">
                            <div>
                                <input class="form-check-input ms-0 me-2" type="radio" name="payment" id="cod" checked>
                                <label class="form-check-label fw-bold" for="cod">Cash on Delivery</label>
                            </div>
                            <i class="bi bi-cash-stack fs-4 text-success"></i>
                        </div>
                    </div>

                </div>

                <div class="col-lg-4">
                    <div class="summary-card">
                        <h5 class="fw-bold mb-4">Your order from</h5>
                        <h6 class="text-muted mb-3 small fw-bold text-uppercase">Items</h6>
                        
                        <?php foreach($cart_items as $item): ?>
                        <div class="item-row">
                            <span>
                                <span class="fw-bold text-dark me-1"><?php echo $item['qty']; ?>x</span> 
                                <?php echo $item['name']; ?>
                                <?php if($item['discount_percent'] > 0) { ?>
                                    <span class="badge bg-warning text-dark ms-1" style="font-size: 10px;">
                                        -<?php echo $item['discount_percent']; ?>%
                                    </span>
                                <?php } ?>
                            </span>
                            
                            <span class="text-end">
                                <?php if($item['discount_percent'] > 0) { ?>
                                    <div class="d-flex flex-column align-items-end">
                                        <small class="text-muted text-decoration-line-through" style="font-size:12px;">
                                            Rs. <?php echo $item['price'] * $item['qty']; ?>
                                        </small>
                                        <span class="fw-bold text-success">
                                            Rs. <?php echo (int)$item['final_price'] * $item['qty']; ?>
                                        </span>
                                    </div>
                                <?php } else { ?>
                                    <span class="text-muted">Rs. <?php echo $item['price'] * $item['qty']; ?></span>
                                <?php } ?>
                            </span>
                        </div>
                        <?php endforeach; ?>

                        <hr class="my-3">
                        <div class="item-row"><span class="text-muted">Subtotal</span><span>Rs. <?php echo (int)$total_amount; ?></span></div>
                        <div class="item-row"><span class="text-muted">Delivery Fee</span><span>Rs. <?php echo $delivery_fee; ?></span></div>
                        <div class="total-row"><span>Total</span><span style="color:var(--panda-pink);">Rs. <?php echo (int)$grand_total; ?></span></div>
                        
                        <input type="hidden" name="total_bill" value="<?php echo $grand_total; ?>">
                        
                        <button type="submit" name="place_order" class="btn-place-order shadow">Place Order</button>
                    </div>
                </div>

            </div>
        </form>
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

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // --- 1. SETUP MAP ---
        var map = L.map('map').setView([31.5204, 74.3587], 13);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        var marker = L.marker([31.5204, 74.3587], {draggable: true}).addTo(map);

        // --- 2. LOGIC: COORDINATES -> ADDRESS ---
        function fillAddress(lat, lng) {
            var input = document.getElementById('addressInput');
            input.value = "📍 Fetching address...";
            input.style.opacity = "0.6";

            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
                .then(response => response.json())
                .then(data => {
                    if(data.display_name) {
                        input.value = data.display_name;
                        input.style.opacity = "1";
                    } else {
                        input.value = "";
                        input.placeholder = "Address not found. Please type manually.";
                        input.style.opacity = "1";
                    }
                })
                .catch(err => {
                    console.error(err);
                    input.value = "";
                    input.placeholder = "Network error. Please type manually.";
                    input.style.opacity = "1";
                });
        }

        // --- 3. LOGIC: GET USER LIVE LOCATION ---
        function getLocation() {
            if (navigator.geolocation) {
                document.getElementById('addressInput').placeholder = "Locating you...";
                navigator.geolocation.getCurrentPosition(showPosition, showError);
            } else {
                alert("Geolocation is not supported by this browser.");
            }
        }

        function showPosition(position) {
            var lat = position.coords.latitude;
            var lon = position.coords.longitude;
            map.setView([lat, lon], 18);
            marker.setLatLng([lat, lon]);
            fillAddress(lat, lon);
        }

        function showError(error) {
            var msg = "";
            switch(error.code) {
                case error.PERMISSION_DENIED: msg = "User denied location request."; break;
                case error.POSITION_UNAVAILABLE: msg = "Location information unavailable."; break;
                case error.TIMEOUT: msg = "Request timed out."; break;
                default: msg = "Unknown error.";
            }
            alert(msg);
        }

        // --- 4. EVENT LISTENERS ---
        marker.on('dragend', function(e) {
            var position = marker.getLatLng();
            fillAddress(position.lat, position.lng);
            map.panTo(position);
        });

        window.onload = getLocation;
    </script>
</body>
</html>