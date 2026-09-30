<?php
session_start();
include 'db.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if(isset($_POST['cart_id']) && isset($_POST['quantity'])) {
    $cart_id = intval($_POST['cart_id']);
    $quantity = intval($_POST['quantity']);
    
    if($quantity < 1) {
        $quantity = 1;
    }
    
    // Get cart item details
    $cart_query = "SELECT c.*, p.stock as product_stock, pv.stock as variant_stock 
                   FROM cart c 
                   JOIN products p ON c.prod_id = p.prod_id 
                   LEFT JOIN product_variants pv ON c.variant_id = pv.variant_id 
                   WHERE c.cart_id = $cart_id AND c.userid = $user_id";
    $cart_result = mysqli_query($conn, $cart_query);
    
    if(mysqli_num_rows($cart_result) > 0) {
        $cart_item = mysqli_fetch_assoc($cart_result);
        
        // Check stock availability
        if($cart_item['variant_id'] > 0) {
            if($quantity > $cart_item['variant_stock']) {
                $quantity = $cart_item['variant_stock'];
                $_SESSION['cart_error'] = "Only {$cart_item['variant_stock']} units available for this variant.";
            }
        } else {
            if($quantity > $cart_item['product_stock']) {
                $quantity = $cart_item['product_stock'];
                $_SESSION['cart_error'] = "Only {$cart_item['product_stock']} units available.";
            }
        }
        
        // Update quantity
        $update = "UPDATE cart SET quantity = $quantity WHERE cart_id = $cart_id AND userid = $user_id";
        mysqli_query($conn, $update);
    }
}

header("Location: cart.php");
exit();
?>