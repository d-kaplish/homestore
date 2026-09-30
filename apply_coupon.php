<?php
session_start();
include 'db.php';

if(!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

if(isset($_POST['coupon_code'])) {
    $coupon_code = strtoupper(mysqli_real_escape_string($conn, $_POST['coupon_code']));
    $user_id = $_SESSION['user_id'];
    
    // Check if coupon exists and is valid
    $query = "SELECT * FROM coupons WHERE code = ? AND valid_until >= CURDATE() 
              AND used_count < usage_limit";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $coupon_code);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if($coupon = mysqli_fetch_assoc($result)) {
        // Get cart total
        $cart_total = 0;
        $cart_query = "SELECT SUM(p.price * c.quantity) as total 
                       FROM cart c 
                       JOIN products p ON c.prod_id = p.prod_id 
                       WHERE c.userid = $user_id";
        $cart_result = mysqli_query($conn, $cart_query);
        $cart_data = mysqli_fetch_assoc($cart_result);
        $cart_total = $cart_data['total'];
        
        if($cart_total >= $coupon['min_order']) {
            // Calculate discount
            if($coupon['discount_type'] == 'percentage') {
                $discount = ($cart_total * $coupon['discount_value']) / 100;
            } else {
                $discount = $coupon['discount_value'];
            }
            
            // Store coupon in session
            $_SESSION['coupon'] = [
                'code' => $coupon['code'],
                'discount' => $discount,
                'coupon_id' => $coupon['coupon_id']
            ];
            
            echo json_encode([
                'success' => true, 
                'message' => 'Coupon applied successfully!',
                'discount' => $discount,
                'new_total' => $cart_total - $discount
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Minimum order value of ₹' . number_format($coupon['min_order'], 2) . ' required'
            ]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid or expired coupon code']);
    }
}
?>