<?php
include 'db.php';

if(isset($_GET['prod_id'])) {
    $prod_id = intval($_GET['prod_id']);
    
    $query = "SELECT r.*, u.name, u.email 
              FROM reviews r 
              JOIN users u ON r.user_id = u.userid 
              WHERE r.prod_id = ? 
              ORDER BY r.created_at DESC";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $prod_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $reviews = [];
    while($row = mysqli_fetch_assoc($result)) {
        $reviews[] = $row;
    }
    
    echo json_encode($reviews);
}
?>