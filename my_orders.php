<?php
include 'db.php';
include 'navbar.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch all orders of user
$orders_query = "SELECT * FROM orders WHERE user_id = $user_id ORDER BY order_date DESC";
$orders_result = mysqli_query($conn, $orders_query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Orders - HomeStyle</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs2-fix"></script>
</head>
<body>

<div class="main-container">
    <h2><i class="fas fa-credit-card"></i> My Orders</h2>

    <?php if(mysqli_num_rows($orders_result) > 0): ?>
        <?php while($order = mysqli_fetch_assoc($orders_result)): ?>
            <div class="order-card">
                <div class="order-header">
                    <div class="order-info">
                        <h3>Order #<?php echo $order['order_id']; ?></h3>
                        <span class="order-date"><?php echo date('d M Y, h:i A', strtotime($order['order_date'])); ?></span>
                    </div>
                    <div class="order-status">
                        <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $order['status'])); ?>">
                            <?php echo $order['status']; ?>
                        </span>
                    </div>
                </div>
                
                <div class="order-items">
                    <?php
                    $items_query = "
                        SELECT oi.quantity, p.p_name, p.image, oi.price_at_time, oi.size, oi.color
                        FROM order_items oi
                        JOIN products p ON oi.prod_id = p.prod_id
                        WHERE oi.order_id = ".$order['order_id']."
                    ";
                    $items_result = mysqli_query($conn, $items_query);
                    $order_total = 0;
                    while($item = mysqli_fetch_assoc($items_result)):
                        $item_total = $item['price_at_time'] * $item['quantity'];
                        $order_total += $item_total;
                    ?>
                    <div class="order-item">
                        <img src="images/<?php echo $item['image']; ?>" alt="<?php echo $item['p_name']; ?>">
                        <div class="item-details">
                            <h4><?php echo htmlspecialchars($item['p_name']); ?></h4>
                            <?php if($item['size'] && $item['size'] != 'Standard'): ?>
                                <p><small>Size: <?php echo $item['size']; ?> | Color: <?php echo $item['color']; ?></small></p>
                            <?php endif; ?>
                            <p>Quantity: <?php echo $item['quantity']; ?> × ₹<?php echo number_format($item['price_at_time'],2); ?></p>
                        </div>
                        <div class="item-price">
                            ₹<?php echo number_format($item_total,2); ?>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
                
                <div class="order-footer">
    <div class="order-total">
        <strong>Total Amount: ₹<?php echo number_format($order['total_amount'],2); ?></strong>
        <?php if($order['coupon_code']): ?>
            <small class="coupon-applied">Coupon: <?php echo $order['coupon_code']; ?> (Saved: ₹<?php echo number_format($order['discount_amount'],2); ?>)</small>
        <?php endif; ?>
    </div>
    
    <div class="order-actions" style="display: flex; gap: 10px;">
        <!-- View Bill Button -->
        <a href="bill.php?order_id=<?php echo $order['order_id']; ?>" class="view-bill-btn" style="background: #1a1a2e; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
            <i class="fas fa-file-invoice"></i> View Bill
        </a>
        
        <!-- Pay Now Button (only for pending orders) -->
        <?php if($order['status'] == 'Waiting to be shipped'): ?>
            <button class="pay-now-btn" onclick="openPaymentModal(<?php echo $order['order_id']; ?>, <?php echo $order['total_amount']; ?>)">
                <i class="fas fa-qrcode"></i> Pay Now
            </button>
        <?php endif; ?>
    </div>
</div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="empty-orders">
            <i class="fas fa-shopping-bag fa-4x"></i>
            <p>You have not placed any orders yet.</p>
            <a href="index.php" class="btn-primary">Start Shopping</a>
        </div>
    <?php endif; ?>
</div>

<!-- Payment Modal -->
<div id="paymentModal" class="payment-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-qrcode"></i> Pay for Order</h3>
            <button class="close-modal" onclick="closePaymentModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="payment-amount">
                <label>Order Amount:</label>
                <span id="paymentAmount" class="amount">₹0.00</span>
            </div>
            
            <div class="payment-methods">
                <h4>Select Payment Method</h4>
                <div class="method-options">
                    <div class="method-option" onclick="selectMethod('upi')">
                        <i class="fas fa-mobile-alt"></i>
                        <span>UPI / QR Code</span>
                    </div>
                    <div class="method-option" onclick="selectMethod('card')">
                        <i class="fas fa-credit-card"></i>
                        <span>Card Payment</span>
                    </div>
                    <div class="method-option" onclick="selectMethod('netbanking')">
                        <i class="fas fa-university"></i>
                        <span>Net Banking</span>
                    </div>
                </div>
            </div>
            
            <div id="qrCodeSection" class="qr-section">
                <h4>Scan to Pay</h4>
                <div id="qrcode" class="qrcode-container"></div>
                <div class="upi-details">
                    <p><strong>UPI ID:</strong> homestyle@okhdfcbank</p>
                    <p><strong>Name:</strong> HomeStyle Store</p>
                    <button onclick="copyUPI()" class="copy-btn"><i class="fas fa-copy"></i> Copy UPI ID</button>
                </div>
                <div class="payment-note">
                    <small>After payment, upload screenshot to confirm your payment</small>
                </div>
                <div class="upload-section">
                    <label for="paymentScreenshot" class="upload-label">
                        <i class="fas fa-upload"></i> Upload Payment Screenshot
                    </label>
                    <input type="file" id="paymentScreenshot" accept="image/*" style="display: none;">
                    <div id="fileName" class="file-name"></div>
                    <button onclick="submitPayment()" class="confirm-payment-btn">Confirm Payment</button>
                </div>
            </div>
            
            <div id="cardSection" class="card-section" style="display: none;">
                <div class="card-form">
                    <div class="form-group">
                        <label>Card Number</label>
                        <input type="text" placeholder="1234 5678 9012 3456" maxlength="19">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Expiry Date</label>
                            <input type="text" placeholder="MM/YY">
                        </div>
                        <div class="form-group">
                            <label>CVV</label>
                            <input type="password" placeholder="123" maxlength="3">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Cardholder Name</label>
                        <input type="text" placeholder="Name on card">
                    </div>
                    <button onclick="processCardPayment()" class="pay-btn">Pay Now</button>
                </div>
            </div>
            
            <div id="netbankingSection" class="netbanking-section" style="display: none;">
                <select class="bank-select">
                    <option value="">Select Your Bank</option>
                    <option value="sbi">State Bank of India</option>
                    <option value="hdfc">HDFC Bank</option>
                    <option value="icici">ICICI Bank</option>
                    <option value="axis">Axis Bank</option>
                    <option value="kotak">Kotak Mahindra Bank</option>
                </select>
                <button onclick="processNetbanking()" class="pay-btn">Proceed to Pay</button>
            </div>
        </div>
    </div>
</div>

<script>
let currentOrderId = 0;
let currentAmount = 0;
let selectedMethod = 'upi';

function openPaymentModal(orderId, amount) {
    currentOrderId = orderId;
    currentAmount = amount;
    document.getElementById('paymentAmount').innerHTML = '₹' + amount.toFixed(2);
    document.getElementById('paymentModal').style.display = 'flex';
    
    // Generate QR Code
    const upiString = `upi://pay?pa=homestyle@okhdfcbank&pn=HomeStyle%20Store&am=${amount}&cu=INR&tn=Order%20${orderId}`;
    document.getElementById('qrcode').innerHTML = '';
    new QRCode(document.getElementById('qrcode'), {
        text: upiString,
        width: 200,
        height: 200,
        colorDark: '#000000',
        colorLight: '#ffffff',
        correctLevel: QRCode.CorrectLevel.H
    });
}

function closePaymentModal() {
    document.getElementById('paymentModal').style.display = 'none';
    document.getElementById('qrCodeSection').style.display = 'block';
    document.getElementById('cardSection').style.display = 'none';
    document.getElementById('netbankingSection').style.display = 'none';
    selectedMethod = 'upi';
}

function selectMethod(method) {
    selectedMethod = method;
    document.getElementById('qrCodeSection').style.display = method === 'upi' ? 'block' : 'none';
    document.getElementById('cardSection').style.display = method === 'card' ? 'block' : 'none';
    document.getElementById('netbankingSection').style.display = method === 'netbanking' ? 'block' : 'none';
}

function copyUPI() {
    const upiId = 'homestyle@okhdfcbank';
    navigator.clipboard.writeText(upiId);
    alert('UPI ID copied: ' + upiId);
}

function submitPayment() {
    const fileInput = document.getElementById('paymentScreenshot');
    if(!fileInput.files.length) {
        alert('Please upload payment screenshot');
        return;
    }
    
    // Simulate payment confirmation
    alert('Payment confirmation submitted! Your order will be processed after verification.');
    closePaymentModal();
    
    // In real implementation, you would upload the screenshot and send payment details
    // window.location.href = 'confirm_payment.php?order_id=' + currentOrderId;
}

function processCardPayment() {
    alert('Card payment processing... This is a demo. In production, integrate with a payment gateway.');
    closePaymentModal();
}

function processNetbanking() {
    const bank = document.querySelector('.bank-select').value;
    if(!bank) {
        alert('Please select your bank');
        return;
    }
    alert('Redirecting to ' + bank + ' netbanking... This is a demo.');
    closePaymentModal();
}

// File upload preview
document.getElementById('paymentScreenshot').addEventListener('change', function(e) {
    const fileName = e.target.files[0]?.name;
    document.getElementById('fileName').innerHTML = fileName ? 'Selected: ' + fileName : '';
});
</script>

</body>
</html>