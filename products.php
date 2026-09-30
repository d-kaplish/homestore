<?php
include("db.php");

// Initialize search variable FIRST
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$category_filter = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : '';
$stock_filter = isset($_GET['stock']) ? mysqli_real_escape_string($conn, $_GET['stock']) : '';

// Delete product
if(isset($_GET['delete'])) {
    $prod_id = intval($_GET['delete']);
    
    $img_query = mysqli_query($conn, "SELECT image FROM products WHERE prod_id = $prod_id");
    $img_data = mysqli_fetch_assoc($img_query);
    if($img_data && $img_data['image'] && file_exists("images/" . $img_data['image'])) {
        unlink("images/" . $img_data['image']);
    }
    
    mysqli_query($conn, "DELETE FROM products WHERE prod_id = $prod_id");
    header("Location: products.php?msg=deleted");
    exit();
}

// Build WHERE clause for filters
$where = [];
if($search) {
    $where[] = "(p_name LIKE '%$search%' OR brand LIKE '%$search%')";
}
if($category_filter) {
    $where[] = "category = '$category_filter'";
}
if($stock_filter == 'low') {
    $where[] = "stock < 10 AND stock > 0";
} elseif($stock_filter == 'out') {
    $where[] = "stock = 0";
} elseif($stock_filter == 'in') {
    $where[] = "stock > 0";
}

$where_sql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// Get products
$products = mysqli_query($conn, "SELECT * FROM products $where_sql ORDER BY created_at DESC");

// Get categories for filter
$categories = mysqli_query($conn, "SELECT DISTINCT category FROM products ORDER BY category");

include("a_navbar.php");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Products - HomeStyle Admin</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="admin-main-container">
    <div class="products-page">
        
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-left">
                <h2><i class="fas fa-boxes"></i> Product Management</h2>
                <p>Manage your product inventory</p>
            </div>
            <div class="header-right">
                <a href="add_product.php" class="btn-primary">
                    <i class="fas fa-plus"></i> Add New Product
                </a>
            </div>
        </div>
        
        <?php if(isset($_GET['msg'])): ?>
            <div class="alert alert-success">
                <?php 
                if($_GET['msg'] == 'deleted') echo "Product deleted successfully!";
                if($_GET['msg'] == 'updated') echo "Product updated successfully!";
                if($_GET['msg'] == 'added') echo "Product added successfully!";
                ?>
            </div>
        <?php endif; ?>
        
        <!-- Filter Bar -->
        <div class="filter-bar">
            <form method="GET" action="products.php" class="filter-form">
                <div class="search-group">
                    <input type="text" name="search" placeholder="Search products by name or brand..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </div>
                
                <select name="category" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    <?php 
                    mysqli_data_seek($categories, 0);
                    while($cat = mysqli_fetch_assoc($categories)): ?>
                        <option value="<?php echo $cat['category']; ?>" <?php echo $category_filter == $cat['category'] ? 'selected' : ''; ?>>
                            <?php echo $cat['category']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                
                <select name="stock" onchange="this.form.submit()">
                    <option value="">All Stock</option>
                    <option value="in" <?php echo $stock_filter == 'in' ? 'selected' : ''; ?>>In Stock</option>
                    <option value="low" <?php echo $stock_filter == 'low' ? 'selected' : ''; ?>>Low Stock (&lt;10)</option>
                    <option value="out" <?php echo $stock_filter == 'out' ? 'selected' : ''; ?>>Out of Stock</option>
                </select>
                
                <?php if($search || $category_filter || $stock_filter): ?>
                    <a href="products.php" class="clear-filter">Clear Filters</a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Products Table -->
        <div class="table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Rating</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($products) > 0): ?>
                        <?php while($product = mysqli_fetch_assoc($products)): ?>
                        <tr>
                            <td>#<?php echo $product['prod_id']; ?></td>
                            <td>
                                <img src="images/<?php echo $product['image']; ?>" alt="<?php echo $product['p_name']; ?>" class="product-thumb">
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($product['p_name']); ?></strong>
                                <br>
                                <small><?php echo htmlspecialchars($product['brand']); ?></small>
                            </td>
                            <td><?php echo $product['category']; ?></td>
                            <td class="price">₹<?php echo number_format($product['price'], 2); ?></td>
                            <td>
                                <span class="stock-badge stock-<?php echo $product['stock'] == 0 ? 'out' : ($product['stock'] < 10 ? 'low' : 'in'); ?>">
                                    <?php echo $product['stock']; ?> units
                                </span>
                            </td>
                            <td>
                                <div class="rating-mini">
                                    <?php 
                                    $rating = $product['rating'] ?: 0;
                                    for($i = 1; $i <= 5; $i++):
                                        echo $i <= $rating ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                                    endfor;
                                    ?>
                                </div>
                            </td>
                            <td class="actions">
                                <a href="edit_product.php?id=<?php echo $product['prod_id']; ?>" class="btn-edit" title="Edit Product">
                                    <i class="fas fa-edit"></i>
                                </a>
                                
                                <a href="products.php?delete=<?php echo $product['prod_id']; ?>" class="btn-delete" onclick="return confirm('Delete this product?')" title="Delete Product">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="no-data">
                                <i class="fas fa-box-open"></i>
                                <p>No products found</p>
                                <?php if($search || $category_filter || $stock_filter): ?>
                                    <a href="products.php" class="clear-filters-link">Clear Filters</a>
                                <?php else: ?>
                                    <a href="add_product.php" class="btn-primary btn-sm">Add your first product</a>
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