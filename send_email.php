<?php
function sendOrderConfirmation($email, $name, $order_id, $total_amount) {
    $subject = "Order Confirmation - Order #$order_id";
    
    $message = "
    <html>
    <head>
        <title>Order Confirmation</title>
    </head>
    <body>
        <h2>Thank you for your order, $name!</h2>
        <p>Your order has been placed successfully.</p>
        <p><strong>Order ID:</strong> #$order_id</p>
        <p><strong>Total Amount:</strong> ₹" . number_format($total_amount, 2) . "</p>
        <p>We'll notify you once your order is shipped.</p>
        <br>
        <p>Track your order: <a href='http://localhost/homestore/my_orders.php'>My Orders</a></p>
        <br>
        <p>Thank you for shopping with HomeStyle!</p>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: noreply@homestyle.com" . "\r\n";
    
    // For local development, mail() might not work
    // You can use PHPMailer or log to file
    // mail($email, $subject, $message, $headers);
    
    // Log email for development
    $log_file = 'email_log.txt';
    $log_content = date('Y-m-d H:i:s') . " - To: $email, Subject: $subject\n";
    file_put_contents($log_file, $log_content, FILE_APPEND);
    
    return true;
}

function sendOrderStatusUpdate($email, $name, $order_id, $status) {
    $subject = "Order Status Update - Order #$order_id";
    
    $message = "
    <html>
    <head>
        <title>Order Status Update</title>
    </head>
    <body>
        <h2>Hello $name,</h2>
        <p>Your order #$order_id status has been updated to:</p>
        <h3 style='color: #ff9900;'>$status</h3>
        <br>
        <p>Track your order: <a href='http://localhost/homestore/my_orders.php'>My Orders</a></p>
        <p>Thank you for shopping with HomeStyle!</p>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: noreply@homestyle.com" . "\r\n";
    
    // mail($email, $subject, $message, $headers);
    
    // Log email
    $log_file = 'email_log.txt';
    $log_content = date('Y-m-d H:i:s') . " - Status Update: To: $email, Order: $order_id, Status: $status\n";
    file_put_contents($log_file, $log_content, FILE_APPEND);
    
    return true;
}
?>