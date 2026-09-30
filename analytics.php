<?php
include("db.php");
include("a_navbar.php");

// Get current year and month
$current_year = date('Y');
$current_month = date('m');

// Total Sales
$total_sales = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_amount) as total FROM orders WHERE status != 'Cancelled'"))['total'] ?? 0;

// Total Orders
$total_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders"))['count'];

// Total Products
$total_products = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM products"))['count'];

// Total Customers
$total_customers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE isadmin = 'no'"))['count'];

// Average Order Value
$avg_order = $total_orders > 0 ? $total_sales / $total_orders : 0;

// Monthly Sales (Last 12 months)
$monthly_sales = mysqli_query($conn, "
    SELECT DATE_FORMAT(order_date, '%b') as month, 
           MONTH(order_date) as month_num,
           SUM(total_amount) as total 
    FROM orders 
    WHERE order_date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY MONTH(order_date) 
    ORDER BY order_date ASC
");

$months = [];
$sales_data = [];
while($row = mysqli_fetch_assoc($monthly_sales)) {
    $months[] = $row['month'];
    $sales_data[] = $row['total'];
}

// Category Sales Distribution
$category_sales = mysqli_query($conn, "
    SELECT p.category, SUM(oi.quantity * oi.price_at_time) as total_sales
    FROM order_items oi
    JOIN products p ON oi.prod_id = p.prod_id
    JOIN orders o ON oi.order_id = o.order_id
    WHERE o.status != 'Cancelled'
    GROUP BY p.category
    ORDER BY total_sales DESC
");

$categories = [];
$category_totals = [];
while($row = mysqli_fetch_assoc($category_sales)) {
    $categories[] = $row['category'];
    $category_totals[] = $row['total_sales'];
}

// Top Selling Products
$top_products = mysqli_query($conn, "
    SELECT p.p_name, p.image, SUM(oi.quantity) as total_sold, SUM(oi.quantity * oi.price_at_time) as revenue
    FROM order_items oi
    JOIN products p ON oi.prod_id = p.prod_id
    GROUP BY oi.prod_id
    ORDER BY total_sold DESC
    LIMIT 5
");

// Daily Orders (Last 7 days)
$daily_orders = mysqli_query($conn, "
    SELECT DATE(order_date) as date, COUNT(*) as count, SUM(total_amount) as total
    FROM orders
    WHERE order_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(order_date)
    ORDER BY date ASC
");

$dates = [];
$order_counts = [];
$daily_totals = [];
while($row = mysqli_fetch_assoc($daily_orders)) {
    $dates[] = date('d M', strtotime($row['date']));
    $order_counts[] = $row['count'];
    $daily_totals[] = $row['total'];
}

// Order Status Distribution
$status_counts = mysqli_query($conn, "
    SELECT status, COUNT(*) as count 
    FROM orders 
    GROUP BY status
");

$statuses = [];
$status_counts_data = [];
while($row = mysqli_fetch_assoc($status_counts)) {
    $statuses[] = $row['status'];
    $status_counts_data[] = $row['count'];
}

// Monthly Growth
$growth_data = mysqli_query($conn, "
    SELECT 
        DATE_FORMAT(order_date, '%b') as month,
        SUM(total_amount) as total,
        LAG(SUM(total_amount)) OVER (ORDER BY MONTH(order_date)) as prev_total
    FROM orders
    WHERE order_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY MONTH(order_date)
    ORDER BY order_date ASC
");

$growth_percentages = [];
while($row = mysqli_fetch_assoc($growth_data)) {
    if($row['prev_total'] && $row['prev_total'] > 0) {
        $growth = (($row['total'] - $row['prev_total']) / $row['prev_total']) * 100;
        $growth_percentages[] = round($growth, 1);
    } else {
        $growth_percentages[] = 0;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Analytics - HomeStyle Admin</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<div class="main-container">
    <div class="analytics-page">
        
        <!-- Page Header -->
        <div class="page-header">
            <h2><i class="fas fa-chart-bar"></i> Analytics Dashboard</h2>
            <p>Comprehensive insights into your store's performance</p>
        </div>
        
        <!-- Key Metrics Cards -->
        <div class="metrics-grid">
            <div class="metric-card">
                <div class="metric-icon">
                    <i class="fas fa-rupee-sign"></i>
                </div>
                <div class="metric-info">
                    <h3>₹<?php echo number_format($total_sales, 2); ?></h3>
                    <p>Total Revenue</p>
                    <span class="metric-trend positive">+12.5%</span>
                </div>
            </div>
            
            <div class="metric-card">
                <div class="metric-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="metric-info">
                    <h3><?php echo number_format($total_orders); ?></h3>
                    <p>Total Orders</p>
                    <span class="metric-trend positive">+8.2%</span>
                </div>
            </div>
            
            <div class="metric-card">
                <div class="metric-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="metric-info">
                    <h3><?php echo number_format($total_customers); ?></h3>
                    <p>Total Customers</p>
                    <span class="metric-trend positive">+15.3%</span>
                </div>
            </div>
            
            <div class="metric-card">
                <div class="metric-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="metric-info">
                    <h3>₹<?php echo number_format($avg_order, 2); ?></h3>
                    <p>Average Order Value</p>
                    <span class="metric-trend positive">+5.8%</span>
                </div>
            </div>
        </div>
        
        <!-- Charts Row 1 -->
        <div class="charts-row">
            <div class="chart-card">
                <div class="card-header">
                    <h3><i class="fas fa-chart-line"></i> Monthly Sales Trend</h3>
                    <span class="card-subtitle">Last 12 months</span>
                </div>
                <div class="card-body">
                    <canvas id="salesTrendChart"></canvas>
                </div>
            </div>
            
            <div class="chart-card">
                <div class="card-header">
                    <h3><i class="fas fa-chart-pie"></i> Sales by Category</h3>
                    <span class="card-subtitle">Revenue distribution</span>
                </div>
                <div class="card-body">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Charts Row 2 -->
        <div class="charts-row">
            <div class="chart-card">
                <div class="card-header">
                    <h3><i class="fas fa-calendar-week"></i> Daily Performance</h3>
                    <span class="card-subtitle">Last 7 days</span>
                </div>
                <div class="card-body">
                    <canvas id="dailyChart"></canvas>
                </div>
            </div>
            
            <div class="chart-card">
                <div class="card-header">
                    <h3><i class="fas fa-chart-pie"></i> Order Status Distribution</h3>
                    <span class="card-subtitle">Current order statuses</span>
                </div>
                <div class="card-body">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Top Products Section -->
        <div class="data-card full-width">
            <div class="card-header">
                <h3><i class="fas fa-trophy"></i> Top Selling Products</h3>
                <span class="card-subtitle">Best performing products</span>
            </div>
            <div class="card-body">
                <div class="top-products-grid">
                    <?php while($product = mysqli_fetch_assoc($top_products)): ?>
                    <div class="top-product-card">
                        <div class="top-product-item">
                        <img src="images/<?php echo $product['image']; ?>" alt="<?php echo $product['p_name']; ?>">
                        <div class="product-details">
                            <h4><?php echo htmlspecialchars($product['p_name']); ?></h4>
                            <div class="product-stats">
                                <span><i class="fas fa-chart-line"></i> <?php echo $product['total_sold']; ?> sold</span>
                                <span><i class="fas fa-rupee-sign"></i> <?php echo number_format($product['revenue'], 2); ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
        
    </div>
</div>

<script>
// Sales Trend Chart
const ctx1 = document.getElementById('salesTrendChart').getContext('2d');
new Chart(ctx1, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($months); ?>,
        datasets: [{
            label: 'Sales (₹)',
            data: <?php echo json_encode($sales_data); ?>,
            borderColor: '#ff9900',
            backgroundColor: 'rgba(255, 153, 0, 0.1)',
            borderWidth: 2,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#ff9900',
            pointBorderColor: '#fff',
            pointRadius: 4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
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

// Category Chart
const ctx2 = document.getElementById('categoryChart').getContext('2d');
new Chart(ctx2, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode($categories); ?>,
        datasets: [{
            data: <?php echo json_encode($category_totals); ?>,
            backgroundColor: ['#ff9900', '#2196f3', '#4caf50', '#f44336', '#9c27b0', '#ff5722', '#00bcd4', '#795548', '#607d8b', '#e91e63'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'right',
                labels: { font: { size: 11 } }
            }
        }
    }
});

// Daily Chart
const ctx3 = document.getElementById('dailyChart').getContext('2d');
new Chart(ctx3, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($dates); ?>,
        datasets: [
            {
                label: 'Orders',
                data: <?php echo json_encode($order_counts); ?>,
                backgroundColor: '#ff9900',
                borderRadius: 6,
                yAxisID: 'y'
            },
            {
                label: 'Revenue (₹)',
                data: <?php echo json_encode($daily_totals); ?>,
                backgroundColor: '#2196f3',
                borderRadius: 6,
                yAxisID: 'y1'
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'top' }
        },
        scales: {
            y: {
                beginAtZero: true,
                title: { display: true, text: 'Number of Orders' }
            },
            y1: {
                position: 'right',
                beginAtZero: true,
                title: { display: true, text: 'Revenue (₹)' },
                ticks: {
                    callback: function(value) {
                        return '₹' + value.toLocaleString();
                    }
                }
            }
        }
    }
});

// Status Chart
const ctx4 = document.getElementById('statusChart').getContext('2d');
new Chart(ctx4, {
    type: 'pie',
    data: {
        labels: <?php echo json_encode($statuses); ?>,
        datasets: [{
            data: <?php echo json_encode($status_counts_data); ?>,
            backgroundColor: ['#ff9900', '#2196f3', '#4caf50', '#f44336'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'right',
                labels: { font: { size: 11 } }
            }
        }
    }
});
</script>

</body>
</html>