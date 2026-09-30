<?php
session_start();
include("db.php");
include("a_navbar.php");

if(!isset($_SESSION['isadmin']) || $_SESSION['isadmin'] !== 'yes') {
    header("Location: login.php");
    exit();
}

// Delete review
if(isset($_GET['delete'])) {
    $review_id = intval($_GET['delete']);
    $delete_query = "DELETE FROM reviews WHERE review_id = ?";
    $stmt = mysqli_prepare($conn, $delete_query);
    mysqli_stmt_bind_param($stmt, "i", $review_id);
    
    if(mysqli_stmt_execute($stmt)) {
        // Update product rating after deletion
        $update_rating = "UPDATE products p 
                          SET p.rating = (SELECT COALESCE(AVG(rating), 0) FROM reviews WHERE prod_id = p.prod_id)";
        mysqli_query($conn, $update_rating);
        
        header("Location: reviews.php?msg=deleted");
        exit();
    }
}

// Approve review (if status column exists, otherwise just show all)
if(isset($_GET['approve']) && column_exists($conn, 'reviews', 'status')) {
    $review_id = intval($_GET['approve']);
    $approve_query = "UPDATE reviews SET status = 'approved' WHERE review_id = ?";
    $stmt = mysqli_prepare($conn, $approve_query);
    mysqli_stmt_bind_param($stmt, "i", $review_id);
    mysqli_stmt_execute($stmt);
    header("Location: reviews.php?msg=approved");
    exit();
}

// Function to check if column exists
function column_exists($conn, $table, $column) {
    $result = mysqli_query($conn, "SHOW COLUMNS FROM $table LIKE '$column'");
    return mysqli_num_rows($result) > 0;
}

// Get filter parameters
$status_filter = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';
$rating_filter = isset($_GET['rating']) ? intval($_GET['rating']) : 0;
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// Build WHERE clause
$where = [];
if($status_filter && column_exists($conn, 'reviews', 'status')) {
    $where[] = "r.status = '$status_filter'";
}
if($rating_filter > 0) {
    $where[] = "r.rating = $rating_filter";
}
if($search) {
    $where[] = "(u.name LIKE '%$search%' OR p.p_name LIKE '%$search%' OR r.comment LIKE '%$search%')";
}

$where_sql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// Fetch reviews with product and user info
$query = "
    SELECT r.*, u.name as user_name, u.email, p.p_name, p.image, p.prod_id
    FROM reviews r
    JOIN users u ON r.user_id = u.userid
    JOIN products p ON r.prod_id = p.prod_id
    $where_sql
    ORDER BY r.created_at DESC
";
$reviews = mysqli_query($conn, $query);

// Get statistics
$total_reviews = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM reviews"))['count'];
$avg_rating = mysqli_fetch_assoc(mysqli_query($conn, "SELECT AVG(rating) as avg FROM reviews"))['avg'];
$total_5star = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM reviews WHERE rating = 5"))['count'];
$total_4star = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM reviews WHERE rating = 4"))['count'];
$total_3star = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM reviews WHERE rating = 3"))['count'];
$total_2star = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM reviews WHERE rating = 2"))['count'];
$total_1star = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM reviews WHERE rating = 1"))['count'];

// Check if status column exists for pending count
$has_status = column_exists($conn, 'reviews', 'status');
$pending_count = 0;
if($has_status) {
    $pending_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM reviews WHERE status = 'pending'"))['count'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Reviews - HomeStyle Admin</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="admin-main-container">
    <div class="reviews-page">
        
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-left">
                <h2><i class="fas fa-star"></i> Customer Reviews</h2>
                <p>Manage and moderate customer product reviews</p>
            </div>
        </div>
        
        <?php if(isset($_GET['msg'])): ?>
            <div class="alert alert-success">
                <?php 
                if($_GET['msg'] == 'deleted') echo "Review deleted successfully!";
                if($_GET['msg'] == 'approved') echo "Review approved successfully!";
                ?>
            </div>
        <?php endif; ?>
        
        <!-- Statistics Cards -->
        <div class="reviews-stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-comments"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo number_format($total_reviews); ?></h3>
                    <p>Total Reviews</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-star"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo number_format($avg_rating, 1); ?></h3>
                    <p>Average Rating</p>
                    <div class="mini-stars">
                        <?php for($i = 1; $i <= 5; $i++): ?>
                            <?php if($i <= round($avg_rating)): ?>
                                <i class="fas fa-star"></i>
                            <?php else: ?>
                                <i class="far fa-star"></i>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-star-half-alt"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $total_5star; ?></h3>
                    <p>5 Star Reviews</p>
                </div>
            </div>
            
            <?php if($has_status && $pending_count > 0): ?>
            <div class="stat-card warning">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $pending_count; ?></h3>
                    <p>Pending Approval</p>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Rating Distribution -->
        <div class="rating-distribution-card">
            <h3><i class="fas fa-chart-bar"></i> Rating Distribution</h3>
            <div class="distribution-bars">
                <div class="distribution-item">
                    <span class="rating-label">5 Star</span>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo $total_reviews > 0 ? ($total_5star / $total_reviews) * 100 : 0; ?>%; background: #4caf50;"></div>
                    </div>
                    <span class="rating-count"><?php echo $total_5star; ?></span>
                </div>
                <div class="distribution-item">
                    <span class="rating-label">4 Star</span>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo $total_reviews > 0 ? ($total_4star / $total_reviews) * 100 : 0; ?>%; background: #8bc34a;"></div>
                    </div>
                    <span class="rating-count"><?php echo $total_4star; ?></span>
                </div>
                <div class="distribution-item">
                    <span class="rating-label">3 Star</span>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo $total_reviews > 0 ? ($total_3star / $total_reviews) * 100 : 0; ?>%; background: #ffc107;"></div>
                    </div>
                    <span class="rating-count"><?php echo $total_3star; ?></span>
                </div>
                <div class="distribution-item">
                    <span class="rating-label">2 Star</span>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo $total_reviews > 0 ? ($total_2star / $total_reviews) * 100 : 0; ?>%; background: #ff9800;"></div>
                    </div>
                    <span class="rating-count"><?php echo $total_2star; ?></span>
                </div>
                <div class="distribution-item">
                    <span class="rating-label">1 Star</span>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo $total_reviews > 0 ? ($total_1star / $total_reviews) * 100 : 0; ?>%; background: #f44336;"></div>
                    </div>
                    <span class="rating-count"><?php echo $total_1star; ?></span>
                </div>
            </div>
        </div>
        
        <!-- Filter Bar -->
        <div class="reviews-filter-bar">
            <form method="GET" action="reviews.php" class="filter-form">
                <div class="search-group">
                    <input type="text" name="search" placeholder="Search by customer, product, or review..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </div>
                
                <select name="rating" onchange="this.form.submit()">
                    <option value="0">All Ratings</option>
                    <option value="5" <?php echo $rating_filter == 5 ? 'selected' : ''; ?>>5 Stars ★★★★★</option>
                    <option value="4" <?php echo $rating_filter == 4 ? 'selected' : ''; ?>>4 Stars ★★★★☆</option>
                    <option value="3" <?php echo $rating_filter == 3 ? 'selected' : ''; ?>>3 Stars ★★★☆☆</option>
                    <option value="2" <?php echo $rating_filter == 2 ? 'selected' : ''; ?>>2 Stars ★★☆☆☆</option>
                    <option value="1" <?php echo $rating_filter == 1 ? 'selected' : ''; ?>>1 Star ★☆☆☆☆</option>
                </select>
                
                <?php if($has_status): ?>
                <select name="status" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="approved" <?php echo $status_filter == 'approved' ? 'selected' : ''; ?>>Approved</option>
                    <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                </select>
                <?php endif; ?>
                
                <?php if($search || $rating_filter > 0 || $status_filter): ?>
                    <a href="reviews.php" class="clear-filter">Clear Filters</a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Reviews List -->
        <div class="reviews-list-container">
            <?php if(mysqli_num_rows($reviews) > 0): ?>
                <?php while($review = mysqli_fetch_assoc($reviews)): ?>
                    <div class="review-card <?php echo ($has_status && $review['status'] == 'pending') ? 'pending' : ''; ?>">
                        <div class="review-header">
                            <div class="reviewer-info">
                                <div class="reviewer-avatar">
                                    <i class="fas fa-user-circle"></i>
                                </div>
                                <div class="reviewer-details">
                                    <h4><?php echo htmlspecialchars($review['user_name']); ?></h4>
                                    <span class="reviewer-email"><?php echo htmlspecialchars($review['email']); ?></span>
                                </div>
                            </div>
                            <div class="review-rating">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <?php if($i <= $review['rating']): ?>
                                        <i class="fas fa-star"></i>
                                    <?php else: ?>
                                        <i class="far fa-star"></i>
                                    <?php endif; ?>
                                <?php endfor; ?>
                                <span class="rating-value">(<?php echo $review['rating']; ?>/5)</span>
                            </div>
                        </div>
                        
                        <div class="review-product">
                            <a href="display.php?id=<?php echo $review['prod_id']; ?>" target="_blank">
                                <img src="images/<?php echo $review['image']; ?>" alt="<?php echo $review['p_name']; ?>">
                                <span><?php echo htmlspecialchars($review['p_name']); ?></span>
                            </a>
                        </div>
                        
                        <div class="review-content">
                            <p><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                        </div>
                        
                        <div class="review-footer">
                            <div class="review-date">
                                <i class="far fa-calendar-alt"></i>
                                <?php echo date('d M Y, h:i A', strtotime($review['created_at'])); ?>
                            </div>
                            <div class="review-actions">
                                <?php if($has_status && $review['status'] == 'pending'): ?>
                                    <a href="reviews.php?approve=<?php echo $review['review_id']; ?>" class="approve-btn" onclick="return confirm('Approve this review?')">
                                        <i class="fas fa-check-circle"></i> Approve
                                    </a>
                                <?php endif; ?>
                                <a href="reviews.php?delete=<?php echo $review['review_id']; ?>" class="delete-review-btn" onclick="return confirm('Delete this review permanently?')">
                                    <i class="fas fa-trash-alt"></i> Delete
                                </a>
                            </div>
                        </div>
                        
                        <?php if($has_status && $review['status'] == 'pending'): ?>
                            <div class="pending-badge">Pending Approval</div>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-reviews">
                    <i class="fas fa-star-half-alt fa-4x"></i>
                    <h3>No Reviews Found</h3>
                    <p>No customer reviews match your filters</p>
                    <a href="reviews.php" class="clear-filters-btn">Clear Filters</a>
                </div>
            <?php endif; ?>
        </div>
        
    </div>
</div>

</body>
</html>