<?php
date_default_timezone_set('Asia/Karachi'); // ← PKT Fix
session_start();
include 'db.php';

// Check Login & Cart
if (!isset($_SESSION['customer_id'])) { header("Location: login.php"); exit(); }
if (empty($_SESSION['cart'])) { header("Location: main.php"); exit(); }

$user_id = $_SESSION['customer_id'];
$user_res = $conn->query("SELECT * FROM customers WHERE id='$user_id'");
$user = $user_res->fetch_assoc();

// --- CALCULATE TOTAL ---
$total_amount = 0;
$cart_items = [];
$ids = implode(',', array_keys($_SESSION['cart']));
$res = $conn->query("SELECT * FROM products WHERE id IN ($ids)");

while ($row = $res->fetch_assoc()) {
    $qty = $_SESSION['cart'][$row['id']];
    $row['qty'] = $qty;
    $price = $row['price'];
    $discount = isset($row['discount_percent']) ? $row['discount_percent'] : 0;
    $final_price = $price;
    if ($discount > 0) {
        $final_price = $price - ($price * $discount / 100);
    }
    $row['final_price'] = $final_price;
    $cart_items[] = $row;
    $total_amount += $final_price * $qty;
}

// Get delivery fee from settings table
$fee_res = $conn->query("SELECT setting_value FROM settings WHERE setting_key='delivery_fee' LIMIT 1");
$delivery_fee = ($fee_res && $fee_res->num_rows > 0) ? (int)$fee_res->fetch_assoc()['setting_value'] : 99;
$grand_total = $total_amount + $delivery_fee;
$cart_count = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;

// NASTP Companies list
$nastp_companies = [
    'NASTP HQ', 'PITB', 'NLC', 'NESCOM', 'SUPARCO', 'PAEC', 'NADRA',
    'MOD', 'Jazz', 'Telenor', 'PTCL', 'Netsol Technologies',
    'Systems Limited', 'TRG Pakistan', 'Arbisoft', 'Softech',
    'Corvit Networks', 'Techlogix', 'i2c Inc.', 'Inbox Business Technologies',
    'Other / Visitor'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout - Foodies Hub</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root { --panda-pink: #D70F64; --dark-grey: #333333; }
        body { font-family: 'Poppins', sans-serif; background-color: #f4f6f8; min-height: 100vh; }
        .navbar-custom { background: white; padding: 12px 0; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .nav-btn-cart { background: var(--panda-pink); color: white; border: none; font-weight: 700; border-radius: 8px; }

        /* Checkout Cards */
        .checkout-card { background: white; border: 1px solid #eee; border-radius: 16px; padding: 28px; margin-bottom: 20px; box-shadow: 0 2px 12px rgba(0,0,0,0.04); }
        .section-title { font-weight: 700; font-size: 1.05rem; margin-bottom: 20px; color: var(--dark-grey); display: flex; align-items: center; gap: 10px; }
        .section-title i { font-size: 1.2rem; color: var(--panda-pink); }
        .form-control:focus, .form-select:focus { border-color: var(--panda-pink); box-shadow: 0 0 0 3px rgba(215,15,100,0.1); }
        .form-control, .form-select { border-radius: 10px; padding: 11px 14px; border: 1.5px solid #e5e5e5; }

        /* NASTP Location Card */
        .nastp-badge { background: linear-gradient(135deg, #1e3a5f, #2563eb); color: white; border-radius: 12px; padding: 14px 20px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px; }
        .nastp-badge i { font-size: 1.6rem; }
        .nastp-badge .title { font-weight: 700; font-size: 0.9rem; letter-spacing: 0.5px; }
        .nastp-badge .sub { font-size: 0.78rem; opacity: 0.85; }
        .location-preview { background: #f0f7ff; border: 1.5px solid #bfdbfe; border-radius: 10px; padding: 12px 16px; margin-top: 16px; font-size: 0.88rem; color: #1e40af; display: none; }
        .location-preview i { color: #2563eb; }

        /* Summary Card */
        .summary-card { position: sticky; top: 85px; background: white; border: 1px solid #eee; border-radius: 16px; padding: 25px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
        .item-row { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 14px; align-items: flex-start; }
        .fee-row { display: flex; justify-content: space-between; font-size: 13px; color: #666; padding: 8px 0; border-top: 1px dashed #eee; }
        .total-row { display: flex; justify-content: space-between; margin-top: 10px; padding-top: 12px; border-top: 2px solid #eee; font-weight: 800; font-size: 1.15rem; }
        .btn-place-order { background: linear-gradient(135deg, var(--panda-pink), #ff6b9d); color: white; font-weight: 700; width: 100%; padding: 15px; border-radius: 12px; border: none; font-size: 1rem; transition: 0.3s; margin-top: 20px; box-shadow: 0 4px 15px rgba(215,15,100,0.35); }
        .btn-place-order:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(215,15,100,0.45); }
        .delivery-info-box { background: #fff8e1; border: 1px solid #ffd54f; border-radius: 10px; padding: 10px 14px; font-size: 0.82rem; color: #795548; margin-top: 12px; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="main.php" style="color: var(--panda-pink);">
                <i class="bi bi-fire"></i> foodieshub
                <span class="badge bg-success fw-normal" style="font-size: 10px;">SECURE CHECKOUT</span>
            </a>
            <div class="d-flex gap-3 align-items-center">
                <div class="d-none d-md-block fw-bold small text-muted">Hi, <?php echo $user['name']; ?></div>
                <a href="cart.php" class="btn nav-btn-cart position-relative px-3 py-2">
                    <i class="bi bi-bag"></i>
                    <?php if($cart_count > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:10px;"><?php echo $cart_count; ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <form action="place_order.php" method="POST">
            <div class="row g-4">
                <div class="col-lg-8">
                    <h3 class="fw-bold mb-4">Review & Place Order</h3>

                    <!-- ===== NASTP DELIVERY LOCATION ===== -->
                    <div class="checkout-card">
                        <div class="nastp-badge">
                            <i class="bi bi-building-fill"></i>
                            <div>
                                <div class="title">📍 NASTP — National Science & Technology Park</div>
                                <div class="sub">Delivery within NASTP premises only · Islamabad</div>
                            </div>
                        </div>

                        <div class="section-title"><i class="bi bi-geo-alt-fill"></i> Delivery Location</div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Company / Organization</label>
                                <select name="company" class="form-select" required onchange="updatePreview()">
                                    <option value="">— Select Company —</option>
                                    <?php foreach($nastp_companies as $co): ?>
                                    <option value="<?= htmlspecialchars($co) ?>"><?= htmlspecialchars($co) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">Floor / Level</label>
                                <select name="floor" class="form-select" required onchange="updatePreview()">
                                    <option value="">Floor</option>
                                    <option value="Ground Floor">Ground (G)</option>
                                    <option value="Mezzanine">Mezzanine</option>
                                    <option value="1st Floor">1st Floor</option>
                                    <option value="2nd Floor">2nd Floor</option>
                                    <option value="3rd Floor">3rd Floor</option>
                                    <option value="4th Floor">4th Floor</option>
                                    <option value="5th Floor">5th Floor</option>
                                    <option value="Basement">Basement</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">Office / Desk No.</label>
                                <input type="text" name="desk" id="deskInput" class="form-control" placeholder="e.g. 204 / A7" oninput="updatePreview()">
                            </div>
                        </div>

                        <div class="location-preview" id="locationPreview">
                            <i class="bi bi-pin-map-fill me-2"></i>
                            <strong>Rider will deliver to:</strong> <span id="previewText">—</span>
                        </div>

                        <!-- Hidden field for place_order.php compatibility -->
                        <input type="hidden" name="address" id="hiddenAddress">
                    </div>

                    <!-- ===== PERSONAL DETAILS ===== -->
                    <div class="checkout-card">
                        <div class="section-title"><i class="bi bi-person-lines-fill"></i> Personal Details</div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Name</label>
                                <input type="text" name="receiver_name" class="form-control" value="<?php echo $user['name']; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Phone Number</label>
                                <input type="text" name="receiver_phone" class="form-control" value="<?php echo $user['phone']; ?>" required>
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="form-label small fw-bold text-muted">Note to Rider (Optional)</label>
                            <input type="text" name="instructions" class="form-control" placeholder="e.g. Call on arrival, leave at reception...">
                        </div>
                    </div>

                    <!-- ===== PAYMENT ===== -->
                    <div class="checkout-card">
                        <div class="section-title"><i class="bi bi-wallet2"></i> Payment Method</div>
                        <div class="form-check p-3 border rounded-3 d-flex align-items-center justify-content-between" style="border-color: var(--panda-pink) !important; background: #fff0f6;">
                            <div>
                                <input class="form-check-input ms-0 me-2" type="radio" name="payment" id="cod" checked>
                                <label class="form-check-label fw-bold" for="cod">Cash on Delivery</label>
                                <div class="small text-muted mt-1">Pay the rider when food arrives</div>
                            </div>
                            <i class="bi bi-cash-stack fs-3 text-success"></i>
                        </div>
                    </div>
                </div>

                <!-- ===== ORDER SUMMARY ===== -->
                <div class="col-lg-4">
                    <div class="summary-card">
                        <h5 class="fw-bold mb-4">Order Summary</h5>
                        <h6 class="text-muted mb-3 small fw-bold text-uppercase">Items</h6>
                        
                        <?php foreach($cart_items as $item): ?>
                        <div class="item-row">
                            <span>
                                <span class="fw-bold text-dark me-1"><?php echo $item['qty']; ?>x</span>
                                <?php echo $item['name']; ?>
                                <?php if($item['discount_percent'] > 0) { ?>
                                    <span class="badge bg-warning text-dark ms-1" style="font-size:10px;">-<?php echo $item['discount_percent']; ?>%</span>
                                <?php } ?>
                            </span>
                            <span class="text-end fw-bold">Rs. <?php echo (int)($item['final_price'] * $item['qty']); ?></span>
                        </div>
                        <?php endforeach; ?>

                        <hr class="my-3">
                        <div class="fee-row"><span>Subtotal</span><span class="fw-bold">Rs. <?php echo (int)$total_amount; ?></span></div>
                        <div class="fee-row">
                            <span><i class="bi bi-bicycle me-1 text-success"></i>Delivery Fee</span>
                            <span class="fw-bold text-success">Rs. <?php echo $delivery_fee; ?></span>
                        </div>

                        <div class="total-row">
                            <span>Total</span>
                            <span style="color:var(--panda-pink);">Rs. <?php echo (int)$grand_total; ?></span>
                        </div>

                        <div class="delivery-info-box">
                            <i class="bi bi-clock me-1"></i> Estimated delivery: <strong>20–40 minutes</strong><br>
                            <i class="bi bi-geo me-1"></i> Delivery within NASTP campus only
                        </div>

                        <input type="hidden" name="total_bill" value="<?php echo $grand_total; ?>">
                        <input type="hidden" name="delivery_fee_val" value="<?php echo $delivery_fee; ?>">

                        <button type="submit" name="place_order" class="btn-place-order shadow" id="placeBtn" onclick="return validateForm()">
                            <i class="bi bi-bag-check-fill me-2"></i> Place Order
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updatePreview() {
            const company = document.querySelector('[name="company"]').value;
            const floor = document.querySelector('[name="floor"]').value;
            const desk = document.getElementById('deskInput').value;
            const preview = document.getElementById('locationPreview');
            const previewText = document.getElementById('previewText');
            const hidden = document.getElementById('hiddenAddress');

            if (company) {
                let loc = company;
                if (floor) loc += ', ' + floor;
                if (desk) loc += ', Office/Desk: ' + desk;
                previewText.textContent = loc;
                hidden.value = 'NASTP — ' + loc;
                preview.style.display = 'block';
            } else {
                preview.style.display = 'none';
                hidden.value = '';
            }
        }

        function validateForm() {
            const company = document.querySelector('[name="company"]').value;
            const floor = document.querySelector('[name="floor"]').value;
            if (!company || !floor) {
                alert('Please select your Company and Floor for delivery.');
                return false;
            }
            updatePreview(); // ensure hidden field is filled
            return true;
        }
    </script>
</body>
</html>