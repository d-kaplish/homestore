<?php
session_start(); // Start session FIRST
include("db.php");

// Check admin access BEFORE any output
if(!isset($_SESSION['isadmin']) || $_SESSION['isadmin'] !== 'yes') {
    header("Location: login.php");
    exit();
}

include("a_navbar.php");

// Delete user
if(isset($_GET['delete'])) {
    $user_id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM users WHERE userid = $user_id");
    header("Location: users.php?msg=deleted");
    exit();
}

// Search and filter
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

$where = "";
if($search) {
    $where = "WHERE name LIKE '%$search%' OR email LIKE '%$search%' OR contact LIKE '%$search%'";
}

$order = "";
switch($sort) {
    case 'newest':
        $order = "ORDER BY created_at DESC";
        break;
    case 'oldest':
        $order = "ORDER BY created_at ASC";
        break;
    case 'name_asc':
        $order = "ORDER BY name ASC";
        break;
    case 'name_desc':
        $order = "ORDER BY name DESC";
        break;
    case 'orders_desc':
        $order = "ORDER BY orders_made DESC";
        break;
}

$users = mysqli_query($conn, "SELECT * FROM users WHERE isadmin = 'no' $where $order");

// Get statistics
$total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE isadmin = 'no'"))['count'];
$total_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders"))['count'];
$total_spent = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_amount) as total FROM orders WHERE status != 'Cancelled'"))['total'] ?? 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Customers - HomeStyle Admin</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="admin-main-container">
    <div class="users-page">
        
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-left">
                <h2><i class="fas fa-users"></i> Customer Management</h2>
                <p>Manage your store customers</p>
            </div>
        </div>
        
        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
            <div class="alert alert-success">Customer deleted successfully!</div>
        <?php endif; ?>
        
        <!-- Stats Cards -->
        <div class="stats-row">
            <div class="stat-card-small">
                <div class="stat-icon-sm">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info-sm">
                    <h3><?php echo number_format($total_users); ?></h3>
                    <p>Total Customers</p>
                </div>
            </div>
            
            <div class="stat-card-small">
                <div class="stat-icon-sm">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-info-sm">
                    <h3><?php echo number_format($total_orders); ?></h3>
                    <p>Total Orders</p>
                </div>
            </div>
            
            <div class="stat-card-small">
                <div class="stat-icon-sm">
                    <i class="fas fa-rupee-sign"></i>
                </div>
                <div class="stat-info-sm">
                    <h3>₹<?php echo number_format($total_spent, 2); ?></h3>
                    <p>Total Spent</p>
                </div>
            </div>
            
            <div class="stat-card-small">
                <div class="stat-icon-sm">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-info-sm">
                    <h3>₹<?php echo number_format($total_users > 0 ? $total_spent / $total_users : 0, 2); ?></h3>
                    <p>Avg. Per Customer</p>
                </div>
            </div>
        </div>
        
        <!-- Filter Bar -->
        <div class="filter-bar">
            <form method="GET" action="users.php" class="filter-form">
                <div class="search-group">
                    <input type="text" name="search" placeholder="Search by name, email, or phone..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </div>
                
                <select name="sort" onchange="this.form.submit()">
                    <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                    <option value="oldest" <?php echo $sort == 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                    <option value="name_asc" <?php echo $sort == 'name_asc' ? 'selected' : ''; ?>>Name (A-Z)</option>
                    <option value="name_desc" <?php echo $sort == 'name_desc' ? 'selected' : ''; ?>>Name (Z-A)</option>
                    <option value="orders_desc" <?php echo $sort == 'orders_desc' ? 'selected' : ''; ?>>Most Orders</option>
                </select>
                
                <?php if($search): ?>
                    <a href="users.php" class="clear-filter">Clear</a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Users Table -->
        <div class="table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Contact</th>
                        <th>Email</th>
                        <th>Address</th>
                        <th>Orders</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($users) > 0): ?>
                        <?php while($user = mysqli_fetch_assoc($users)): ?>
                        <tr>
                            <td>#<?php echo $user['userid']; ?></td>
                            <td>
                                <div class="customer-info">
                                    <div class="customer-avatar">
                                        <i class="fas fa-user-circle"></i>
                                    </div>
                                    <div>
                                        <strong><?php echo htmlspecialchars($user['name']); ?></strong>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($user['contact']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td class="address-cell"><?php echo htmlspecialchars($user['address'] ?: '—'); ?></td>
                            <td>
                                <span class="order-count-badge"><?php echo $user['orders_made']; ?> orders</span>
                            </td>
                            <td><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                            <td class="actions">
                                <a href="user_orders.php?user_id=<?php echo $user['userid']; ?>" class="btn-view" title="View Orders">
                                    <i class="fas fa-shopping-bag"></i>
                                </a>
                                <a href="users.php?delete=<?php echo $user['userid']; ?>" class="btn-delete" onclick="return confirm('Delete this customer? This will also delete all their orders.')" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="no-data">
                                <i class="fas fa-users-slash"></i>
                                <p>No customers found</p>
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