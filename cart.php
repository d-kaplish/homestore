<?php
include 'db.php';
include 'navbar.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$discount = 0;
$coupon_code = '';

// Remove coupon
if(isset($_GET['remove_coupon'])) {
    unset($_SESSION['coupon']);
    header("Location: cart.php");
    exit();
}

// Apply coupon
if(isset($_POST['apply_coupon'])) {
    $coupon_code_input = strtoupper(mysqli_real_escape_string($conn, $_POST['coupon_code']));
    
    // Check if coupon exists and is valid
    $query = "SELECT * FROM coupons WHERE code = ? AND valid_until >= CURDATE() 
              AND used_count < usage_limit";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $coupon_code_input);
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
                $discount_calc = ($cart_total * $coupon['discount_value']) / 100;
            } else {
                $discount_calc = $coupon['discount_value'];
            }
            
            // Store coupon in session
            $_SESSION['coupon'] = [
                'code' => $coupon['code'],
                'discount' => $discount_calc,
                'coupon_id' => $coupon['coupon_id'],
                'discount_type' => $coupon['discount_type'],
                'discount_value' => $coupon['discount_value']
            ];
            
            $success_message = "Coupon applied successfully! You saved ₹" . number_format($discount_calc, 2);
        } else {
            $error_message = "Minimum order value of ₹" . number_format($coupon['min_order'], 2) . " required";
        }
    } else {
        $error_message = "Invalid or expired coupon code";
    }
}

// Get coupon from session if exists
if(isset($_SESSION['coupon'])) {
    $discount = $_SESSION['coupon']['discount'];
    $coupon_code = $_SESSION['coupon']['code'];
}

// ----------------- CHECKOUT -----------------
if(isset($_POST['checkout'])) {
    // Fetch cart items with product info
    $cart_items_query = "SELECT c.*, p.price, p.p_name, p.stock as product_stock,
                         COALESCE(pv.size, 'Standard') as size,
                         COALESCE(pv.color, 'Default') as color,
                         CASE 
                             WHEN c.variant_id > 0 THEN p.price + COALESCE(pv.additional_price, 0)
                             ELSE p.price
                         END as final_price,
                         pv.stock as variant_stock
                         FROM cart c 
                         JOIN products p ON c.prod_id = p.prod_id 
                         LEFT JOIN product_variants pv ON c.variant_id = pv.variant_id
                         WHERE c.userid = $user_id";
    $cart_items_res = mysqli_query($conn, $cart_items_query);

    $total_amount = 0;
    $items_to_insert = [];
    $stock_error = false;

    while($item = mysqli_fetch_assoc($cart_items_res)) {
        // Check stock availability
        if($item['variant_id'] > 0) {
            if($item['quantity'] > $item['variant_stock']) {
                $stock_error = true;
                $error_message = "Insufficient stock for " . $item['p_name'] . " (" . $item['size'] . ", " . $item['color'] . ")";
                break;
            }
        } else {
            if($item['quantity'] > $item['product_stock']) {
                $stock_error = true;
                $error_message = "Insufficient stock for " . $item['p_name'];
                break;
            }
        }
        
        $item_total = $item['final_price'] * $item['quantity'];
        $total_amount += $item_total;
        $items_to_insert[] = [
            'prod_id' => $item['prod_id'],
            'quantity' => $item['quantity'],
            'variant_id' => $item['variant_id'],
            'size' => $item['size'],
            'color' => $item['color'],
            'price_at_time' => $item['final_price']
        ];
    }

    if(!$stock_error && $total_amount > 0) {
        // Apply coupon discount
        if(isset($_SESSION['coupon'])) {
            $total_amount -= $_SESSION['coupon']['discount'];
            
            // Update coupon usage count
            $coupon_id = $_SESSION['coupon']['coupon_id'];
            mysqli_query($conn, "UPDATE coupons SET used_count = used_count + 1 WHERE coupon_id = $coupon_id");
        }

        $order_date = date('Y-m-d H:i:s');
        $status = 'Waiting to be shipped';

        // Insert into orders table
        $stmt = mysqli_prepare($conn, "INSERT INTO orders (user_id, total_amount, order_date, status, coupon_code, discount_amount) VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "idsssd", $user_id, $total_amount, $order_date, $status, $coupon_code, $discount);
        mysqli_stmt_execute($stmt);

        $order_id = mysqli_insert_id($conn);

        // Insert into order_items table and update stock
        foreach($items_to_insert as $item){
            $stmt = mysqli_prepare($conn, "INSERT INTO order_items (order_id, prod_id, quantity, variant_id, size, color, price_at_time) VALUES (?, ?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "iiisssd", $order_id, $item['prod_id'], $item['quantity'], $item['variant_id'], $item['size'], $item['color'], $item['price_at_time']);
            mysqli_stmt_execute($stmt);
            
            // Update stock
            if($item['variant_id'] > 0) {
                mysqli_query($conn, "UPDATE product_variants SET stock = stock - {$item['quantity']} WHERE variant_id = {$item['variant_id']}");
            } else {
                mysqli_query($conn, "UPDATE products SET stock = stock - {$item['quantity']} WHERE prod_id = {$item['prod_id']}");
            }
        }

        // Clear cart
        mysqli_query($conn, "DELETE FROM cart WHERE userid = $user_id");
        
        // Clear coupon from session
        unset($_SESSION['coupon']);

        echo "<script>alert('Order placed successfully!'); window.location='my_orders.php';</script>";
        exit;
    }
}

// ----------------- FETCH CART FOR DISPLAY -----------------
$sql = "SELECT c.*, p.p_name, p.price, p.image, p.stock as product_stock,
        COALESCE(pv.size, 'Standard') as size,
        COALESCE(pv.color, 'Default') as color,
        COALESCE(pv.stock, p.stock) as available_stock,
        CASE 
            WHEN c.variant_id > 0 THEN p.price + COALESCE(pv.additional_price, 0)
            ELSE p.price
        END as final_price
        FROM cart c 
        JOIN products p ON c.prod_id = p.prod_id 
        LEFT JOIN product_variants pv ON c.variant_id = pv.variant_id
        WHERE c.userid = $user_id";

$result = mysqli_query($conn, $sql);
$subtotal = 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Cart - HomeStyle</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

<div class="main-container">
    <h2><i class="fas fa-shopping-cart"></i> My Cart</h2>

    <?php if(isset($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    
    <?php if(isset($error_message)): ?>
        <div class="alert alert-error"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <?php if(mysqli_num_rows($result) > 0) { ?>
        <table class="cart-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Product</th>
                    <th>Variant</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php while($row = mysqli_fetch_assoc($result)) {
                $item_total = $row['final_price'] * $row['quantity'];
                $subtotal += $item_total;
            ?>
                <tr>
                    <td><img src="images/<?php echo $row['image']; ?>" width="80" alt="<?php echo $row['p_name']; ?>"></td>
                    <td><?php echo htmlspecialchars($row['p_name']); ?></td>
                    <td>
                        <?php if($row['variant_id'] > 0): ?>
                            <small>Size: <?php echo $row['size']; ?><br>Color: <?php echo $row['color']; ?></small>
                        <?php else: ?>
                            <small>Standard</small>
                        <?php endif; ?>
                    </td>
                    <td>₹<?php echo number_format($row['final_price'],2); ?></td>
                    <td>
                        <form action="update_cart_quantity.php" method="POST" style="display: inline-block;">
                            <input type="hidden" name="cart_id" value="<?php echo $row['cart_id']; ?>">
                            <input type="number" name="quantity" value="<?php echo $row['quantity']; ?>" min="1" max="<?php echo $row['available_stock']; ?>" style="width: 60px; text-align: center;" onchange="this.form.submit()">
                        </form>
                    </td>
                    <td>₹<?php echo number_format($item_total,2); ?></td>
                    <td>
                        <a href="remove_from_cart.php?id=<?php echo $row['prod_id']; ?>&variant=<?php echo $row['variant_id']; ?>">
                            <button class="delete-btn"><i class="fas fa-trash"></i> Remove</button>
                        </a>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" style="text-align:right; font-weight:bold;">Subtotal:</td>
                    <td colspan="2" style="font-weight:bold;">₹<?php echo number_format($subtotal,2); ?></td>
                </tr>
                <?php if($discount > 0): ?>
                <tr style="color: green;">
                    <td colspan="5" style="text-align:right; font-weight:bold;">Coupon Discount (<?php echo $coupon_code; ?>):</td>
                    <td colspan="2" style="font-weight:bold;">- ₹<?php echo number_format($discount,2); ?></td>
                </tr>
                <tr>
                    <td colspan="5" style="text-align:right; font-weight:bold; font-size: 18px;">Grand Total:</td>
                    <td colspan="2" style="font-weight:bold; font-size: 18px; color: #b12704;">₹<?php echo number_format($subtotal - $discount,2); ?></td>
                </tr>
                <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align:right; font-weight:bold; font-size: 18px;">Total:</td>
                    <td colspan="2" style="font-weight:bold; font-size: 18px; color: #b12704;">₹<?php echo number_format($subtotal,2); ?></td>
                </tr>
                <?php endif; ?>
            </tfoot>
        </table>

        <!-- Coupon Section -->
        <div class="coupon-section">
            <?php if(isset($_SESSION['coupon'])): ?>
                <div class="coupon-applied">
                    <i class="fas fa-tag"></i> 
                    <strong>Coupon Applied:</strong> <?php echo $_SESSION['coupon']['code']; ?>
                    <span class="coupon-saved">Saved: ₹<?php echo number_format($_SESSION['coupon']['discount'], 2); ?></span>
                    <a href="cart.php?remove_coupon=1" class="remove-coupon-btn">
                        <i class="fas fa-times"></i> Remove
                    </a>
                </div>
            <?php else: ?>
                <div class="coupon-input-group">
                    <label><i class="fas fa-ticket-alt"></i> Have a coupon code?</label>
                    <form method="POST" class="coupon-form">
                        <input type="text" name="coupon_code" id="coupon_code" placeholder="Enter coupon code" class="coupon-input">
                        <button type="submit" name="apply_coupon" class="apply-coupon-btn">Apply Coupon</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>

        <form method="POST" style="margin-top: 20px;">
            <button type="submit" name="checkout" class="checkout-btn">
                <i class="fas fa-credit-card"></i> Proceed to Checkout
            </button>
        </form>

    <?php } else { ?>
        <div class="empty-cart">
            <i class="fas fa-shopping-cart fa-4x"></i>
            <p>Your cart is empty.</p>
            <a href="index.php" class="btn-primary">Continue Shopping</a>
        </div>
    <?php } ?>
</div>

</body>
</html>