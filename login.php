<?php
session_start();
include 'db.php';

// Agar user pehle se login hai to redirect karen
if (isset($_SESSION['customer_id'])) {
    header("Location: main.php");
    exit();
}

$error_msg = "";

if (isset($_POST['login_btn'])) {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);

    if (!empty($name) && !empty($phone)) {
        
        // 1. Check if user exists
        $stmt = $conn->prepare("SELECT * FROM customers WHERE phone = ?");
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // --- LOGIN (Existing User) ---
            $row = $result->fetch_assoc();
            $user_id = $row['id'];
            $user_name = $row['name'];
        } else {
            // --- REGISTER (New User) ---
            $stmt = $conn->prepare("INSERT INTO customers (name, phone) VALUES (?, ?)");
            $stmt->bind_param("ss", $name, $phone);
            $stmt->execute();
            $user_id = $conn->insert_id;
            $user_name = $name;
        }

        if (isset($user_id)) {
            // Session Set Karen
            $_SESSION['customer_id'] = $user_id;
            $_SESSION['customer_name'] = $user_name;
            $_SESSION['customer_phone'] = $phone;

            // ====================================================
            // CRITICAL FIX: SESSION CART OVERRIDES DATABASE
            // ====================================================
            if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
                foreach ($_SESSION['cart'] as $p_id => $qty) {
                    
                    // Check karen agr ye item pehle se DB cart me hai
                    $check_cart = $conn->prepare("SELECT quantity FROM cart WHERE customer_id = ? AND product_id = ?");
                    $check_cart->bind_param("ii", $user_id, $p_id);
                    $check_cart->execute();
                    $res = $check_cart->get_result();

                    if ($res->num_rows > 0) {
                        $update_stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE customer_id = ? AND product_id = ?");
                        $update_stmt->bind_param("iii", $qty, $user_id, $p_id); 
                        $update_stmt->execute();
                    } else {
                        $insert_stmt = $conn->prepare("INSERT INTO cart (customer_id, product_id, quantity) VALUES (?, ?, ?)");
                        $insert_stmt->bind_param("iii", $user_id, $p_id, $qty);
                        $insert_stmt->execute();
                    }
                }
                unset($_SESSION['cart']);
            }
            // ====================================================

            // Final Step: Database se wapis latest cart load kar len
            $_SESSION['cart'] = [];
            $final_cart = $conn->query("SELECT * FROM cart WHERE customer_id = '$user_id'");
            while($fc = $final_cart->fetch_assoc()){
                $_SESSION['cart'][$fc['product_id']] = $fc['quantity'];
            }

            header("Location: main.php");
            exit();
        }

    } else {
        $error_msg = "Please fill all fields!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Foodies Hub</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        :root {
            --brand-color: #D70F64;
            --brand-hover: #b00c50;
        }
        
        body, html { 
            height: 100%; 
            margin: 0;
            font-family: 'Poppins', sans-serif; 
            background-color: #fff;
        }

        /* --- LEFT SIDE (FORM) --- */
        .left-pane {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            background: white;
            height: 100%;
        }
        
        .form-wrapper {
            width: 100%;
            max-width: 400px;
        }

        .brand-logo {
            color: var(--brand-color);
            font-weight: 800;
            font-size: 2.2rem;
            margin-bottom: 0.5rem;
            display: inline-block;
        }

        .form-control { 
            background-color: #f8f9fa; 
            border: 2px solid #f0f0f0; 
            padding: 14px; 
            font-size: 15px; 
            border-radius: 12px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            box-shadow: 0 0 0 4px rgba(215, 15, 100, 0.1);
            border-color: var(--brand-color);
            background-color: #fff;
        }
        
        .input-group-text {
            background: #f8f9fa;
            border: 2px solid #f0f0f0;
            border-right: none;
            color: var(--brand-color);
            border-radius: 12px 0 0 12px;
            padding-left: 20px;
        }
        
        .form-control-icon {
            border-left: none;
            border-radius: 0 12px 12px 0;
        }

        .btn-brand { 
            background-color: var(--brand-color); 
            color: white; 
            font-weight: 600; 
            width: 100%; 
            padding: 15px; 
            border-radius: 12px; 
            border: none; 
            font-size: 16px;
            transition: 0.3s;
            box-shadow: 0 10px 20px rgba(215, 15, 100, 0.2);
        }
        
        .btn-brand:hover { 
            background-color: var(--brand-hover); 
            transform: translateY(-2px);
            box-shadow: 0 15px 25px rgba(215, 15, 100, 0.3);
        }

        /* --- RIGHT SIDE (VISUALS) --- */
        .right-pane {
            background: linear-gradient(135deg, #D70F64 0%, #FF4B8B 100%);
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 60px;
            position: relative;
            overflow: hidden;
            height: 100%;
        }

        .right-pane::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background-image: url('https://img.freepik.com/free-vector/seamless-pattern-with-fast-food_23-2147608249.jpg?w=740&t=st=1680000000~exp=1680000600~hmac=xyz');
            opacity: 0.05;
            background-size: 300px;
        }

        .hero-img {
            max-width: 80%;
            margin: 0 auto 40px auto;
            animation: float 6s ease-in-out infinite;
            filter: drop-shadow(0 20px 30px rgba(0,0,0,0.2));
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }

        .display-text {
            font-size: 3rem;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 20px;
            z-index: 2;
        }

        .sub-text {
            font-size: 1.1rem;
            opacity: 0.9;
            font-weight: 300;
            max-width: 80%;
            z-index: 2;
        }
    </style>
</head>
<body>

    <div class="container-fluid h-100 p-0">
        <div class="row g-0 h-100">
            
            <div class="col-md-6 left-pane">
                <div class="form-wrapper">
                    <div class="mb-5">
                        <span class="brand-logo"><i class="bi bi-fire"></i> foodieshub</span>
                        <h3 class="fw-bold mt-2 text-dark">Welcome Back! 👋</h3>
                        <p class="text-muted">Enter your details to access your account or create a new one instantly.</p>
                    </div>

                    <?php if(!empty($error_msg)): ?>
                        <div class="alert alert-danger border-0 shadow-sm mb-4">
                            <i class="bi bi-exclamation-circle-fill me-2"></i> <?php echo $error_msg; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-uppercase text-muted" style="font-size: 0.75rem; letter-spacing: 1px;">Full Name</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" name="name" class="form-control form-control-icon" placeholder="e.g. John Doe" required>
                            </div>
                        </div>

                        <div class="mb-5">
                            <label class="form-label small fw-bold text-uppercase text-muted" style="font-size: 0.75rem; letter-spacing: 1px;">Phone Number</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-phone"></i></span>
                                <input type="number" name="phone" class="form-control form-control-icon" placeholder="03XXXXXXXXX" required>
                            </div>
                        </div>

                        <button type="submit" name="login_btn" class="btn btn-brand">
                            Login / Register <i class="bi bi-arrow-right ms-2"></i>
                        </button>

                        <p class="text-center mt-4 text-muted small">
                            By continuing, you accept our <a href="#" class="text-dark fw-bold text-decoration-none">Terms</a> & <a href="#" class="text-dark fw-bold text-decoration-none">Privacy Policy</a>.
                        </p>
                    </form>
                </div>
            </div>

            <div class="col-md-6 d-none d-md-flex right-pane">
                <img src="https://cdn.pixabay.com/photo/2022/05/25/22/01/burger-7221644_1280.png" alt="Delicious Food" class="hero-img">
                
                <div class="px-4">
                    <h1 class="display-text">Craving Something<br>Delicious?</h1>
                    <p class="sub-text">Order from the best local restaurants with easy, on-demand delivery. Good food is just a click away.</p>
                </div>
            </div>

        </div>
    </div>

</body>
</html>