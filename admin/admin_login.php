<?php
session_start();
include '../db.php';

// Agar pehle se login hai to Dashboard bhejo
if (isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_dashboard.php");
    exit();
}

// Login Logic
if (isset($_POST['login_btn'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Database check (Table: users)
    // Note: Production main SQL Injection se bachne k liye Prepared Statements use karein
    $sql = "SELECT * FROM users WHERE username='$username' AND password='$password' AND role='admin'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $_SESSION['admin_logged_in'] = true;
        header("Location: admin_dashboard.php");
    } else {
        $error = "Invaild Credentials";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Login - Foodies Hub</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: url('https://images.unsplash.com/photo-1495195134817-aeb325a55b65?q=80&w=1776&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        /* Dark Overlay */
        body::before {
            content: "";
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: -1;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
            text-align: center;
            animation: fadeIn 0.8s ease-in-out;
        }

        .brand-logo {
            font-size: 1.8rem;
            font-weight: 800;
            color: #333;
            margin-bottom: 5px;
            letter-spacing: -1px;
        }
        .brand-logo span { color: #D70F64; }

        .form-control {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            padding: 12px;
            padding-left: 45px; /* Space for icon */
            font-size: 0.95rem;
            border-radius: 8px;
        }
        .form-control:focus {
            box-shadow: none;
            border-color: #D70F64;
            background-color: white;
        }

        .input-group { position: relative; margin-bottom: 20px; }
        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #aaa;
            z-index: 10;
        }

        .btn-login {
            background: #D70F64;
            color: white;
            font-weight: 700;
            padding: 12px;
            border-radius: 8px;
            border: none;
            width: 100%;
            transition: 0.3s;
        }
        .btn-login:hover {
            background: #b00c50;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(215, 15, 100, 0.3);
        }

        .alert-error {
            background-color: #ffe6e6;
            color: #d63031;
            font-size: 0.9rem;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ffcccc;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="mb-4">
            <div class="brand-logo">FOODIES<span>HUB</span></div>
            <p class="text-muted small">Admin Control Panel</p>
        </div>

        <?php if(isset($error)){ echo "<div class='alert-error'><i class='bi bi-exclamation-circle-fill me-2'></i> $error</div>"; } ?>

        <form method="POST">
            <div class="input-group">
                <i class="bi bi-person input-icon"></i>
                <input type="text" name="username" class="form-control" placeholder="Username" required>
            </div>
            
            <div class="input-group">
                <i class="bi bi-lock input-icon"></i>
                <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>

            <button type="submit" name="login_btn" class="btn btn-login">SECURE LOGIN</button>
        </form>

        <div class="mt-4 pt-3 border-top">
            <a href="../main.php" class="text-decoration-none text-muted small hover-link">
                <i class="bi bi-arrow-left me-1"></i> Back to Website
            </a>
        </div>
    </div>

</body>
</html>