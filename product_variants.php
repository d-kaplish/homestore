<?php
include("db.php");
include("a_navbar.php");

if(!isset($_SESSION['isadmin']) || $_SESSION['isadmin'] !== 'yes') {
    header("Location: login.php");
    exit();
}

$product_id = isset($_GET['prod_id']) ? intval($_GET['prod_id']) : 0;

// Add variant
if(isset($_POST['add_variant'])) {
    $prod_id = intval($_POST['prod_id']);
    $size = mysqli_real_escape_string($conn, $_POST['size']);
    $color = mysqli_real_escape_string($conn, $_POST['color']);
    $additional_price = floatval($_POST['additional_price']);
    $stock = intval($_POST['stock']);
    
    $insert = "INSERT INTO product_variants (prod_id, size, color, additional_price, stock) 
               VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $insert);
    mysqli_stmt_bind_param($stmt, "issdi", $prod_id, $size, $color, $additional_price, $stock);
    
    if(mysqli_stmt_execute($stmt)) {
        $message = "Variant added successfully!";
    } else {
        $error = "Failed to add variant!";
    }
}

// Get product info
$product = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM products WHERE prod_id = $product_id"));

// Get variants
$variants = mysqli_query($conn, "SELECT * FROM product_variants WHERE prod_id = $product_id");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Variants - <?php echo $product['p_name']; ?></title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="main-container">
    <h2>Manage Variants: <?php echo $product['p_name']; ?></h2>
    
    <div class="form-container">
        <h3>Add New Variant</h3>
        <form method="POST" class="product-form">
            <input type="hidden" name="prod_id" value="<?php echo $product_id; ?>">
            <table>
                <tr>
                    <td>Size:</td>
                    <td>
                        <select name="size" required>
                            <option value="">Select Size</option>
                            <option value="S">Small (S)</option>
                            <option value="M">Medium (M)</option>
                            <option value="L">Large (L)</option>
                            <option value="XL">Extra Large (XL)</option>
                            <option value="XXL">XXL</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Color:</td>
                    <td><input type="text" name="color" placeholder="e.g., Red, Blue, Black" required></td>
                </tr>
                <tr>
                    <td>Additional Price:</td>
                    <td><input type="number" step="0.01" name="additional_price" value="0"></td>
                </tr>
                <tr>
                    <td>Stock:</td>
                    <td><input type="number" name="stock" required></td>
                </tr>
            </table>
            <button type="submit" name="add_variant">Add Variant</button>
        </form>
    </div>
    
    <h3>Existing Variants</h3>
    <table class="orders-table">
        <tr>
            <th>ID</th>
            <th>Size</th>
            <th>Color</th>
            <th>Additional Price</th>
            <th>Total Price</th>
            <th>Stock</th>
            <th>Action</th>
        </tr>
        <?php while($variant = mysqli_fetch_assoc($variants)): ?>
        <tr>
            <td><?php echo $variant['variant_id']; ?></td>
            <td><?php echo $variant['size']; ?></td>
            <td><?php echo $variant['color']; ?></td>
            <td>₹<?php echo number_format($variant['additional_price'], 2); ?></td>
            <td>₹<?php echo number_format($product['price'] + $variant['additional_price'], 2); ?></td>
            <td><?php echo $variant['stock']; ?></td>
            <td>
                <a href="delete_variant.php?id=<?php echo $variant['variant_id']; ?>" 
                   class="delete-btn" onclick="return confirm('Delete this variant?')">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    
    <a href="admin_dashboard.php" class="back-btn">← Back to Products</a>
</div>
</body>
</html>