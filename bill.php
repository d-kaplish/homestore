<?php
session_start();
include 'db.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$is_admin = (isset($_SESSION['isadmin']) && $_SESSION['isadmin'] === 'yes');

// Get order ID from URL
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if($order_id == 0) {
    if($is_admin) {
        header("Location: orders.php");
    } else {
        header("Location: my_orders.php");
    }
    exit();
}

// Verify order belongs to user OR user is admin
$order_query = "SELECT o.*, u.name, u.email, u.contact, u.address 
                FROM orders o 
                JOIN users u ON o.user_id = u.userid 
                WHERE o.order_id = ?";
                
if(!$is_admin) {
    $order_query .= " AND o.user_id = ?";
}

$stmt = mysqli_prepare($conn, $order_query);
if(!$is_admin) {
    mysqli_stmt_bind_param($stmt, "ii", $order_id, $user_id);
} else {
    mysqli_stmt_bind_param($stmt, "i", $order_id);
}
mysqli_stmt_execute($stmt);
$order_result = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($order_result) == 0) {
    if($is_admin) {
        header("Location: orders.php");
    } else {
        header("Location: my_orders.php");
    }
    exit();
}

$order = mysqli_fetch_assoc($order_result);

// Get order items
$items_query = "SELECT oi.*, p.p_name, p.image 
                FROM order_items oi 
                JOIN products p ON oi.prod_id = p.prod_id 
                WHERE oi.order_id = ?";
$stmt = mysqli_prepare($conn, $items_query);
mysqli_stmt_bind_param($stmt, "i", $order_id);
mysqli_stmt_execute($stmt);
$items_result = mysqli_stmt_get_result($stmt);

// Calculate subtotal
$subtotal = 0;
$items = [];
while($item = mysqli_fetch_assoc($items_result)) {
    $item_total = $item['price_at_time'] * $item['quantity'];
    $subtotal += $item_total;
    $items[] = $item;
}

$discount = $order['discount_amount'];
$total = $order['total_amount'];

// Check if payment is made (order status is not 'Waiting to be shipped')
// For delivered or shipped orders, consider as paid
$is_paid = ($order['status'] != 'Waiting to be shipped');

// Include appropriate navbar based on user role
if($is_admin) {
    include("a_navbar.php");
} else {
    include("navbar.php");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Order Bill #<?php echo str_pad($order_id, 6, '0', STR_PAD_LEFT); ?> - HomeStyle</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="<?php echo $is_admin ? 'admin-main-container' : 'main-container'; ?>">
    <div class="bill-container">
        <!-- Bill Header -->
        <div class="bill-header">
            <h1>
                <i class="fas fa-file-invoice"></i>
                TAX INVOICE
            </h1>
            <p>Order Confirmation & Payment Receipt</p>
            <div class="bill-status <?php echo $is_paid ? 'paid' : 'pending'; ?>">
                <i class="fas <?php echo $is_paid ? 'fa-check-circle' : 'fa-clock'; ?>"></i>
                <?php echo $is_paid ? 'PAID' : 'PENDING PAYMENT'; ?>
            </div>
        </div>
        
        <!-- Bill Body -->
        <div class="bill-body">
            <!-- Company Details -->
            <div class="company-details">
                <h2>HomeStyle</h2>
                <p>Your One-Stop Home Solution Store</p>
                <p>123, Fashion Street, Camp, Pune - 411001 | support@homestyle.com | +91 12345 67890</p>
                <p><small>GSTIN: 27AAAAA1234B1Z | CIN: U12345PN2020PTC123456</small></p>
            </div>
            
            <!-- Bill Information Row -->
            <div class="bill-info-row">
                <div class="bill-info-box">
                    <h4><i class="fas fa-receipt"></i> ORDER DETAILS</h4>
                    <p>Order ID: #<?php echo str_pad($order['order_id'], 6, '0', STR_PAD_LEFT); ?></p>
                    <p>Order Date: <?php echo date('d F Y, h:i A', strtotime($order['order_date'])); ?></p>
                    <p>Status: 
                        <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $order['status'])); ?>">
                            <?php echo $order['status']; ?>
                        </span>
                    </p>
                </div>
                
                <div class="bill-info-box">
                    <h4><i class="fas fa-user"></i> BILLING DETAILS</h4>
                    <p><?php echo htmlspecialchars($order['name']); ?></p>
                    <p><?php echo htmlspecialchars($order['email']); ?></p>
                    <p><?php echo htmlspecialchars($order['contact']); ?></p>
                </div>
                
                <div class="bill-info-box">
                    <h4><i class="fas fa-map-marker-alt"></i> SHIPPING ADDRESS</h4>
                    <p><?php echo nl2br(htmlspecialchars($order['address'] ?: 'Not provided')); ?></p>
                </div>
            </div>
            
            <!-- Order Items Table -->
            <table class="bill-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $counter = 1; foreach($items as $item): ?>
                    <tr>
                        <td><?php echo $counter++; ?></td>
                        <td>
                            <div class="product-cell">
                                <img src="images/<?php echo $item['image']; ?>" alt="<?php echo $item['p_name']; ?>">
                                <div>
                                    <div class="product-name"><?php echo htmlspecialchars($item['p_name']); ?></div>
                                    <?php if($item['size'] && $item['size'] != 'Standard'): ?>
                                        <div class="product-variant">Size: <?php echo $item['size']; ?> | Color: <?php echo $item['color']; ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td>₹<?php echo number_format($item['price_at_time'], 2); ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td>₹<?php echo number_format($item['price_at_time'] * $item['quantity'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <!-- Totals Section -->
            <div class="totals-section">
                <div class="totals-row">
                    <span class="totals-label">Subtotal:</span>
                    <span class="totals-value">₹<?php echo number_format($subtotal, 2); ?></span>
                </div>
                
                <?php if($discount > 0): ?>
                <div class="totals-row">
                    <span class="totals-label">Discount (<?php echo $order['coupon_code'] ?: 'Coupon'; ?>):</span>
                    <span class="totals-value" style="color: #4caf50;">- ₹<?php echo number_format($discount, 2); ?></span>
                </div>
                <?php endif; ?>
                
                <div class="totals-row">
                    <span class="totals-label">Shipping:</span>
                    <span class="totals-value">FREE</span>
                </div>
                
                
                
                <div class="grand-total">
                    <span>GRAND TOTAL:</span>
                    <span>₹<?php echo number_format($total, 2); ?></span>
                </div>
                
                <div class="totals-row" style="margin-top: 5px;">
                    <span class="totals-label">Amount in Words:</span>
                    <span class="totals-value" style="font-size: 12px; color: #666;">
                        <?php echo convertNumberToWords($total); ?> Rupees Only
                    </span>
                </div>
            </div>
            
            <!-- Payment Note - Different for Admin and User -->
            <?php if($is_paid): ?>
            <div class="payment-note">
                <i class="fas fa-check-circle"></i>
                <div class="note-text">
                    <p><strong>Payment Confirmed!</strong> Thank you for shopping with HomeStyle.</p>
                    <small>This is a system generated invoice. No signature required.</small>
                </div>
            </div>
            <?php else: ?>
                <?php if(!$is_admin): ?>
                <!-- Show Pay Now option ONLY for users (not for admins) -->
                <div class="pending-payment-note">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div class="note-text">
                        <p><strong>Payment Pending!</strong> Please complete your payment to confirm this order.</p>
                        <small>Once payment is confirmed, you will receive a payment receipt via email.</small>
                    </div>
                    <a href="my_orders.php" class="pay-now-link">
                        <i class="fas fa-credit-card"></i> Pay Now
                    </a>
                </div>
                <?php else: ?>
                <!-- For Admin: Show pending message without Pay Now button -->
                <div class="pending-payment-note" style="background: #fff3e0;">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div class="note-text">
                        <p><strong>Payment Pending for this Order</strong></p>
                        <small>Customer has not completed the payment yet. Order will be processed after payment confirmation.</small>
                    </div>
                </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <!-- Terms & Conditions -->
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; font-size: 11px; color: #999; text-align: center;">
                <p><strong>Terms & Conditions:</strong></p>
                <p>Items once sold cannot be returned or exchanged. For any queries, contact our customer support within 7 days of delivery.</p>
                <p>This is a computer generated invoice and does not require a physical signature.</p>
            </div>
        </div>
        
        <!-- Bill Actions -->
        <div class="bill-actions">
            <button onclick="window.print()" class="btn-print">
                <i class="fas fa-print"></i> Print / Download Bill
            </button>
            <?php if($is_admin): ?>
                <a href="orders.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Back to Orders
                </a>
            <?php else: ?>
                <a href="my_orders.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Back to My Orders
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Auto trigger print dialog when ?print parameter is present
const urlParams = new URLSearchParams(window.location.search);
if(urlParams.has('print')) {
    window.print();
}
</script>

</body>
</html>

<?php
/**
 * Convert number to words in Indian Rupees format
 */
function convertNumberToWords($number) {
    $number = round($number, 2);
    $decimal = round($number - floor($number), 2) * 100;
    $number = floor($number);
    
    $words = "";
    $crore = floor($number / 10000000);
    if($crore > 0) {
        $words .= convertLessThanThousand($crore) . " Crore ";
        $number %= 10000000;
    }
    
    $lakh = floor($number / 100000);
    if($lakh > 0) {
        $words .= convertLessThanThousand($lakh) . " Lakh ";
        $number %= 100000;
    }
    
    $thousand = floor($number / 1000);
    if($thousand > 0) {
        $words .= convertLessThanThousand($thousand) . " Thousand ";
        $number %= 1000;
    }
    
    $hundred = floor($number / 100);
    if($hundred > 0) {
        $words .= convertLessThanThousand($hundred) . " Hundred ";
        $number %= 100;
    }
    
    if($number > 0) {
        if($words != "") $words .= "and ";
        $words .= convertLessThanThousand($number);
    }
    
    if($words == "") $words = "Zero";
    
    if($decimal > 0) {
        $words .= " and " . convertLessThanThousand($decimal) . " Paise";
    }
    
    return ucfirst($words);
}

function convertLessThanThousand($number) {
    $units = array("", "One", "Two", "Three", "Four", "Five", "Six", "Seven", "Eight", "Nine", 
                   "Ten", "Eleven", "Twelve", "Thirteen", "Fourteen", "Fifteen", "Sixteen", 
                   "Seventeen", "Eighteen", "Nineteen");
    $tens = array("", "", "Twenty", "Thirty", "Forty", "Fifty", "Sixty", "Seventy", "Eighty", "Ninety");
    
    if($number < 20) {
        return $units[$number];
    } elseif($number < 100) {
        return $tens[floor($number / 10)] . " " . $units[$number % 10];
    } else {
        return $units[floor($number / 100)] . " Hundred " . convertLessThanThousand($number % 100);
    }
}
?>