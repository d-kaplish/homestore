<?php
session_start();
include("db.php");
include("a_navbar.php");

if(!isset($_GET['order_id'])){
    header("Location: orders.php");
    exit;
}

$order_id = $_GET['order_id'];

// Fetch items for this order
$query = "
    SELECT p.p_name, p.image, p.price, oi.quantity
    FROM order_items oi
    JOIN products p ON oi.prod_id = p.prod_id
    WHERE oi.order_id = ?
";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $order_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<div class="main-container">
    <h2>Order Items for Order ID: <?php echo $order_id; ?></h2>

    <table class="orders-table">
        <tr>
            <th>Product Name</th>
            <th>Image</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Subtotal</th>
        </tr>

        <?php 
        $total_amount = 0;
        while($row = mysqli_fetch_assoc($result)) {
            $subtotal = $row['price'] * $row['quantity'];
            $total_amount += $subtotal;
        ?>
        <tr>
            <td><?php echo htmlspecialchars($row['p_name']); ?></td>
            <td><img src="images/<?php echo $row['image']; ?>" width="80" alt="<?php echo $row['p_name']; ?>"></td>
            <td>₹<?php echo number_format($row['price'],2); ?></td>
            <td><?php echo $row['quantity']; ?></td>
            <td>₹<?php echo number_format($subtotal,2); ?></td>
        </tr>
        <?php } ?>

        <tr>
            <td colspan="4" style="text-align:right;font-weight:bold;">Total Amount:</td>
            <td style="font-weight:bold;">₹<?php echo number_format($total_amount,2); ?></td>
        </tr>
    </table>

    <a href="orders.php" class="back-btn">← Back to Orders</a>
</div>