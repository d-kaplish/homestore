<?php
session_start();
include 'db.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$prod_id = intval($_GET['id']);

$check = mysqli_query($conn, "SELECT * FROM wishlist WHERE userid=$user_id AND prod_id=$prod_id");

if(mysqli_num_rows($check) == 0) {
    mysqli_query($conn, "INSERT INTO wishlist(userid, prod_id) VALUES($user_id, $prod_id)");
}

header("Location: wishlist.php");
exit();
?>