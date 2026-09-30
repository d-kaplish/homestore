<?php
session_start();
include 'db.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$prod_id = intval($_GET['id']);
$variant_id = isset($_GET['variant']) ? intval($_GET['variant']) : 0;

// Check if product exists
$product_check = mysqli_query($conn, "SELECT stock FROM products WHERE prod_id = $prod_id");
if(mysqli_num_rows($product_check) == 0) {
    header("Location: index.php");
    exit();
}
$product = mysqli_fetch_assoc($product_check);

// Check if variant exists and has stock
if($variant_id > 0) {
    $variant_check = mysqli_query($conn, "SELECT stock, size, color, additional_price FROM product_variants WHERE variant_id = $variant_id AND prod_id = $prod_id");
    
    if(mysqli_num_rows($variant_check) == 0) {
        header("Location: display.php?id=$prod_id&error=invalid_variant");
        exit();
    }
    
    $variant = mysqli_fetch_assoc($variant_check);
    
    if($variant['stock'] <= 0) {
        header("Location: display.php?id=$prod_id&error=outofstock");
        exit();
    }
} else {
    // Check product stock for standard version
    if($product['stock'] <= 0) {
        header("Location: display.php?id=$prod_id&error=outofstock");
        exit();
    }
}

// Check if product already in cart with same variant
$check = mysqli_query($conn, "SELECT * FROM cart WHERE userid=$user_id AND prod_id=$prod_id AND variant_id=$variant_id");

if(mysqli_num_rows($check) > 0) {
    // Get current quantity and check against stock
    $cart_item = mysqli_fetch_assoc($check);
    $new_quantity = $cart_item['quantity'] + 1;
    
    if($variant_id > 0) {
        $stock_check = mysqli_query($conn, "SELECT stock FROM product_variants WHERE variant_id = $variant_id");
        $stock_data = mysqli_fetch_assoc($stock_check);
        if($new_quantity > $stock_data['stock']) {
            header("Location: display.php?id=$prod_id&error=stock_limit");
            exit();
        }
    } else {
        if($new_quantity > $product['stock']) {
            header("Location: display.php?id=$prod_id&error=stock_limit");
            exit();
        }
    }
    
    // Increase quantity
    mysqli_query($conn, "UPDATE cart SET quantity = quantity + 1 WHERE userid=$user_id AND prod_id=$prod_id AND variant_id=$variant_id");
} else {
    // Insert new item
    $insert = "INSERT INTO cart(userid, prod_id, variant_id, quantity) VALUES($user_id, $prod_id, $variant_id, 1)";
    mysqli_query($conn, $insert);
}

// Redirect back to previous page or cart
if(isset($_SERVER['HTTP_REFERER'])) {
    header("Location: " . $_SERVER['HTTP_REFERER']);
} else {
    header("Location: cart.php");
}
exit();
?>  