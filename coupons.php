<?php
include("db.php");
include("a_navbar.php");

if(!isset($_SESSION['isadmin']) || $_SESSION['isadmin'] !== 'yes') {
    header("Location: login.php");
    exit();
}

// Add coupon
if(isset($_POST['add_coupon'])) {
    $code = strtoupper(mysqli_real_escape_string($conn, $_POST['code']));
    $discount_type = $_POST['discount_type'];
    $discount_value = floatval($_POST['discount_value']);
    $min_order = floatval($_POST['min_order']);
    $valid_until = $_POST['valid_until'];
    $usage_limit = intval($_POST['usage_limit']);
    
    // Check if coupon code already exists
    $check = mysqli_query($conn, "SELECT * FROM coupons WHERE code = '$code'");
    if(mysqli_num_rows($check) > 0) {
        $error = "Coupon code already exists!";
    } else {
        $insert = "INSERT INTO coupons (code, discount_type, discount_value, min_order, valid_until, usage_limit) 
                   VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insert);
        mysqli_stmt_bind_param($stmt, "ssddsi", $code, $discount_type, $discount_value, $min_order, $valid_until, $usage_limit);
        
        if(mysqli_stmt_execute($stmt)) {
            $success = "Coupon added successfully!";
        } else {
            $error = "Failed to add coupon!";
        }
    }
}

// Delete coupon
if(isset($_GET['delete'])) {
    $coupon_id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM coupons WHERE coupon_id = $coupon_id");
    header("Location: coupons.php?msg=deleted");
    exit();
}

// Fetch all coupons
$coupons = mysqli_query($conn, "SELECT * FROM coupons ORDER BY coupon_id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Coupons - HomeStyle Admin</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="admin-main-container">
    <div class="coupons-page">
        
        <!-- Page Header -->
        <div class="page-header">
            <h2><i class="fas fa-tags"></i> Coupon Management</h2>
            <p>Create and manage discount coupons for your customers</p>
        </div>
        
        <?php if(isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
            <div class="alert alert-success">Coupon deleted successfully!</div>
        <?php endif; ?>
        
        <!-- Add Coupon Card -->
        <div class="add-coupon-card">
            <div class="add-coupon-header">
                <h3>
                    <i class="fas fa-plus-circle"></i>
                    Create New Coupon
                </h3>
            </div>
            <div class="add-coupon-form">
                <form method="POST">
                    <div class="coupon-form-grid">
                        <div class="coupon-form-group">
                            <label><i class="fas fa-code"></i> Coupon Code *</label>
                            <input type="text" name="code" placeholder="e.g., SAVE20, WELCOME10" required>
                            <small>Use uppercase letters and numbers only</small>
                        </div>
                        
                        <div class="coupon-form-group">
                            <label><i class="fas fa-percent"></i> Discount Type *</label>
                            <select name="discount_type" required>
                                <option value="percentage">Percentage (%)</option>
                                <option value="fixed">Fixed Amount (₹)</option>
                            </select>
                        </div>
                        
                        <div class="coupon-form-group">
                            <label><i class="fas fa-rupee-sign"></i> Discount Value *</label>
                            <input type="number" step="0.01" name="discount_value" placeholder="Enter discount amount" required>
                        </div>
                        
                        <div class="coupon-form-group">
                            <label><i class="fas fa-shopping-cart"></i> Minimum Order Value</label>
                            <input type="number" step="0.01" name="min_order" placeholder="Minimum order amount" value="0">
                            <small>Leave 0 for no minimum</small>
                        </div>
                        
                        <div class="coupon-form-group">
                            <label><i class="fas fa-calendar"></i> Valid Until *</label>
                            <input type="date" name="valid_until" required>
                        </div>
                        
                        <div class="coupon-form-group">
                            <label><i class="fas fa-users"></i> Usage Limit</label>
                            <input type="number" name="usage_limit" placeholder="Maximum uses" value="1">
                        </div>
                    </div>
                    
                    <button type="submit" name="add_coupon" class="btn-add-coupon">
                        <i class="fas fa-plus"></i> Create Coupon
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Coupons List -->
        <div class="coupons-list-card">
            <div class="coupons-header">
                <h3>
                    <i class="fas fa-list"></i>
                    Active Coupons
                </h3>
            </div>
            
            <div class="coupons-grid">
                <?php if(mysqli_num_rows($coupons) > 0): ?>
                    <?php while($coupon = mysqli_fetch_assoc($coupons)): 
                        $usage_percentage = ($coupon['used_count'] / $coupon['usage_limit']) * 100;
                        $is_expired = strtotime($coupon['valid_until']) < time();
                        $is_fully_used = $coupon['used_count'] >= $coupon['usage_limit'];
                    ?>
                    <div class="coupon-card <?php echo ($is_expired || $is_fully_used) ? 'expired' : ''; ?>">
                        <div class="coupon-card-header">
                            <span class="coupon-code"><?php echo $coupon['code']; ?></span>
                            <span class="coupon-discount">
                                <?php if($coupon['discount_type'] == 'percentage'): ?>
                                    <?php echo $coupon['discount_value']; ?>% OFF
                                <?php else: ?>
                                    ₹<?php echo number_format($coupon['discount_value'], 2); ?> OFF
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="coupon-card-body">
                            <div class="coupon-detail">
                                <span class="label">Minimum Order:</span>
                                <span class="value">₹<?php echo number_format($coupon['min_order'], 2); ?></span>
                            </div>
                            <div class="coupon-detail">
                                <span class="label">Valid Until:</span>
                                <span class="value <?php echo $is_expired ? 'expired-text' : ''; ?>">
                                    <?php echo date('d M Y', strtotime($coupon['valid_until'])); ?>
                                    <?php if($is_expired): ?>(Expired)<?php endif; ?>
                                </span>
                            </div>
                            <div class="coupon-usage">
                                <div class="usage-bar">
                                    <div class="usage-progress" style="width: <?php echo min($usage_percentage, 100); ?>%"></div>
                                </div>
                                <div class="usage-text">
                                    Used <?php echo $coupon['used_count']; ?> of <?php echo $coupon['usage_limit']; ?> times
                                </div>
                            </div>
                        </div>
                        <div class="coupon-card-footer">
                            <a href="coupons.php?delete=<?php echo $coupon['coupon_id']; ?>" class="delete-coupon-btn" onclick="return confirm('Delete this coupon?')">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-coupons">
                        <i class="fas fa-tags"></i>
                        <p>No coupons created yet</p>
                        <small>Create your first coupon using the form above</small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
    </div>
</div>

<style>
.expired {
    opacity: 0.6;
}
.expired-text {
    color: #f44336;
}
</style>

</body>
</html>