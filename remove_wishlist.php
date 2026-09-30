<?php
session_start();
include 'db.php';
include 'navbar.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT products.* FROM wishlist 
        JOIN products ON wishlist.prod_id = products.prod_id 
        WHERE wishlist.userid = $user_id";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
<title>Wishlist</title>
<link rel="stylesheet" href="styles.css">
</head>

<body>

<div class="main-container">
<h2>Your Wishlist ❤️</h2>

<?php if(mysqli_num_rows($result) > 0) { ?>

<div class="product-container">
<?php while($row = mysqli_fetch_assoc($result)) { ?>
    
    <div class="product-card">
        <img src="images/<?php echo $row['image']; ?>">
        <h3><?php echo $row['p_name']; ?></h3>
        <p><?php echo $row['brand']; ?></p>
        <p class="price">₹<?php echo $row['price']; ?></p>

        <a href="add_to_cart.php?id=<?php echo $row['prod_id']; ?>">
            <button>Add to Cart</button>
        </a>

        <a href="remove_wishlist.php?id=<?php echo $row['prod_id']; ?>">
            <button>Remove</button>
        </a>
    </div>

<?php } ?>
</div>

<?php } else { ?>
<p>No items in wishlist 😢</p>
<?php } ?>

</div>

</body>
</html>