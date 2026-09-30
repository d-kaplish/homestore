<?php
include 'db.php';
include 'navbar.php';
include 'sidebar_filters.php';
include 'sidebar_sort.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>HomeStyle - Your Home Store</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="main-container">

    <!-- Active Filters Bar -->
   
        
        
        
        

    <section class="product-area">
        <div class="product-header">
            <h2>Featured Products</h2>
            <div class="product-count">
                <?php
                // Count products for display
                $count_query = "SELECT COUNT(*) as total FROM products";
                $count_result = mysqli_query($conn, $count_query);
                $total_products = mysqli_fetch_assoc($count_result)['total'];
                echo $total_products . ' Products';
                ?>
            </div>
        </div>
        
        <div class="product-container">
            <?php
            // Build WHERE clause based on filters
            $where = [];
            $params = [];
            $types = "";
            
            // Category filter
            if(isset($_GET['category']) && !empty($_GET['category'])) {
                $categories = array_map(function($cat) use ($conn) {
                    return "'" . mysqli_real_escape_string($conn, $cat) . "'";
                }, $_GET['category']);
                $where[] = "category IN (" . implode(",", $categories) . ")";
            }
            
            // Material filter
            if(isset($_GET['material']) && !empty($_GET['material'])) {
                $materials = array_map(function($mat) use ($conn) {
                    return "'" . mysqli_real_escape_string($conn, $mat) . "'";
                }, $_GET['material']);
                $where[] = "material IN (" . implode(",", $materials) . ")";
            }
            
            // Color filter
            if(isset($_GET['color']) && !empty($_GET['color'])) {
                $colors = array_map(function($col) use ($conn) {
                    return "'" . mysqli_real_escape_string($conn, $col) . "'";
                }, $_GET['color']);
                $where[] = "color IN (" . implode(",", $colors) . ")";
            }
            
            // Brand filter
            if(isset($_GET['brand']) && !empty($_GET['brand'])) {
                $brands = array_map(function($brand) use ($conn) {
                    return "'" . mysqli_real_escape_string($conn, $brand) . "'";
                }, $_GET['brand']);
                $where[] = "brand IN (" . implode(",", $brands) . ")";
            }
            
            // Price range filter
            if(isset($_GET['min_price']) && $_GET['min_price'] != '') {
                $min_price = floatval($_GET['min_price']);
                $where[] = "price >= $min_price";
            }
            if(isset($_GET['max_price']) && $_GET['max_price'] != '') {
                $max_price = floatval($_GET['max_price']);
                $where[] = "price <= $max_price";
            }
            
            // Availability filter
            if(isset($_GET['availability']) && $_GET['availability'] == 'in_stock') {
                $where[] = "stock > 0";
            } elseif(isset($_GET['availability']) && $_GET['availability'] == 'out_of_stock') {
                $where[] = "stock = 0";
            }
            
            // Rating filter
            if(isset($_GET['rating']) && $_GET['rating'] > 0) {
                $rating = intval($_GET['rating']);
                $where[] = "rating >= $rating";
            }
            
            // Build ORDER BY clause
            $order = "";
            if(isset($_GET['sort'])) {
                switch($_GET['sort']){
                    case "price_low_high": 
                        $order = "ORDER BY price ASC"; 
                        break;
                    case "price_high_low": 
                        $order = "ORDER BY price DESC"; 
                        break;
                    case "rating_high_low": 
                        $order = "ORDER BY rating DESC, price ASC"; 
                        break;
                    case "best_selling": 
                        $order = "ORDER BY sold_count DESC, created_at DESC"; 
                        break;
                    case "newest": 
                        $order = "ORDER BY created_at DESC"; 
                        break;
                    default: 
                        $order = "ORDER BY created_at DESC";
                }
            } else {
                $order = "ORDER BY created_at DESC";
            }
            
            // Build final query
            $where_sql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
            $sql = "SELECT * FROM products $where_sql $order LIMIT 50";
            $result = mysqli_query($conn, $sql);
            
            if(mysqli_num_rows($result) > 0) {
                while($row = mysqli_fetch_assoc($result)) {
                    // Check if product is in stock
                    $stock_status = $row['stock'] > 0 ? 'in-stock' : 'out-of-stock';
                    $stock_text = $row['stock'] > 0 ? 'In Stock' : 'Out of Stock';
            ?>
                    <div class="product-card <?php echo $stock_status; ?>">
                        <?php if($row['stock'] == 0): ?>
                            <div class="sold-out-badge">Sold Out</div>
                        <?php endif; ?>
                        <a href="display.php?id=<?php echo $row["prod_id"]; ?>">
                            <img src="images/<?php echo $row["image"]; ?>" alt="<?php echo $row["p_name"]; ?>">
                            <h3><?php echo htmlspecialchars($row["p_name"]); ?></h3>
                        </a>
                        <div class="product-meta">
                            <p class="brand"><?php echo htmlspecialchars($row["brand"]); ?></p>
                            <div class="rating">
                                <?php 
                                $rating = $row['rating'] ?: 0;
                                for($i = 1; $i <= 5; $i++):
                                    if($i <= round($rating)):
                                        echo '<i class="fas fa-star"></i>';
                                    else:
                                        echo '<i class="far fa-star"></i>';
                                    endif;
                                endfor;
                                ?>
                            </div>
                            <p class="price">₹<?php echo number_format($row["price"], 2); ?></p>
                        </div>
                        <a href="add_to_cart.php?id=<?php echo $row["prod_id"]; ?>" class="add-to-cart-btn <?php echo ($row['stock'] == 0) ? 'disabled' : ''; ?>" <?php echo ($row['stock'] == 0) ? 'onclick="return false;"' : ''; ?>>
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </a>
                    </div>
            <?php
                }
            } else {
                echo '<div class="no-products">
                        <i class="fas fa-search fa-4x"></i>
                        <h3>No products found</h3>
                        <p>Try adjusting your filters or search terms</p>
                        <a href="index.php" class="clear-filters-btn">Clear All Filters</a>
                      </div>';
            }
            ?>
        </div>
    </section>

</div>

<script>
// Toggle Filter Panel
const filterBtn = document.getElementById("filterToggle");
const filterSidebar = document.getElementById("filterSidebar");
if(filterBtn) {
    filterBtn.addEventListener("click", function(e){
        e.stopPropagation();
        filterSidebar.classList.toggle("active");
        const sortSidebar = document.getElementById("sortSidebar");
        if(sortSidebar) sortSidebar.classList.remove("active");
    });
}

// Toggle Sort Panel
const sortBtn = document.getElementById("sortToggle");
const sortSidebar = document.getElementById("sortSidebar");
if(sortBtn) {
    sortBtn.addEventListener("click", function(e){
        e.stopPropagation();
        sortSidebar.classList.toggle("active");
        if(filterSidebar) filterSidebar.classList.remove("active");
    });
}

// Close both panels if clicking outside
document.addEventListener("click", function(e){
    if(filterSidebar && !filterSidebar.contains(e.target) && e.target !== filterBtn){
        filterSidebar.classList.remove("active");
    }
    if(sortSidebar && !sortSidebar.contains(e.target) && e.target !== sortBtn){
        sortSidebar.classList.remove("active");
    }
});
</script>

<?php include 'footer.php'; ?>
</body>
</html>