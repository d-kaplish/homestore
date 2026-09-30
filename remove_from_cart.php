<?php
session_start();
include 'db.php';

$user_id = $_SESSION['user_id'];
$prod_id = intval($_GET['id']);

mysqli_query($conn, "DELETE FROM cart WHERE userid=$user_id AND prod_id=$prod_id");

header("Location: cart.php");
exit();
?>