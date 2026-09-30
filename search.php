<?php
include 'db.php';
include 'navbar.php';

$search_term = isset($_GET['q']) ? mysqli_real_escape_string($conn, $_GET['q']) : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Search Results - HomeStyle</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="main-container">
    <div class="search-header">
        <h2>Search Results for: "<?php echo htmlspecialchars($search_term); ?>"</h2>
        
        <div class="search-box-large">
            <form method="GET" action="search.php" class="search-form">
                <input type="text" name="q" placeholder="Search products..." value="<?php echo htmlspecialchars($search_term); ?>">
                <button type="submit"><i class="fas fa-search"></i> Search</button>
            </form>
        </div>
    </div>
    
    <div class="product-area">
        <div class="product-container">
            <?php
            $sql = "SELECT * FROM products WHERE p_name LIKE '%$search_term%' 
                    OR brand LIKE '%$search_term%' 
                    OR description LIKE '%$search_term%'";
            
            if($category) {
                $sql .= " AND category = '$category'";
            }
            
            $sql .= " ORDER BY created_at DESC";
            $result = mysqli_query($conn, $sql);
            
            if(mysqli_num_rows($result) > 0) {
                while($row = mysqli_fetch_assoc($result)) {
                    ?>
                    <div class="product-card">
                        <a href="display.php?id=<?php echo $row['prod_id']; ?>">
                            <img src="images/<?php echo $row['image']; ?>" alt="<?php echo $row['p_name']; ?>">
                            <h3><?php echo htmlspecialchars($row['p_name']); ?></h3>
                        </a>
                        <p><?php echo $row['brand']; ?></p>
                        <p class="price">₹<?php echo number_format($row['price'], 2); ?></p>
                        <a href="add_to_cart.php?id=<?php echo $row['prod_id']; ?>">
                            <button><i class="fas fa-shopping-cart"></i> Add to Cart</button>
                        </a>
                    </div>
                    <?php
                }
            } else {
                echo '<div class="no-results">
                        <i class="fas fa-search fa-3x"></i>
                        <p>No products found matching your search.</p>
                        <a href="index.php" class="btn-primary">Browse All Products</a>
                      </div>';
            }
            ?>
        </div>
    </div>
</div>
</body>
</html>