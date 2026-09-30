<?php
session_start();
include 'db.php';

// Check if user is logged in
$user_id = isset($_SESSION['customer_id']) ? $_SESSION['customer_id'] : null;

// ==========================================
// 1. ADD TO CART (Background AJAX Call)
// ==========================================
// Jab aap Menu ya Home page se "Add" ya "+" dabate hain
if (isset($_POST['product_id']) && !isset($_POST['update_qty'])) {
    
    // Agar login nahi hai to JS ko batao
    if (!$user_id) { echo "login_required"; exit(); }

    $p_id = $_POST['product_id'];
    $qty = 1; 

    // A. Session Update (Taake UI foran update ho)
    if (isset($_SESSION['cart'][$p_id])) {
        $_SESSION['cart'][$p_id]++;
    } else {
        $_SESSION['cart'][$p_id] = 1;
    }

    // B. Database Update (Permanent Storage)
    $check = $conn->query("SELECT * FROM cart WHERE customer_id = '$user_id' AND product_id = '$p_id'");
    if ($check->num_rows > 0) {
        $conn->query("UPDATE cart SET quantity = quantity + 1 WHERE customer_id = '$user_id' AND product_id = '$p_id'");
    } else {
        $conn->query("INSERT INTO cart (customer_id, product_id, quantity) VALUES ('$user_id', '$p_id', '$qty')");
    }

    // C. Return Total Count (Jo Badge par show hoga)
    echo array_sum($_SESSION['cart']);
    exit();
}

// ==========================================
// 2. UPDATE QUANTITY (Form Submit)
// ==========================================
// Jab aap Cart Page par number change karte hain
if (isset($_POST['update_qty'])) {
    $p_id = $_POST['product_id'];
    $qty = (int)$_POST['quantity'];

    if ($qty < 1) $qty = 1; // 1 se kam na ho

    // Update Session
    $_SESSION['cart'][$p_id] = $qty;

    // Update Database
    if ($user_id) {
        $conn->query("UPDATE cart SET quantity = '$qty' WHERE customer_id = '$user_id' AND product_id = '$p_id'");
    }

    // Wapis Cart Page par bhej do (Refresh)
    header("Location: cart.php");
    exit();
}

// ==========================================
// 3. REMOVE ITEM (Link Click)
// ==========================================
// Jab aap Cart Page par Delete (Trash) icon dabate hain
if (isset($_GET['remove'])) {
    $p_id = $_GET['remove'];

    // Remove from Session
    if(isset($_SESSION['cart'][$p_id])) {
        unset($_SESSION['cart'][$p_id]);
    }

    // Remove from Database
    if ($user_id) {
        $conn->query("DELETE FROM cart WHERE customer_id = '$user_id' AND product_id = '$p_id'");
    }

    // Wapis Cart Page par bhej do
    header("Location: cart.php");
    exit();
}
?>