<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db.php';  

$user_image = '';
$user_name = 'User';

if(isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    $query = mysqli_query($conn, "SELECT name, image FROM users WHERE userid = $user_id");
    if($data = mysqli_fetch_assoc($query)) {
        $user_image = $data['image'];
        $user_name = $data['name'];
    }
}
?>

<link rel="stylesheet" href="styles.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<nav class="admin-navbar"> <!-- reuse admin styling -->
    
    <div class="admin-nav-header">

        <!-- LOGO -->
        <div class="logo">
            <a href="index.php">
                <i class="fas fa-home"></i>
                <span>HomeStyle</span>
            </a>
        </div>

        <!-- SEARCH -->
        <div class="nav-search-container">
            <form action="search.php" method="GET" class="nav-search-form">
                <input type="text" name="q" placeholder="Search products..." class="nav-search-input">
                <button type="submit" class="nav-search-btn">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>

        <!-- USER / GUEST -->
        <?php if(isset($_SESSION['user_id'])): ?>
        <div class="admin-user-info">

            <!-- AVATAR -->
            <div class="admin-avatar">
                <?php if($user_image && file_exists("uploads/profiles/".$user_image)): ?>
                    <img src="uploads/profiles/<?php echo $user_image; ?>" 
                         style="width:45px;height:45px;border-radius:50%;object-fit:cover;">
                <?php else: ?>
                    <i class="fas fa-user"></i>
                <?php endif; ?>
            </div>

            <div class="admin-details">
                <span class="admin-name"><?php echo htmlspecialchars($user_name); ?></span>
                <span class="admin-role">Customer</span>
            </div>

            <div class="admin-dropdown">
                <button class="dropdown-btn" id="userDropdownBtn">
                    <i class="fas fa-chevron-down"></i>
                </button>

                <div class="dropdown-menu" id="userDropdownMenu">
                    <a href="profile.php">
                        <i class="fas fa-user-circle"></i> My Profile
                    </a>
                    <a href="my_orders.php">
                        <i class="fas fa-shopping-bag"></i> My Orders
                    </a>
                    <a href="wishlist.php">
                        <i class="fas fa-heart"></i> Wishlist
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="logout.php" class="logout-link">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>

        </div>

        <?php else: ?>
        <div class="admin-user-info">
            <a href="login.php" class="btn-primary">
                <i class="fas fa-sign-in-alt"></i> Login
            </a>
        </div>
        <?php endif; ?>

    </div>

    <!-- NAV LINKS -->
    <div class="admin-nav-menu">
        <ul class="admin-nav-links">

            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                <a href="index.php">
                    <i class="fas fa-home"></i>
                    <span>Home</span>
                </a>
            </li>

            <li>
                <button id="filterToggle" class="nav-btn">
                    <i class="fas fa-filter"></i>
                    <span>Filters</span>
                </button>
            </li>

            <li>
                <button id="sortToggle" class="nav-btn">
                    <i class="fas fa-sort"></i>
                    <span>Sort By</span>
                </button>
            </li>

            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'wishlist.php' ? 'active' : ''; ?>">
                <a href="<?php echo isset($_SESSION['user_id']) ? 'wishlist.php' : 'login.php'; ?>">
                    <i class="fas fa-heart"></i>
                    <span>Wishlist</span>
                </a>
            </li>

            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'cart.php' ? 'active' : ''; ?>">
                <a href="<?php echo isset($_SESSION['user_id']) ? 'cart.php' : 'login.php'; ?>">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Cart</span>

                    <?php
                    if(isset($_SESSION['user_id'])) {
                        $cart_count_query = "SELECT SUM(quantity) as total FROM cart WHERE userid = $user_id";
                        $cart_count_result = mysqli_query($conn, $cart_count_query);
                        $cart_count = mysqli_fetch_assoc($cart_count_result);

                        if($cart_count['total'] > 0) {
                            echo '<span class="nav-badge">'.$cart_count['total'].'</span>';
                        }
                    }
                    ?>
                </a>
            </li>

            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'my_orders.php' ? 'active' : ''; ?>">
                <a href="<?php echo isset($_SESSION['user_id']) ? 'my_orders.php' : 'login.php'; ?>">
                    <i class="fas fa-box"></i><span>My Orders</span>
                </a>
            </li>

            <?php if(isset($_SESSION['isadmin']) && $_SESSION['isadmin'] === 'yes'): ?>
            <li>
                <a href="admin_dashboard.php">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Admin Panel</span>
                </a>
            </li>
            <?php endif; ?>

        </ul>
    </div>

</nav>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('userDropdownBtn');
    const menu = document.getElementById('userDropdownMenu');

    if(btn && menu) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            menu.classList.toggle('show');
        });

        document.addEventListener('click', function(e) {
            if(!btn.contains(e.target) && !menu.contains(e.target)) {
                menu.classList.remove('show');
            }
        });
    }
});
</script>