<?php
session_start();
include 'db.php';

// Check Logic for Navbar (Guest vs User)
$is_logged_in = isset($_SESSION['customer_id']); 
$customer_name = $is_logged_in ? $_SESSION['customer_name'] : "Guest";
$cart_count = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Cart - Foodies Hub</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f9fafa; }
        :root { --panda-pink: #D70F64; }
        .navbar-brand { color: var(--panda-pink) !important; font-weight: 800; font-size: 1.5rem; }
        .btn-panda { background-color: var(--panda-pink); color: white; border: none; padding: 12px 20px; border-radius: 8px; font-weight: 600; text-decoration: none; transition: 0.2s; }
        .btn-panda:hover { background-color: #b00c50; color: white; transform: translateY(-2px); }
        .cart-card { background: white; border-radius: 16px; border: none; padding: 25px; box-shadow: 0 4px 20px rgba(0,0,0,0.04); }
        .table img { border-radius: 8px; }
        
        /* Split Bill Styles */
        .form-switch .form-check-input { width: 3em; height: 1.5em; cursor: pointer; }
        .form-switch .form-check-input:checked { background-color: var(--panda-pink); border-color: var(--panda-pink); }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
      <div class="container">
        <a class="navbar-brand" href="main.php"><i class="bi bi-fire me-1"></i> foodieshub</a>
        <div class="d-flex align-items-center">
            <?php if($is_logged_in): ?>
                <div class="bg-light rounded-pill px-3 py-1 me-3 d-none d-md-block">
                    <i class="bi bi-person-circle text-secondary me-2"></i>
                    <span class="fw-bold small text-dark"><?php echo $customer_name; ?></span>
                </div>
                <a href="logout.php" class="btn btn-sm btn-outline-danger rounded-pill px-3">Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-sm btn-panda rounded-pill px-4">Login</a>
            <?php endif; ?>
        </div>
      </div>
    </nav>

    <div class="container my-5">
        <h3 class="fw-bold mb-4 text-dark">Your Food Cart <span class="text-muted fs-6 fw-normal">(<?php echo $cart_count; ?> items)</span></h3>

        <div class="row g-4">
            <div class="col-lg-8">
                <?php if (empty($_SESSION['cart'])) { ?>
                    <div class="text-center py-5 bg-white rounded-4 shadow-sm">
                        <img src="https://cdni.iconscout.com/illustration/premium/thumb/empty-cart-5521508-4610092.png" width="200" alt="Empty Cart">
                        <h4 class="mt-3 fw-bold text-dark">Your cart is empty</h4>
                        <p class="text-muted">Looks like you haven't made your choice yet.</p>
                        <a href="main.php" class="btn btn-panda mt-2 shadow-sm">Browse Food</a>
                    </div>
                <?php } else { ?>
                    <div class="cart-card">
                        <div class="table-responsive">
                            <table class="table table-borderless align-middle mb-0">
                                <thead class="border-bottom">
                                    <tr class="text-secondary small text-uppercase">
                                        <th style="width: 40%;">Item</th>
                                        <th style="width: 20%;">Price</th>
                                        <th style="width: 20%;">Quantity</th>
                                        <th style="width: 15%;">Total</th>
                                        <th style="width: 5%;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $total_amount = 0;
                                    foreach ($_SESSION['cart'] as $pid => $qty) {
                                        $sql = "SELECT * FROM products WHERE id = $pid";
                                        $result = $conn->query($sql);
                                        if ($result->num_rows > 0) {
                                            $row = $result->fetch_assoc();
                                            
                                            // --- DISCOUNT LOGIC ADDED HERE ---
                                            $price = $row['price'];
                                            $discount = isset($row['discount_percent']) ? $row['discount_percent'] : 0;
                                            $final_price = $price;
                                            
                                            if ($discount > 0) {
                                                $saved = ($price * $discount) / 100;
                                                $final_price = $price - $saved;
                                            }

                                            // Calculate Subtotal using FINAL PRICE
                                            $subtotal = $final_price * $qty;
                                            $total_amount += $subtotal;
                                            
                                            $img = !empty($row['image']) ? "assets/uploads/".$row['image'] : "https://placehold.co/100";
                                    ?>
                                    <tr class="border-bottom">
                                        <td>
                                            <div class="d-flex align-items-center gap-3 py-2">
                                                <img src="<?php echo $img; ?>" width="60" height="60" style="object-fit:cover;">
                                                <div>
                                                    <div class="fw-bold text-dark"><?php echo $row['name']; ?></div>
                                                    <?php if($discount > 0) { ?>
                                                        <span class="badge bg-warning text-dark" style="font-size: 10px;"><?php echo $discount; ?>% OFF</span>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if($discount > 0) { ?>
                                                <div class="d-flex flex-column">
                                                    <small class="text-decoration-line-through text-muted" style="font-size: 12px;">Rs. <?php echo $price; ?></small>
                                                    <span class="fw-bold text-danger">Rs. <?php echo (int)$final_price; ?></span>
                                                </div>
                                            <?php } else { ?>
                                                <span class="text-muted">Rs. <?php echo $price; ?></span>
                                            <?php } ?>
                                        </td>
                                        <td>
                                            <form action="cart_action.php" method="POST" class="d-flex align-items-center bg-light rounded-pill border px-2 py-1" style="width: fit-content;">
                                                <input type="hidden" name="product_id" value="<?php echo $pid; ?>">
                                                <input type="hidden" name="update_qty" value="1">
                                                <input type="number" name="quantity" value="<?php echo $qty; ?>" min="1" class="form-control border-0 bg-transparent text-center p-0" style="width: 40px; height: 25px;" onchange="this.form.submit()">
                                            </form>
                                        </td>
                                        <td class="fw-bold text-dark">Rs. <?php echo (int)$subtotal; ?></td>
                                        <td>
                                            <a href="cart_action.php?remove=<?php echo $pid; ?>" class="btn btn-sm btn-light text-danger rounded-circle" title="Remove">
                                                <i class="bi bi-trash-fill"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php 
                                        } 
                                    } 
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php } ?>
            </div>

            <?php if (!empty($_SESSION['cart'])) { ?>
            <div class="col-lg-4">
                <div class="cart-card">
                    <h5 class="fw-bold mb-4">Order Summary</h5>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal</span>
                        <span class="fw-bold">Rs. <?php echo (int)$total_amount; ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Delivery Fee</span>
                        <span class="text-success fw-bold">Free</span>
                    </div>
                    
                    <hr class="text-muted opacity-25">
                    
                    <div class="d-flex justify-content-between mb-4">
                        <span class="fs-5 fw-bold text-dark">Total</span>
                        <span class="fs-5 fw-bold" style="color:var(--panda-pink);">Rs. <?php echo (int)$total_amount; ?></span>
                    </div>

                    <div class="mt-4 pt-3 border-top mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="fw-bold text-dark" style="font-size: 14px;">
                                <i class="bi bi-people-fill text-secondary me-2"></i> Split Bill?
                            </span>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="splitToggle">
                            </div>
                        </div>

                        <div id="splitBox" class="p-3 rounded bg-light border" style="display: none;">
                            <label class="small text-muted mb-2 d-block text-center fw-bold">Number of People</label>
                            <div class="input-group mb-3 justify-content-center">
                                <button class="btn btn-white border bg-white text-secondary" onclick="changePeople(-1)">-</button>
                                <input type="number" id="totalFriends" class="form-control text-center border-start-0 border-end-0 bg-white" value="2" min="1" readonly style="max-width: 60px;">
                                <button class="btn btn-white border bg-white text-secondary" onclick="changePeople(1)">+</button>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                                <span class="small fw-bold text-muted">Per Person:</span>
                                <span class="fw-bold text-success fs-5" id="perPersonShare">Rs. 0</span>
                            </div>
                        </div>
                    </div>
                    <?php if ($is_logged_in): ?>
                        <a href="checkout.php" class="btn btn-panda w-100 py-3 shadow-sm">Proceed to Checkout <i class="bi bi-arrow-right ms-2"></i></a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-panda w-100 py-3 shadow-sm">Log in to Checkout</a>
                        <p class="text-muted small text-center mt-2 mb-0">Please login to place order</p>
                    <?php endif; ?>

                </div>
            </div>
            <?php } ?>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // 1. Select Elements
        const splitToggle = document.getElementById('splitToggle');
        const splitBox = document.getElementById('splitBox');
        const friendsInput = document.getElementById('totalFriends');
        const shareText = document.getElementById('perPersonShare');
        
        // Get PHP Total Amount into JS
        const grandTotal = <?php echo isset($total_amount) ? $total_amount : 0; ?>; 

        // 2. Toggle Visibility
        if(splitToggle) {
            splitToggle.addEventListener('change', function() {
                if (this.checked) {
                    splitBox.style.display = 'block';
                    calculateShare(); 
                } else {
                    splitBox.style.display = 'none';
                }
            });
        }

        // 3. Change Number Logic
        function changePeople(change) {
            let current = parseInt(friendsInput.value);
            let newVal = current + change;
            if (newVal < 1) newVal = 1; 
            friendsInput.value = newVal;
            calculateShare();
        }

        // 4. Calculation Logic
        function calculateShare() {
            let people = parseInt(friendsInput.value);
            let share = Math.ceil(grandTotal / people); 
            shareText.innerText = "Rs. " + share;
        }
    </script>
</body>
</html>