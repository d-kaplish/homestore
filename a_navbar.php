<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if(!isset($_SESSION['isadmin']) || $_SESSION['isadmin'] !== 'yes') {
    header("Location: login.php");
    exit();
}

// Get admin name and image from session or database
$admin_name = isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'Admin';
$admin_image = isset($_SESSION['admin_image']) ? $_SESSION['admin_image'] : '';

// If image not in session, fetch from database
if(empty($admin_image) && isset($_SESSION['user_id'])) {
    $admin_id = $_SESSION['user_id'];
    $img_query = mysqli_query($conn, "SELECT image FROM users WHERE userid = $admin_id");
    if($img_data = mysqli_fetch_assoc($img_query)) {
        $admin_image = $img_data['image'];
        $_SESSION['admin_image'] = $admin_image;
    }
}

// Get pending orders count
$pending_query = "SELECT COUNT(*) as pending FROM orders WHERE status = 'Waiting to be shipped'";
$pending_result = mysqli_query($conn, $pending_query);
$pending_data = mysqli_fetch_assoc($pending_result);
$pending_count = $pending_data['pending'];

// Get low stock count
$low_stock_query = "SELECT COUNT(*) as low FROM products WHERE stock < 10";
$low_stock_result = mysqli_query($conn, $low_stock_query);
$low_stock_data = mysqli_fetch_assoc($low_stock_result);
$low_stock_count = $low_stock_data['low'];

// Get current page name
$current_page = basename($_SERVER['PHP_SELF']);
?>

<link rel="stylesheet" href="styles.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<nav class="admin-navbar">
    <div class="admin-nav-header">
        <div class="logo">
            <a href="admin_dashboard.php">
                <i class="fas fa-store"></i>
                <span>HomeStyle Admin</span>
            </a>
        </div>
        
        <div class="admin-user-info">
            <div class="admin-avatar">
                <?php if($admin_image && file_exists("uploads/profiles/".$admin_image)): ?>
                    <img src="uploads/profiles/<?php echo $admin_image; ?>" alt="Admin" style="width: 45px; height: 45px; border-radius: 50%; object-fit: cover;">
                <?php else: ?>
                    <i class="fas fa-user-shield"></i>
                <?php endif; ?>
            </div>
            <div class="admin-details">
                <span class="admin-name"><?php echo htmlspecialchars($admin_name); ?></span>
                <span class="admin-role">Administrator</span>
            </div>
            <div class="admin-dropdown">
                <button class="dropdown-btn" id="adminDropdownBtn">
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="dropdown-menu" id="adminDropdownMenu">
                    <a href="admin_profile.php">
                        <i class="fas fa-user-circle"></i> My Profile
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="logout.php" class="logout-link">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="admin-nav-menu">
        <ul class="admin-nav-links">
            <li class="<?php echo $current_page == 'admin_dashboard.php' ? 'active' : ''; ?>">
                <a href="admin_dashboard.php">
                    <i class="fas fa-chart-line"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            
            <li class="<?php echo $current_page == 'add_product.php' ? 'active' : ''; ?>">
                <a href="add_product.php">
                    <i class="fas fa-plus-circle"></i>
                    <span>Add Product</span>
                </a>
            </li>
            
            <li class="<?php echo in_array($current_page, ['products.php', 'edit_product.php', 'product_variants.php']) ? 'active' : ''; ?>">
                <a href="products.php">
                    <i class="fas fa-boxes"></i>
                    <span>Products</span>
                    <?php if($low_stock_count > 0): ?>
                        <span class="nav-badge warning"><?php echo $low_stock_count; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            
            <li class="<?php echo $current_page == 'categories.php' ? 'active' : ''; ?>">
                <a href="categories.php">
                    <i class="fas fa-tags"></i>
                    <span>Categories</span>
                </a>
            </li>
            
            <li class="<?php echo $current_page == 'orders.php' ? 'active' : ''; ?>">
                <a href="orders.php">
                    <i class="fas fa-truck"></i>
                    <span>Orders</span>
                </a>
            </li>
            
            <li class="<?php echo $current_page == 'users.php' ? 'active' : ''; ?>">
                <a href="users.php">
                    <i class="fas fa-users"></i>
                    <span>Customers</span>
                </a>
            </li>
            
            <li class="<?php echo $current_page == 'coupons.php' ? 'active' : ''; ?>">
                <a href="coupons.php">
                    <i class="fas fa-tags"></i>
                    <span>Coupons</span>
                </a>
            </li>
            
            <li class="<?php echo $current_page == 'reviews.php' ? 'active' : ''; ?>">
                <a href="reviews.php">
                    <i class="fas fa-star"></i>
                    <span>Reviews</span>
                </a>
            </li>
            
            <li class="<?php echo $current_page == 'analytics.php' ? 'active' : ''; ?>">
                <a href="analytics.php">
                    <i class="fas fa-chart-bar"></i>
                    <span>Analytics</span>
                </a>
            </li>
        
        </ul>
    </div>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropdownBtn = document.getElementById('adminDropdownBtn');
    const dropdownMenu = document.getElementById('adminDropdownMenu');
    
    if(dropdownBtn && dropdownMenu) {
        dropdownBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdownMenu.classList.toggle('show');
        });
        
        document.addEventListener('click', function(e) {
            if(!dropdownBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
                dropdownMenu.classList.remove('show');
            }
        });
    }
});
</script>