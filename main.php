<?php
session_start();
include 'db.php';

// --- 1. USER & FAVORITES ---
$is_logged_in = isset($_SESSION['customer_id']);
$customer_name = isset($_SESSION['customer_name']) ? $_SESSION['customer_name'] : "Guest";
$customer_phone = isset($_SESSION['customer_phone']) ? $_SESSION['customer_phone'] : "";
$user_email = "";

$fav_products = [];
$has_orders = false;

if ($is_logged_in) {
    $uid = $_SESSION['customer_id'];
    
    // Profile
    $u_res = $conn->query("SELECT * FROM customers WHERE id='$uid'");
    if ($u_res && $u_res->num_rows > 0) {
        $u_row = $u_res->fetch_assoc();
        $customer_name = $u_row['name'];
        $customer_phone = $u_row['phone'];
        $user_email = isset($u_row['email']) ? $u_row['email'] : ""; 
    }
    
    // Favorites
    $fav_res = $conn->query("SELECT product_id FROM favorites WHERE user_id='$uid'");
    while($r = $fav_res->fetch_assoc()){
        $fav_products[] = $r['product_id'];
    }

    // Orders Check
    $order_check = $conn->query("SELECT id FROM orders WHERE customer_id='$uid' LIMIT 1");
    if ($order_check && $order_check->num_rows > 0) {
        $has_orders = true;
    }
}

$cart_count = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;

// --- 2. LOGIC (Budget & Search) ---
$combo_items = [];
$search_performed = false;
$bf_message = "";

if (isset($_POST['find_food'])) {
    $budget = (int)$_POST['budget'];
    $stmt = $conn->prepare("SELECT p.*, r.name as restaurant_name, r.rating FROM products p LEFT JOIN restaurants r ON p.restaurant_id = r.id WHERE p.price <= ? ORDER BY p.price DESC");
    $stmt->bind_param("i", $budget);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $search_performed = true;
        while ($row = $result->fetch_assoc()) {
            $combo_items[] = $row;
        }
    } else {
        $bf_message = "😞 No items found under Rs. $budget";
    }
}

// --- 3. FETCH FILTERS DATA ---
$cats_sql = "SELECT * FROM categories";
$cats_result = $conn->query($cats_sql);

$rests_sql = "SELECT * FROM restaurants";
$rests_result = $conn->query($rests_sql);

// --- 4. MAIN PRODUCT QUERY ---
$sql_query = "SELECT p.*, r.name as restaurant_name, r.rating 
              FROM products p 
              LEFT JOIN restaurants r ON p.restaurant_id = r.id 
              WHERE 1=1";

// SEARCH FILTER
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $sql_query .= " AND p.name LIKE '%$search%'";
}

// DISCOUNT FILTER (NEW LOGIC)
if (isset($_GET['on_sale'])) {
    $sql_query .= " AND p.discount_percent > 0";
}

// CATEGORY FILTER
if (isset($_GET['categories']) && !empty($_GET['categories'])) {
    $cats_checked = array_map('intval', $_GET['categories']);
    $cat_string = implode(',', $cats_checked);
    $sql_query .= " AND p.category_id IN ($cat_string)";
}

// RESTAURANT FILTER
if (isset($_GET['restaurants']) && !empty($_GET['restaurants'])) {
    $rests_checked = array_map('intval', $_GET['restaurants']);
    $rest_string = implode(',', $rests_checked);
    $sql_query .= " AND p.restaurant_id IN ($rest_string)";
}

// RATING FILTER
if (isset($_GET['rating']) && !empty($_GET['rating'])) {
    $rating_val = (float)$_GET['rating'];
    $sql_query .= " AND r.rating >= $rating_val"; 
}

$product_result = $conn->query($sql_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Home - Foodies Hub</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root { --primary: #D70F64; --primary-hover: #b00c50; --dark: #2D3436; --light-bg: #F8F9FA; --card-shadow: 0 10px 30px rgba(0,0,0,0.05); --card-hover: 0 15px 35px rgba(0,0,0,0.1); }
        body { font-family: 'Poppins', sans-serif; background-color: var(--light-bg); color: var(--dark); }
        .navbar { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); }
        .navbar-brand { font-size: 1.5rem; color: var(--primary) !important; font-weight: 800; letter-spacing: -0.5px; }
        .btn-panda { background-color: var(--primary); color: white; font-weight: 600; border-radius: 50px; padding: 10px 25px; border: none; transition: 0.3s; box-shadow: 0 4px 15px rgba(215, 15, 100, 0.3); }
        .btn-panda:hover { background-color: var(--primary-hover); color: white; transform: translateY(-2px); box-shadow: 0 8px 20px rgba(215, 15, 100, 0.4); }
        .location-pill { cursor: pointer; background: #f1f1f1; padding: 8px 16px; border-radius: 50px; font-size: 0.85rem; font-weight: 600; transition: 0.2s; }
        .location-pill:hover { background: #e9ecef; }
        .hero-section { background: linear-gradient(135deg, rgba(0,0,0,0.6), rgba(215, 15, 100, 0.4)), url('https://images.unsplash.com/photo-1504674900247-0877df9cc836?q=80&w=1470&auto=format&fit=crop'); background-size: cover; background-position: center; padding: 120px 0 100px; color: white; text-align: center; border-radius: 0 0 50px 50px; margin-bottom: 60px; }
        .hero-title { font-weight: 800; font-size: 3.5rem; margin-bottom: 1rem; text-shadow: 0 2px 10px rgba(0,0,0,0.3); }
        .budget-search-box { background: white; padding: 10px; border-radius: 50px; box-shadow: 0 15px 40px rgba(0,0,0,0.15); display: flex; align-items: center; max-width: 600px; margin: 0 auto; }
        .budget-input { border: none; padding: 15px 25px; font-size: 1rem; border-radius: 50px; flex-grow: 1; outline: none; }
        .fp-card { background: white; border: none; border-radius: 20px; overflow: hidden; transition: all 0.3s ease; box-shadow: var(--card-shadow); height: 100%; position: relative; }
        .fp-card:hover { transform: translateY(-5px); box-shadow: var(--card-hover); }
        .img-wrapper { position: relative; height: 200px; overflow: hidden; cursor: pointer; flex-shrink: 0; }
        .img-wrapper img { width: 100%; height: 100%; object-fit: cover; transition: 0.5s; }
        .fp-card:hover .img-wrapper img { transform: scale(1.05); }
        .badge-rating { position: absolute; bottom: 15px; left: 15px; background: rgba(255,255,255,0.95); padding: 5px 12px; border-radius: 12px; font-size: 0.85rem; font-weight: 700; color: var(--dark); box-shadow: 0 4px 10px rgba(0,0,0,0.1); backdrop-filter: blur(4px); display: flex; align-items: center; gap: 4px; }
        
        .btn-fav { 
            position: absolute; top: 15px; right: 15px; 
            background: white; 
            border: none; border-radius: 50%; 
            width: 40px; height: 40px; 
            display: grid; place-items: center; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.15); 
            transition: all 0.3s ease; z-index: 5; 
        }
        .btn-fav:hover { transform: scale(1.15); box-shadow: 0 6px 15px rgba(0,0,0,0.2); }
        .btn-fav i { font-size: 1.2rem; color: #b2bec3; transition: 0.2s; }
        .btn-fav i.active { color: #D70F64; animation: pop 0.3s ease; }

        .btn-add-custom {
            width: 45px; height: 45px;
            background-color: var(--primary);
            color: white;
            border-radius: 50%;
            border: none;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 10px rgba(215, 15, 100, 0.4);
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .btn-add-custom:hover {
            background-color: var(--primary-hover);
            transform: scale(1.1);
            box-shadow: 0 8px 20px rgba(215, 15, 100, 0.6);
        }
        .btn-add-custom:active { transform: scale(0.95); }
        
        @keyframes pop { 0% { transform: scale(1); } 50% { transform: scale(1.3); } 100% { transform: scale(1); } }

        .sidebar-card { background: white; border-radius: 20px; padding: 30px; box-shadow: var(--card-shadow); position: sticky; top: 100px; }
        .filter-title { font-weight: 800; font-size: 0.8rem; text-transform: uppercase; color: #999; letter-spacing: 1px; margin-bottom: 20px; display: block; }
        .form-check-input:checked { background-color: var(--primary); border-color: var(--primary); }
        .form-switch .form-check-input { width: 2.5em; height: 1.3em; cursor: pointer; }
        .modal-content { border: none; border-radius: 24px; overflow: hidden; }
        .fp-input { width: 100%; padding: 12px 15px; border: 2px solid #eee; border-radius: 12px; outline: none; transition: 0.3s; }
        .fp-input:focus { border-color: var(--primary); }
        .logout-btn:hover { background-color: #dc3545 !important; color: white !important; }
        .logout-btn:hover i { color: white !important; }

        /* --- UPDATED FOOTER STYLES --- */
        .main-footer { background-color: #f8f9fa; padding-top: 80px; padding-bottom: 30px; margin-top: 100px; border-top: 1px solid #e9ecef; }
        .footer-brand { font-size: 1.8rem; font-weight: 800; color: var(--primary); letter-spacing: -1px; text-decoration: none; margin-bottom: 15px; display: inline-block; }
        .footer-heading { font-weight: 700; font-size: 1.1rem; color: var(--dark); margin-bottom: 25px; }
        .footer-links { list-style: none; padding: 0; margin: 0; }
        .footer-links li { margin-bottom: 12px; }
        .footer-link { color: #6c757d; text-decoration: none; font-size: 0.95rem; transition: all 0.3s ease; display: inline-block; }
        .footer-link:hover { color: var(--primary); transform: translateX(5px); }
        .social-links { display: flex; gap: 10px; margin-top: 20px; }
        .social-btn { width: 40px; height: 40px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--dark); box-shadow: 0 4px 10px rgba(0,0,0,0.05); transition: all 0.3s ease; text-decoration: none; }
        .social-btn:hover { background-color: var(--primary); color: white; transform: translateY(-3px); box-shadow: 0 8px 15px rgba(215, 15, 100, 0.3); }
        .app-badge { background-color: #1a1a1a; color: white; padding: 8px 16px; border-radius: 10px; display: flex; align-items: center; gap: 10px; text-decoration: none; transition: all 0.3s ease; margin-bottom: 10px; width: fit-content; border: 1px solid transparent; }
        .app-badge:hover { background-color: #000; border-color: var(--primary); transform: translateY(-2px); color: white; }
        .app-text span { display: block; line-height: 1.2; }
        .app-text .small-text { font-size: 0.65rem; opacity: 0.8; text-transform: uppercase; }
        .app-text .big-text { font-size: 1rem; font-weight: 600; }
        .footer-bottom { margin-top: 60px; padding-top: 30px; border-top: 1px solid #e9ecef; }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-light fixed-top shadow-sm">
      <div class="container">
        <a class="navbar-brand" href="main.php"><i class="bi bi-fire me-1"></i> foodieshub</a>
        
        <div class="d-flex align-items-center gap-3">
            <div class="location-pill d-none d-lg-flex align-items-center gap-2" onclick="getLocation()">
                <i class="bi bi-geo-alt-fill text-danger"></i>
                <span id="userLocation" class="text-truncate" style="max-width: 150px;">Locate Me</span>
            </div>

            <a href="favorites.php" class="btn btn-light rounded-circle shadow-sm" style="width: 45px; height: 45px; display:grid; place-items:center;">
               <i class="bi bi-heart text-dark"></i>
            </a>

            <a href="cart.php" class="btn btn-light rounded-circle shadow-sm position-relative" style="width: 45px; height: 45px; display:grid; place-items:center;">
              <i class="bi bi-bag text-dark"></i>
              <span id="cart-count" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-white" <?php if($cart_count == 0) echo 'style="display:none;"'; ?>>
                  <?php echo $cart_count; ?>
              </span>
            </a>
          
            <?php if($is_logged_in): ?>
            <div class="dropdown">
              <button class="btn btn-light shadow-sm rounded-pill px-3 py-2 d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                <div id="navUserInitial" class="bg-dark text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 25px; height: 25px; font-size: 12px;">
                    <?php echo strtoupper(substr($customer_name, 0, 1)); ?>
                </div>
                <span class="fw-bold small" id="navUserName"><?php echo explode(' ', $customer_name)[0]; ?></span>
              </button>
              
              <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-3 p-2 rounded-4" style="min-width: 220px;">
                <li><a class="dropdown-item p-2 rounded-3" href="#" data-bs-toggle="modal" data-bs-target="#profileModal"><i class="bi bi-person me-2"></i> Profile</a></li>
                <li><a class="dropdown-item p-2 rounded-3" href="favorites.php"><i class="bi bi-heart me-2"></i> Favorites</a></li>
                <?php if ($has_orders): ?>
                    <li><a class="dropdown-item p-2 rounded-3" href="my_orders.php"><i class="bi bi-box-seam me-2"></i> Orders</a></li>
                <?php endif; ?>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item p-2 text-danger rounded-3 logout-btn" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
              </ul>
            </div>
            <?php else: ?>
            <a href="login.php" class="btn btn-panda ms-2">Login</a>
            <?php endif; ?>
        </div>
      </div>
    </nav>

    <div class="modal fade" id="profileModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0 rounded-4">
          <div class="modal-header border-0 pb-0">
             <h5 class="modal-title fw-bold ms-2">My Profile</h5>
             <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body p-4">
            <ul class="nav nav-pills mb-4 nav-fill bg-light rounded-pill p-1" id="pills-tab" role="tablist">
              <li class="nav-item"><button class="nav-link active rounded-pill small fw-bold" id="pills-info-tab" data-bs-toggle="pill" data-bs-target="#pills-info">Personal Info</button></li>
              <li class="nav-item"><button class="nav-link rounded-pill small fw-bold" id="pills-address-tab" data-bs-toggle="pill" data-bs-target="#pills-address">Address Book</button></li>
            </ul>
            <div class="tab-content" id="pills-tabContent">
              <div class="tab-pane fade show active" id="pills-info">
                <form id="profileForm" onsubmit="return false;">
                    <div class="text-center mb-4">
                        <div class="position-relative d-inline-block">
                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center text-secondary border" style="width:80px; height:80px; font-size:30px;">
                                <?php echo substr($customer_name, 0, 1); ?>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3"><label class="small fw-bold text-muted mb-1">Full Name</label><input type="text" id="inputName" class="fp-input" value="<?php echo $customer_name; ?>"></div>
                    <div class="mb-3"><label class="small fw-bold text-muted mb-1">Phone Number</label><input type="number" id="inputPhone" class="fp-input" value="<?php echo $customer_phone; ?>" readonly style="background:#f9f9f9; cursor:not-allowed;"></div>
                    <div class="mb-3"><label class="small fw-bold text-muted mb-1">Email (Optional)</label><input type="email" id="inputEmail" class="fp-input" value="<?php echo $user_email; ?>" placeholder="Add your email"></div>
                    <button type="button" id="btnSaveProfile" class="btn btn-panda w-100 py-2 mt-2 shadow-sm">Save Changes</button>
                </form>
              </div>
              <div class="tab-pane fade" id="pills-address">
                <form id="addressForm" onsubmit="return false;">
                    <div class="alert alert-light border small text-muted mb-3"><i class="bi bi-info-circle me-1"></i> This address will be used for delivery.</div>
                    <div class="mb-3"><label class="small fw-bold text-muted mb-1">Complete Address</label><textarea id="inputAddress" class="fp-input" rows="4" placeholder="House No, Street, Landmark..."></textarea></div>
                    <div class="mb-3"><label class="small fw-bold text-muted mb-1">City</label><input type="text" class="fp-input" value="Lahore" readonly></div>
                    <button type="button" id="btnSaveAddress" class="btn btn-dark w-100 py-2 mt-2 shadow-sm">Update Address</button>
                </form>
              </div>
            </div>
            <div id="updateMsg"></div>
          </div>
        </div>
      </div>
    </div>

    <div class="hero-section">
        <div class="container">
            <h1 class="hero-title">Hungry? We got you.</h1>
            <p class="fs-5 mb-5 opacity-75">Discover the best food & drinks in your area.</p>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <form method="POST" class="budget-search-box">
                         <i class="bi bi-wallet2 fs-5 ms-3 text-secondary"></i>
                        <input type="number" name="budget" class="budget-input" placeholder="What is your budget? (e.g. 500)" required>
                        <button type="submit" name="find_food" class="btn btn-panda py-2 px-4 me-1">Find Deals</button>
                    </form>
                    <div class="mt-3"><a href="menu.php" class="text-white text-decoration-none fw-bold small opacity-75 hover-opacity-100"><i class="bi bi-grid me-1"></i> View Full Menu</a></div>
                </div>
            </div>
            
            <?php if ($search_performed && !empty($combo_items)) { ?>
                <div class="container mt-5">
                    <div class="card shadow-lg border-0 overflow-hidden rounded-4 text-start mx-auto col-lg-8">
                        <div class="card-header bg-success text-white fw-bold py-3 px-4"><i class="bi bi-check-circle-fill me-2"></i> Deals under Rs. <?php echo $budget; ?></div>
                        <div class="list-group list-group-flush">
                            <?php foreach ($combo_items as $item) { ?>
                            <div class="list-group-item p-3 d-flex align-items-center gap-3">
                                <img src="<?php echo !empty($item['image']) ? 'assets/uploads/'.$item['image'] : 'https://placehold.co/100'; ?>" class="rounded-3" width="60" height="60" style="object-fit:cover;">
                                <div class="flex-grow-1">
                                    <h6 class="fw-bold mb-0 text-dark"><?php echo $item['name']; ?></h6>
                                    <small class="text-muted"><i class="bi bi-shop"></i> <?php echo $item['restaurant_name']; ?> <span class="text-warning fw-bold ms-1"><i class="bi bi-star-fill"></i> <?php echo $item['rating']; ?></span></small>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold text-danger mb-1">Rs. <?php echo $item['price']; ?></div>
                                    <button type="button" onclick="addToCart(<?php echo $item['id']; ?>)" class="btn-add-custom" style="width: 35px; height: 35px; font-size: 14px;">
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            <?php } elseif ($bf_message) { ?>
                <div class="alert alert-light mt-4 mx-auto col-lg-6 shadow-sm rounded-pill d-inline-block px-4"><i class="bi bi-emoji-frown me-2"></i> <?php echo $bf_message; ?></div>
            <?php } ?>
        </div>
    </div>

    <div class="container mb-5">
        <div class="row g-5">
            <div class="col-lg-3 d-none d-lg-block">
                <div class="sidebar-card">
                    <form action="main.php" method="GET" id="filterForm">
                        <div class="mb-5">
                            <span class="filter-title"><i class="bi bi-search me-2"></i> Search</span>
                            <div class="position-relative">
                                <input type="text" name="search" class="fp-input ps-5" placeholder="Pizza, Burger..." value="<?php if(isset($_GET['search'])) echo $_GET['search']; ?>">
                                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                            </div>
                        </div>

                        <div class="mb-5">
                            <span class="filter-title"><i class="bi bi-tag-fill me-2"></i> Deals & Offers</span>
                            <div class="form-check form-switch p-0 d-flex justify-content-between align-items-center border rounded-3 p-3 bg-light">
                                <label class="form-check-label small fw-bold text-dark m-0" for="saleFilter">Show Only Sale</label>
                                <input class="form-check-input m-0 shadow-sm" type="checkbox" name="on_sale" value="1" id="saleFilter" <?php if(isset($_GET['on_sale'])) echo "checked"; ?> onchange="this.form.submit()">
                            </div>
                        </div>

                        <div class="mb-5">
                            <span class="filter-title"><i class="bi bi-grid me-2"></i> Categories</span>
                            <div class="d-flex flex-column gap-2" style="max-height: 200px; overflow-y: auto;">
                                <?php 
                                if($cats_result && $cats_result->num_rows > 0) {
                                    while($cat = $cats_result->fetch_assoc()) {
                                        $isChecked = (isset($_GET['categories']) && in_array($cat['id'], $_GET['categories'])) ? 'checked' : '';
                                ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="categories[]" value="<?php echo $cat['id']; ?>" id="cat<?php echo $cat['id']; ?>" <?php echo $isChecked; ?>>
                                    <label class="form-check-label small" for="cat<?php echo $cat['id']; ?>"><?php echo $cat['name']; ?></label>
                                </div>
                                <?php 
                                    } 
                                } else { echo "<small class='text-muted'>No categories found</small>"; }
                                ?>
                            </div>
                        </div>

                        <div class="mb-5">
                            <span class="filter-title"><i class="bi bi-shop me-2"></i> Restaurants</span>
                            <div class="d-flex flex-column gap-2" style="max-height: 200px; overflow-y: auto;">
                                <?php 
                                if($rests_result && $rests_result->num_rows > 0) {
                                    while($rest = $rests_result->fetch_assoc()) {
                                        $isChecked = (isset($_GET['restaurants']) && in_array($rest['id'], $_GET['restaurants'])) ? 'checked' : '';
                                ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="restaurants[]" value="<?php echo $rest['id']; ?>" id="rest<?php echo $rest['id']; ?>" <?php echo $isChecked; ?>>
                                    <label class="form-check-label small" for="rest<?php echo $rest['id']; ?>"><?php echo $rest['name']; ?></label>
                                </div>
                                <?php 
                                    }
                                } else { echo "<small class='text-muted'>No restaurants found</small>"; }
                                ?>
                            </div>
                        </div>

                        <div class="mb-5">
                            <span class="filter-title"><i class="bi bi-star me-2"></i> Rating</span>
                            <div class="d-flex flex-column gap-2">
                                <div class="form-check"><input class="form-check-input" type="radio" name="rating" value="4" id="rate4" <?php if(isset($_GET['rating']) && $_GET['rating'] == '4') echo "checked"; ?>><label class="form-check-label small" for="rate4"><span class="text-warning"><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i></span> 4.0+</label></div>
                                <div class="form-check"><input class="form-check-input" type="radio" name="rating" value="3" id="rate3" <?php if(isset($_GET['rating']) && $_GET['rating'] == '3') echo "checked"; ?>><label class="form-check-label small" for="rate3"><span class="text-warning"><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i></span> 3.0+</label></div>
                                <div class="form-check"><input class="form-check-input" type="radio" name="rating" value="" id="rateAny" <?php if(!isset($_GET['rating']) || $_GET['rating'] == '') echo "checked"; ?>><label class="form-check-label small" for="rateAny">All Ratings</label></div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-dark w-100 rounded-pill fw-bold mb-2">Apply Filters</button>
                        <a href="main.php" class="btn btn-outline-secondary w-100 rounded-pill btn-sm">Clear All</a>
                    </form>
                </div>
            </div>

            <div class="col-lg-9">
                <div class="d-flex justify-content-between align-items-center mb-4 ps-2">
                    <h4 class="fw-bold m-0 text-dark">Delicious Menu</h4>
                    <span class="text-muted small"><?php echo $product_result->num_rows; ?> items found</span>
                </div>

                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">
                    <?php 
                    if ($product_result->num_rows > 0) {
                        while($prod = $product_result->fetch_assoc()) {
                            $img = !empty($prod['image']) ? "assets/uploads/".$prod['image'] : "https://placehold.co/400";
                            $modalID = "modal".$prod['id'];
                            $heartClass = in_array($prod['id'], $fav_products) ? "bi-heart-fill active" : "bi-heart";
                            
                            $rating = isset($prod['rating']) ? $prod['rating'] : 0.0;
                            
                            // --- DYNAMIC DISCOUNT LOGIC ---
                            $price = $prod['price'];
                            $discount = isset($prod['discount_percent']) ? $prod['discount_percent'] : 0;
                            $final_price = $price;
                            $on_sale = false;
                    
                            if ($discount > 5) {
                                $on_sale = true;
                                $saved_amount = ($price * $discount) / 100;
                                $final_price = $price - $saved_amount;
                            }
                    ?>
                    <div class="col">
                        <div class="fp-card h-100 d-flex flex-column position-relative">
                            <button onclick="toggleFavorite(<?php echo $prod['id']; ?>, this)" class="btn-fav shadow-sm"><i class="bi <?php echo $heartClass; ?>"></i></button>
                            <div class="img-wrapper" data-bs-toggle="modal" data-bs-target="#<?php echo $modalID; ?>">
                                <img src="<?php echo $img; ?>">
                                <?php if($prod['is_popular']) { echo '<span class="position-absolute top-0 start-0 m-3 badge bg-danger rounded-pill shadow-sm text-uppercase" style="font-size: 10px; letter-spacing:1px;">Popular</span>'; } ?>
                                
                                <?php if($on_sale) { ?>
                                    <span class="position-absolute bottom-0 end-0 m-3 badge bg-danger text-white fw-bold rounded-pill shadow-sm" style="font-size: 11px; z-index:4;">
                                        <?php echo $discount; ?>% OFF
                                    </span>
                                <?php } ?>

                                <span class="badge-rating"><i class="bi bi-star-fill text-warning"></i> <?php echo $rating; ?></span>
                            </div>
                            <div class="p-3 d-flex flex-column flex-grow-1">
                                <div class="mb-1" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#<?php echo $modalID; ?>">
                                    <div class="d-flex justify-content-between align-items-start"><h6 class="fw-bold mb-1 text-dark text-truncate w-100" style="font-size: 1.05rem;"><?php echo $prod['name']; ?></h6></div>
                                    <p class="text-muted small mb-2 text-truncate"><i class="bi bi-shop me-1"></i> <?php echo $prod['restaurant_name']; ?></p>
                                    <p class="text-muted small text-truncate mb-3" style="font-size: 0.85rem;"><?php echo $prod['description']; ?></p>
                                </div>
                                <div class="mt-auto d-flex justify-content-between align-items-center">
                                    <div class="d-flex flex-column">
                                        <?php if($on_sale) { ?>
                                            <small class="text-decoration-line-through text-muted" style="font-size: 12px;">Rs. <?php echo $price; ?></small>
                                            <span class="fw-bold text-danger fs-5">Rs. <?php echo (int)$final_price; ?></span>
                                        <?php } else { ?>
                                            <span class="fw-bold text-dark fs-5">Rs. <?php echo $price; ?></span>
                                        <?php } ?>
                                    </div>
                                    <button type="button" onclick="addToCart(<?php echo $prod['id']; ?>)" class="btn-add-custom">
                                        <i class="bi bi-plus-lg fs-4 fw-bold"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="<?php echo $modalID; ?>" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-body p-0">
                                    <div class="position-relative">
                                        <img src="<?php echo $img; ?>" class="w-100" style="height: 300px; object-fit:cover;">
                                        <button type="button" class="btn-close position-absolute top-0 end-0 m-3 bg-white p-2 rounded-circle shadow opacity-100" data-bs-dismiss="modal"></button>
                                        <?php if($on_sale) { ?>
                                            <span class="position-absolute bottom-0 end-0 m-3 badge bg-danger text-white fs-6 shadow">FLAT <?php echo $discount; ?>% OFF</span>
                                        <?php } ?>
                                    </div>
                                    <div class="p-4">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h3 class="fw-bold m-0"><?php echo $prod['name']; ?></h3>
                                            <div>
                                                <?php if($on_sale) { ?>
                                                    <span class="text-muted text-decoration-line-through me-2">Rs. <?php echo $price; ?></span>
                                                    <h3 class="fw-bold text-danger m-0 d-inline">Rs. <?php echo (int)$final_price; ?></h3>
                                                <?php } else { ?>
                                                    <h3 class="fw-bold text-primary m-0">Rs. <?php echo $price; ?></h3>
                                                <?php } ?>
                                            </div>
                                        </div>
                                        <p class="text-muted mb-4 fs-6"><i class="bi bi-shop me-2"></i><?php echo $prod['restaurant_name']; ?></p>
                                        <p class="text-secondary mb-4"><?php echo $prod['description']; ?></p>
                                        <div class="d-grid">
                                            <button type="button" onclick="addToCart(<?php echo $prod['id']; ?>)" class="btn btn-panda py-3 fs-5">Add to Cart</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php 
                        } 
                    } else { echo "<div class='col-12 text-center py-5'><h4 class='text-muted opacity-50 fw-bold'>No products found.</h4></div>"; }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <footer class="main-footer">
        <div class="container">
            <div class="row g-4">
                
                <div class="col-lg-4 col-md-6">
                    <a href="main.php" class="footer-brand">
                        <i class="bi bi-fire me-1"></i> foodieshub.
                    </a>
                    <p class="text-muted pe-lg-4" style="line-height: 1.8;">
                        Craving something delicious? We deliver the best food from your favorite local restaurants straight to your doorstep. Fresh, fast, and fierce.
                    </p>
                    <div class="social-links">
                        <a href="#" class="social-btn"><i class="bi bi-facebook fs-5"></i></a>
                        <a href="#" class="social-btn"><i class="bi bi-instagram fs-5"></i></a>
                        <a href="#" class="social-btn"><i class="bi bi-twitter-x fs-5"></i></a>
                        <a href="#" class="social-btn"><i class="bi bi-linkedin fs-5"></i></a>
                    </div>
                </div>

                <div class="col-lg-2 col-md-6 col-6">
                    <h5 class="footer-heading">Company</h5>
                    <ul class="footer-links">
                        <li><a href="#" class="footer-link">About Us</a></li>
                        <li><a href="#" class="footer-link">Our Team</a></li>
                        <li><a href="#" class="footer-link">Careers</a></li>
                        <li><a href="#" class="footer-link">Blog</a></li>
                    </ul>
                </div>

                <div class="col-lg-2 col-md-6 col-6">
                    <h5 class="footer-heading">Support</h5>
                    <ul class="footer-links">
                        <li><a href="#" class="footer-link">Help Center</a></li>
                        <li><a href="#" class="footer-link">Terms of Service</a></li>
                        <li><a href="#" class="footer-link">Privacy Policy</a></li>
                        <li><a href="#" class="footer-link">Contact Us</a></li>
                    </ul>
                </div>

                <div class="col-lg-4 col-md-6">
                    <h5 class="footer-heading">Get the App</h5>
                    <p class="text-muted small mb-3">Order on the go. Download our mobile app now.</p>
                    
                    <div class="d-flex flex-column flex-sm-row gap-2">
                        <a href="#" class="app-badge">
                            <i class="bi bi-apple fs-2"></i>
                            <div class="app-text">
                                <span class="small-text">Download on the</span>
                                <span class="big-text">App Store</span>
                            </div>
                        </a>
                        
                        <a href="#" class="app-badge">
                            <i class="bi bi-google-play fs-2"></i>
                            <div class="app-text">
                                <span class="small-text">Get it on</span>
                                <span class="big-text">Google Play</span>
                            </div>
                        </a>
                    </div>
                </div>

            </div>

            <div class="footer-bottom">
                <div class="row align-items-center">
                    <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                        <p class="text-muted small mb-0">&copy; <?php echo date("Y"); ?> Foodies Hub. All rights reserved.</p>
                    </div>
                    <div class="col-md-6 text-center text-md-end">
                        <div class="d-inline-flex align-items-center gap-3 grayscale-img opacity-75">
                            <i class="bi bi-credit-card-2-front fs-4 text-secondary" title="Visa"></i>
                            <i class="bi bi-paypal fs-4 text-secondary" title="Paypal"></i>
                            <i class="bi bi-cash-coin fs-4 text-secondary" title="Cash on Delivery"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function addToCart(pid) {
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "cart_action.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (this.readyState === 4 && this.status === 200) {
                    var response = this.responseText.trim();
                    if(response === "login_required") { window.location.href = "login.php"; } 
                    else {
                        var badge = document.getElementById('cart-count');
                        badge.innerText = response;
                        badge.style.display = 'block'; 
                    }
                }
            };
            xhr.send("product_id=" + pid);
        }

        const saveBtn = document.getElementById('btnSaveProfile');
        if (saveBtn) {
            saveBtn.addEventListener('click', function() {
                const nameInput = document.getElementById('inputName').value;
                const emailInput = document.getElementById('inputEmail').value;
                const msgDiv = document.getElementById('updateMsg');
                if(nameInput.trim() === "") { alert("Name cannot be empty"); return; }
                let originalText = saveBtn.innerText;
                saveBtn.innerText = "Saving...";
                saveBtn.disabled = true;
                const formData = new FormData();
                formData.append('action', 'update_info'); 
                formData.append('name', nameInput);
                formData.append('email', emailInput);
                fetch('update_profile.php', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        msgDiv.innerHTML = `<div class="alert alert-success mt-2 py-2 small shadow-sm border-0"><i class="bi bi-check-circle me-1"></i> ${data.message}</div>`;
                        let firstName = nameInput.trim().split(' ')[0];
                        document.getElementById('navUserName').innerText = firstName;
                        let initial = nameInput.trim().charAt(0).toUpperCase();
                        document.getElementById('navUserInitial').innerText = initial;
                        setTimeout(() => {
                            msgDiv.innerHTML = '';
                            saveBtn.innerText = "Saved";
                            saveBtn.disabled = false;
                            setTimeout(() => { saveBtn.innerText = originalText; }, 1000);
                        }, 2000);
                    } else {
                        msgDiv.innerHTML = `<div class="alert alert-danger mt-2 py-2 small">${data.message}</div>`;
                        saveBtn.innerText = originalText;
                        saveBtn.disabled = false;
                    }
                })
                .catch(error => { console.error('Error:', error); saveBtn.innerText = originalText; saveBtn.disabled = false; });
            });
        }

        const addrBtn = document.getElementById('btnSaveAddress');
        if (addrBtn) {
            addrBtn.addEventListener('click', function() {
                const address = document.getElementById('inputAddress').value;
                const msgDiv = document.getElementById('updateMsg');
                if(address.trim() === "") { alert("Address cannot be empty"); return; }
                let originalText = addrBtn.innerText;
                addrBtn.innerText = "Updating...";
                addrBtn.disabled = true;
                const formData = new FormData();
                formData.append('action', 'update_address'); 
                formData.append('address', address);
                fetch('update_profile.php', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        msgDiv.innerHTML = `<div class="alert alert-success mt-2 py-2 small shadow-sm border-0"><i class="bi bi-geo-alt me-1"></i> ${data.message}</div>`;
                        setTimeout(() => {
                            msgDiv.innerHTML = '';
                            addrBtn.innerText = "Updated";
                            addrBtn.disabled = false;
                            setTimeout(() => { addrBtn.innerText = originalText; }, 1000);
                        }, 2000);
                    } else {
                        msgDiv.innerHTML = `<div class="alert alert-danger mt-2 py-2 small">${data.message}</div>`;
                        addrBtn.innerText = originalText;
                        addrBtn.disabled = false;
                    }
                })
                .catch(error => { console.error(error); addrBtn.innerText = originalText; addrBtn.disabled = false; });
            });
        }

        window.onload = function() { getLocation(); };
        function getLocation() {
            const locText = document.getElementById("userLocation");
            if (navigator.geolocation) { navigator.geolocation.getCurrentPosition(showPosition, showError); } else { locText.innerHTML = "Location N/A"; }
        }
        function showPosition(position) {
            const lat = position.coords.latitude;
            const lon = position.coords.longitude;
            const locText = document.getElementById("userLocation");
            locText.innerHTML = "Fetching...";
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}`)
                .then(response => response.json())
                .then(data => { if(data.address) { let city = data.address.city || ""; let road = data.address.road || ""; locText.innerHTML = (road && city) ? road + ", " + city : data.display_name.split(',')[0]; } else { locText.innerHTML = "Current Location"; } })
                .catch(error => { locText.innerHTML = "Current Location"; });
        }
        function showError(error) { document.getElementById("userLocation").innerHTML = "Locate Me"; }

        function toggleFavorite(pid, btn) {
            const icon = btn.querySelector('i');
            const formData = new FormData();
            formData.append('product_id', pid);
            fetch('favorite_action.php', { method: 'POST', body: formData }).then(response => response.json()).then(data => {
                if(data.status === 'login_required') { window.location.href = 'login.php'; } 
                else if(data.status === 'added') { icon.classList.remove('bi-heart'); icon.classList.add('bi-heart-fill', 'active'); } 
                else if(data.status === 'removed') { icon.classList.remove('bi-heart-fill', 'active'); icon.classList.add('bi-heart'); }
            });
        }
    </script>
</body>
</html>