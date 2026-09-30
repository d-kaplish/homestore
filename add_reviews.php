<?php
session_start();
include 'db.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $prod_id = intval($_POST['prod_id']);
    $rating = intval($_POST['rating']);
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);
    
    // Check if user has purchased this product
    $check_purchase = "SELECT * FROM order_items oi 
                       JOIN orders o ON oi.order_id = o.order_id 
                       WHERE o.user_id = ? AND oi.prod_id = ? AND o.status = 'Delivered'";
    $stmt = mysqli_prepare($conn, $check_purchase);
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $prod_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    
    if(mysqli_stmt_num_rows($stmt) == 0) {
        echo "You can only review products you have purchased and received!";
        exit();
    }
    
    // Check if already reviewed
    $check_review = "SELECT * FROM reviews WHERE user_id = ? AND prod_id = ?";
    $stmt = mysqli_prepare($conn, $check_review);
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $prod_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    
    if(mysqli_stmt_num_rows($stmt) > 0) {
        // Update existing review
        $update_review = "UPDATE reviews SET rating = ?, comment = ?, created_at = NOW() WHERE user_id = ? AND prod_id = ?";
        $stmt = mysqli_prepare($conn, $update_review);
        mysqli_stmt_bind_param($stmt, "isii", $rating, $comment, $user_id, $prod_id);
    } else {
        // Insert new review
        $insert_review = "INSERT INTO reviews (user_id, prod_id, rating, comment) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insert_review);
        mysqli_stmt_bind_param($stmt, "iiis", $user_id, $prod_id, $rating, $comment);
    }
    
    if(mysqli_stmt_execute($stmt)) {
        // Update average rating in products table
        $update_avg = "UPDATE products p 
                       SET p.rating = (SELECT AVG(rating) FROM reviews WHERE prod_id = ?) 
                       WHERE p.prod_id = ?";
        $stmt = mysqli_prepare($conn, $update_avg);
        mysqli_stmt_bind_param($stmt, "ii", $prod_id, $prod_id);
        mysqli_stmt_execute($stmt);
        
        header("Location: display.php?id=$prod_id");
    } else {
        echo "Failed to submit review!";
    }
}
?>