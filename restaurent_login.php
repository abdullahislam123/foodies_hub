<?php
session_start();
include 'db.php';

if(isset($_SESSION['rest_id'])){
    header("Location: restaurent_dashboard.php");
    exit();
}

if(isset($_POST['login_btn'])){
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Simple check (Security note: Use password_hash in production)
    $sql = "SELECT * FROM restaurants WHERE username='$username' AND password='$password'";
    $result = $conn->query($sql);

    if($result->num_rows > 0){
        $row = $result->fetch_assoc();
        $_SESSION['rest_id'] = $row['id'];
        $_SESSION['rest_name'] = $row['name'];
        header("Location: restaurent_dashboard.php");
        exit();
    } else {
        $error = "Invalid Username or Password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Partner Login | FoodiesHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { 
            font-family: 'Poppins', sans-serif; 
            height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center;
            background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('https://images.unsplash.com/photo-1555396273-367ea4eb4db5?q=80&w=1974&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
        }

        .login-card { 
            background: rgba(255, 255, 255, 0.95); 
            padding: 40px 35px; 
            border-radius: 20px; 
            width: 100%; 
            max-width: 400px; 
            box-shadow: 0 15px 35px rgba(0,0,0,0.2); 
            backdrop-filter: blur(10px);
        }

        .brand-logo {
            font-size: 2rem;
            font-weight: 800;
            color: #D70F64;
            text-align: center;
            display: block;
            margin-bottom: 5px;
            letter-spacing: -1px;
        }

        .subtitle {
            text-align: center;
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 30px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }

        .form-control {
            border-radius: 10px;
            padding: 12px;
            border: 1px solid #ddd;
            font-size: 0.95rem;
        }

        .form-control:focus {
            box-shadow: none;
            border-color: #D70F64;
        }

        .input-group-text {
            background: white;
            border: 1px solid #ddd;
            border-right: none;
            border-radius: 10px 0 0 10px;
            color: #D70F64;
        }
        
        .form-control-icon {
            border-left: none;
            border-radius: 0 10px 10px 0;
        }

        .btn-brand {
            background-color: #D70F64;
            color: white;
            font-weight: 600;
            padding: 12px;
            border-radius: 10px;
            width: 100%;
            border: none;
            transition: 0.3s;
            margin-top: 10px;
        }

        .btn-brand:hover {
            background-color: #b00c50;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(215, 15, 100, 0.3);
        }

        .footer-link {
            text-align: center;
            display: block;
            margin-top: 20px;
            font-size: 0.85rem;
            color: #888;
            text-decoration: none;
        }
        .footer-link:hover { color: #D70F64; }
    </style>
</head>
<body>

    <div class="login-card">
        <span class="brand-logo"><i class="bi bi-fire"></i> foodieshub</span>
        <div class="subtitle">Partner Portal</div>

        <?php if(isset($error)): ?>
            <div class="alert alert-danger py-2 text-center small mb-4">
                <i class="bi bi-exclamation-circle me-1"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label small text-muted fw-bold">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                    <input type="text" name="username" class="form-control form-control-icon" placeholder="Enter ID" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label small text-muted fw-bold">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                    <input type="password" name="password" class="form-control form-control-icon" placeholder="Enter Password" required>
                </div>
            </div>

            <button type="submit" name="login_btn" class="btn btn-brand">
                Login to Dashboard <i class="bi bi-arrow-right-short"></i>
            </button>
        </form>
    </div>

</body>
</html>