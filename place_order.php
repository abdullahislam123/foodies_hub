<?php
session_start();
include 'db.php';

// Check Logic
if (isset($_POST['place_order'])) {
    
    // 1. Check Empty Cart
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        header("Location: main.php");
        exit();
    }

    $cid = $_SESSION['customer_id'];
    $r_name = mysqli_real_escape_string($conn, $_POST['receiver_name']);
    $r_phone = mysqli_real_escape_string($conn, $_POST['receiver_phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $instr = isset($_POST['instructions']) ? mysqli_real_escape_string($conn, $_POST['instructions']) : ''; 
    
    // 2. Calculate Subtotal & Get Restaurant ID
    $subtotal = 0;
    $ids = implode(',', array_keys($_SESSION['cart']));
    
    // FETCH PRODUCTS
    $res = $conn->query("SELECT * FROM products WHERE id IN ($ids)");
    
    // --- NEW LOGIC: Get Restaurant ID ---
    $restaurant_id = 0; 
    // We will pick the restaurant_id from the first item found.
    // (Assuming all items in cart belong to one restaurant, which is standard for food apps)
    
    $products_data = []; // Save data to avoid re-querying
    
    while($row = $res->fetch_assoc()) {
        if($restaurant_id == 0) {
            $restaurant_id = $row['restaurant_id']; // Capture the ID
        }
        $subtotal += $row['price'] * $_SESSION['cart'][$row['id']];
        $products_data[] = $row; // Save row for Step 5
    }
    
    // 3. Add Delivery Fee
    $delivery_fee = 99;
    $grand_total = $subtotal + $delivery_fee;

    // 4. Create Order (NOW INCLUDES restaurant_id)
    $sql = "INSERT INTO orders (customer_id, restaurant_id, receiver_name, receiver_phone, total_amount, status, address, instructions) 
            VALUES ('$cid', '$restaurant_id', '$r_name', '$r_phone', '$grand_total', 'Pending', '$address', '$instr')";
    
    if ($conn->query($sql)) {
        $order_id = $conn->insert_id; // Get new Order ID

        // 5. Insert Order Items
        foreach($products_data as $row) {
            $pid = $row['id'];
            $qty = $_SESSION['cart'][$pid];
            $price = $row['price'];
            
            $conn->query("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES ('$order_id', '$pid', '$qty', '$price')");
        }

        // 6. Clear Cart
        unset($_SESSION['cart']); 
        $conn->query("DELETE FROM cart WHERE customer_id='$cid'");

        header("Location: order_success.php");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
} else {
    header("Location: main.php");
    exit();
}
?>