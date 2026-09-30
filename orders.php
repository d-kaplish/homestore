<?php
session_start();
include("db.php");
include("a_navbar.php");

if(!isset($_SESSION['isadmin']) || $_SESSION['isadmin'] !== 'yes') {
    header("Location: login.php");
    exit();
}

$success_message = '';
$error_message = '';

// Update order status
if(isset($_POST['update_status'])){
    $order_id = intval($_POST['order_id']);
    $new_status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $update_query = "UPDATE orders SET status=? WHERE order_id=?";
    $stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt, "si", $new_status, $order_id);
    
    if(mysqli_stmt_execute($stmt)) {
        $success_message = "Order #$order_id status updated to: $new_status";
    } else {
        $error_message = "Failed to update order status!";
    }
}

// Filter orders by status
$status_filter = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';
$where_clause = "";
if($status_filter && $status_filter != 'all') {
    $where_clause = "WHERE o.status = '$status_filter'";
}

// Search orders
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
if($search) {
    $where_clause = "WHERE o.order_id LIKE '%$search%' OR u.name LIKE '%$search%' OR u.email LIKE '%$search%'";
}

// Fetch orders
$query = "
    SELECT o.order_id, u.name AS user_name, u.email, u.address, o.order_date, o.status, 
           COUNT(oi.prod_id) AS total_items, o.total_amount, 
           COALESCE(o.coupon_code, '') as coupon_code, 
           COALESCE(o.discount_amount, 0) as discount_amount
    FROM orders o
    JOIN users u ON o.user_id = u.userid
    JOIN order_items oi ON o.order_id = oi.order_id
    $where_clause
    GROUP BY o.order_id
    ORDER BY o.order_date DESC
";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Orders - HomeStyle</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="admin-main-container">
    <div class="orders-page">
        
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-left">
                <h2><i class="fas fa-box"></i> Order Management</h2>
                <p>View and manage all customer orders</p>
            </div>
            <div class="header-right">
                <div class="order-stats">
                    <span class="stat-badge total">Total: <?php echo mysqli_num_rows($result); ?> Orders</span>
                </div>
            </div>
        </div>
        
        <?php if($success_message): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if($error_message): ?>
            <div class="alert alert-error"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <!-- Filter and Search Bar -->
        <div class="orders-filter-bar">
            <div class="filter-group">
                <label><i class="fas fa-filter"></i> Filter by Status:</label>
                <select onchange="window.location.href='orders.php?status='+this.value" class="filter-select">
                    <option value="all" <?php echo $status_filter == 'all' || !$status_filter ? 'selected' : ''; ?>>All Orders</option>
                    <option value="Waiting to be shipped" <?php echo $status_filter == 'Waiting to be shipped' ? 'selected' : ''; ?>>Waiting to be shipped</option>
                    <option value="Shipped out for delivery" <?php echo $status_filter == 'Shipped out for delivery' ? 'selected' : ''; ?>>Shipped out for delivery</option>
                    <option value="Delivered" <?php echo $status_filter == 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                    <option value="Cancelled" <?php echo $status_filter == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </div>
            
            <div class="search-group">
                <form method="GET" action="orders.php" class="search-form">
                    <input type="text" name="search" placeholder="Search by Order ID, Customer, or Email" value="<?php echo htmlspecialchars($search); ?>" class="search-input">
                    <button type="submit" class="search-btn"><i class="fas fa-search"></i></button>
                    <?php if($search): ?>
                        <a href="orders.php" class="clear-btn"><i class="fas fa-times"></i> Clear</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
        
        <!-- Orders Table - TABULAR FORMAT -->
        <div class="orders-table-container">
            <table class="orders-data-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Contact</th>
                        <th>Address</th>
                        <th>Items</th>
                        <th>Amount</th>
                        <th>Coupon</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($result) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($result)) { ?>
                            <tr>
                                <td class="order-id">
                                    <strong>#<?php echo str_pad($row['order_id'], 6, '0', STR_PAD_LEFT); ?></strong>
                                </td>
                                <td class="customer-info">
                                    <div class="customer-cell">
                                        <i class="fas fa-user-circle"></i>
                                        <span><?php echo htmlspecialchars($row['user_name']); ?></span>
                                        <small><?php echo htmlspecialchars($row['email']); ?></small>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($row['contact'] ?? '—'); ?></td>
                                <td class="address-cell">
                                    <?php echo htmlspecialchars(substr($row['address'], 0, 35)) . (strlen($row['address']) > 35 ? '...' : ''); ?>
                                </td>
                                <td class="items-cell">
                                    <a href="order_items.php?order_id=<?php echo $row['order_id']; ?>" class="items-link">
                                        <i class="fas fa-eye"></i> <?php echo $row['total_items']; ?> Items
                                    </a>
                                </td>
                                <td class="amount-cell">
                                    <strong>₹<?php echo number_format($row['total_amount'], 2); ?></strong>
                                </td>
                                <td class="coupon-cell">
                                    <?php if($row['coupon_code']): ?>
                                        <div class="coupon-tag">
                                            <i class="fas fa-tag"></i> <?php echo $row['coupon_code']; ?>
                                            <span class="discount">-₹<?php echo number_format($row['discount_amount'], 2); ?></span>
                                        </div>
                                    <?php else: ?>
                                        <span class="no-coupon">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="date-cell">
                                    <?php echo date('d M Y', strtotime($row['order_date'])); ?>
                                    <small><?php echo date('h:i A', strtotime($row['order_date'])); ?></small>
                                </td>
                                <td class="status-cell">
                                    <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $row['status'])); ?>">
                                        <?php 
                                        $status_icon = '';
                                        switch($row['status']) {
                                            case 'Waiting to be shipped': $status_icon = 'fa-clock'; break;
                                            case 'Shipped out for delivery': $status_icon = 'fa-truck'; break;
                                            case 'Delivered': $status_icon = 'fa-check-circle'; break;
                                            case 'Cancelled': $status_icon = 'fa-times-circle'; break;
                                        }
                                        ?>
                                        <i class="fas <?php echo $status_icon; ?>"></i>
                                        <?php echo $row['status']; ?>
                                    </span>
                                </td>
                                <td class="action-cell">
    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        <!-- View Bill Button -->
        <a href="bill.php?order_id=<?php echo $row['order_id']; ?>" class="view-bill-btn" style="background: #1a1a2e; color: white; padding: 6px 12px; border-radius: 6px; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; font-size: 12px;">
            <i class="fas fa-file-invoice"></i> Bill
        </a>
        
        <!-- Status Update Form -->
        <form class="status-update-form" method="POST" style="display: flex; gap: 5px;">
            <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
            <select name="status" class="status-select">
                <?php
                $statuses = ['Waiting to be shipped', 'Shipped out for delivery', 'Delivered', 'Cancelled'];
                foreach($statuses as $status){
                    $selected = ($row['status'] == $status) ? 'selected' : '';
                    echo "<option value='$status' $selected>$status</option>";
                }
                ?>
            </select>
            <button type="submit" name="update_status" class="update-btn" title="Update Status">
                <i class="fas fa-sync-alt"></i>
            </button>
        </form>
    </div>
</td>
                            </tr>
                        <?php } ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="no-data">
                                <i class="fas fa-inbox fa-3x"></i>
                                <p>No orders found</p>
                                <?php if($search || $status_filter): ?>
                                    <a href="orders.php" class="clear-filters-link">Clear Filters</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
    </div>
</div>

</body>
</html>