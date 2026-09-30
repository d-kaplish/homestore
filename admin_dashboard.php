<?php
include("db.php"); 
include("a_navbar.php");

// Get statistics
$total_products = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM products"))['count'];
$total_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders"))['count'];
$total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE isadmin = 'no'"))['count'];
$total_revenue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_amount) as total FROM orders WHERE status != 'Cancelled'"))['total'] ?? 0;

// Get recent orders
$recent_orders = mysqli_query($conn, "SELECT o.*, u.name FROM orders o JOIN users u ON o.user_id = u.userid ORDER BY o.order_date DESC LIMIT 5");

// Get low stock products (stock < 10)
$low_stock = mysqli_query($conn, "SELECT * FROM products WHERE stock < 10 ORDER BY stock ASC LIMIT 5");

// Get monthly sales for chart
$monthly_sales = mysqli_query($conn, "SELECT DATE_FORMAT(order_date, '%b') as month, SUM(total_amount) as total FROM orders WHERE order_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY MONTH(order_date) ORDER BY order_date ASC");

$months = [];
$sales = [];
while($row = mysqli_fetch_assoc($monthly_sales)) {
    $months[] = $row['month'];
    $sales[] = $row['total'];
}

// Get top selling products
$top_products = mysqli_query($conn, "SELECT p.p_name, p.image, SUM(oi.quantity) as total_sold FROM order_items oi JOIN products p ON oi.prod_id = p.prod_id GROUP BY oi.prod_id ORDER BY total_sold DESC LIMIT 5");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - HomeStyle</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<div class="admin-main-container">
    <div class="admin-dashboard">
        
        <!-- Welcome Section -->
        <div class="welcome-section">
            <div class="welcome-text">
                <h1>Welcome back, <?php echo htmlspecialchars($admin_name); ?>!</h1>
                <p>Here's what's happening with your store today.</p>
            </div>
            <div class="date-time">
                <i class="fas fa-calendar-alt"></i>
                <?php echo date('l, F j, Y'); ?>
            </div>
        </div>
        
        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-box"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo number_format($total_products); ?></h3>
                    <p>Total Products</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo number_format($total_orders); ?></h3>
                    <p>Total Orders</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo number_format($total_users); ?></h3>
                    <p>Total Customers</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-rupee-sign"></i>
                </div>
                <div class="stat-info">
                    <h3>₹<?php echo number_format($total_revenue, 2); ?></h3>
                    <p>Total Revenue</p>
                </div>
            </div>
        </div>
        
        <!-- Charts Section -->
        <div class="charts-row">
            <div class="chart-card">
                <div class="card-header">
                    <h3><i class="fas fa-chart-line"></i> Sales Overview</h3>
                </div>
                <div class="card-body">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
            
            <div class="chart-card">
                <div class="card-header">
                    <h3><i class="fas fa-trophy"></i> Top Selling Products</h3>
                </div>
                <div class="card-body">
                    <div class="top-products-list">
                        <?php while($product = mysqli_fetch_assoc($top_products)): ?>
                        <div class="top-product-item">
                            <img src="images/<?php echo $product['image']; ?>" alt="<?php echo $product['p_name']; ?>">
                            <div class="product-info">
                                <h4><?php echo htmlspecialchars($product['p_name']); ?></h4>
                                <span class="sold-count"><?php echo $product['total_sold']; ?> sold</span>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
        
        
        <div class="data-row">
            <div class="data-card">
                <div class="card-header">
                    <h3><i class="fas fa-clock"></i> Recent Orders</h3>
                    <a href="orders.php" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="card-body">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($order = mysqli_fetch_assoc($recent_orders)): ?>
                            <tr>
                                <td>#<?php echo $order['order_id']; ?></td>
                                <td><?php echo htmlspecialchars($order['name']); ?></td>
                                <td>₹<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $order['status'])); ?>">
                                        <?php echo $order['status']; ?>
                                    </span>
                                </td>
                                <td><?php echo date('d-m-Y', strtotime($order['order_date'])); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="data-card">
                <div class="card-header">
                    <h3><i class="fas fa-exclamation-triangle"></i> Low Stock Alerts</h3>
                    <a href="products.php?filter=lowstock" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="card-body">
                    <div class="low-stock-list">
                        <?php if(mysqli_num_rows($low_stock) > 0): ?>
                            <?php while($product = mysqli_fetch_assoc($low_stock)): ?>
                            <div class="stock-item">
                                <img src="images/<?php echo $product['image']; ?>" alt="<?php echo $product['p_name']; ?>">
                                <div class="stock-info">
                                    <h4><?php echo htmlspecialchars($product['p_name']); ?></h4>
                                    <span class="stock-count <?php echo $product['stock'] == 0 ? 'out' : 'low'; ?>">
                                        <?php echo $product['stock'] == 0 ? 'Out of Stock' : $product['stock'] . ' left'; ?>
                                    </span>
                                </div>
                                <a href="edit_product.php?id=<?php echo $product['prod_id']; ?>" class="stock-action">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="no-data">
                                <i class="fas fa-check-circle"></i>
                                <p>All products have sufficient stock!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</div>

<script>
// Sales Chart
const ctx = document.getElementById('salesChart').getContext('2d');
const salesChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($months); ?>,
        datasets: [{
            label: 'Sales (₹)',
            data: <?php echo json_encode($sales); ?>,
            borderColor: '#ff9900',
            backgroundColor: 'rgba(255, 153, 0, 0.1)',
            borderWidth: 2,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#ff9900',
            pointBorderColor: '#fff',
            pointRadius: 4,
            pointHoverRadius: 6
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '₹' + value.toLocaleString();
                    }
                }
            }
        }
    }
});
</script>

</body>
</html>