<?php
include("db.php");
include("a_navbar.php");

// Add category
if(isset($_POST['add_category'])) {
    $category_name = mysqli_real_escape_string($conn, $_POST['category_name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    
    $check = mysqli_query($conn, "SELECT * FROM categories WHERE name = '$category_name'");
    if(mysqli_num_rows($check) == 0) {
        $insert = "INSERT INTO categories (name, description) VALUES ('$category_name', '$description')";
        if(mysqli_query($conn, $insert)) {
            $success = "Category added successfully!";
        } else {
            $error = "Failed to add category!";
        }
    } else {
        $error = "Category already exists!";
    }
}

// Delete category
if(isset($_GET['delete'])) {
    $cat_id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM categories WHERE id = $cat_id");
    header("Location: categories.php");
    exit();
}

// Get all categories
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name ASC");

// Get product count per category
$product_counts = mysqli_query($conn, "
    SELECT category, COUNT(*) as count 
    FROM products 
    GROUP BY category
");
$counts = [];
while($row = mysqli_fetch_assoc($product_counts)) {
    $counts[$row['category']] = $row['count'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Categories - HomeStyle Admin</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="admin-main-container">
    <div class="categories-page">
        
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-left">
                <h2><i class="fas fa-tags"></i> Category Management</h2>
                <p>Organize your products with categories</p>
            </div>
        </div>
        
        <?php if(isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="categories-layout">
            <!-- Add Category Form -->
            <div class="add-category-card">
                <h3><i class="fas fa-plus-circle"></i> Add New Category</h3>
                <form method="POST" class="category-form">
                    <div class="form-group">
                        <label>Category Name</label>
                        <input type="text" name="category_name" placeholder="e.g., Electronics, Furniture" required>
                    </div>
                    <div class="form-group">
                        <label>Description (Optional)</label>
                        <textarea name="description" placeholder="Brief description of this category" rows="3"></textarea>
                    </div>
                    <button type="submit" name="add_category" class="btn-primary">Add Category</button>
                </form>
            </div>
            
            <!-- Categories List -->
            <div class="categories-list-card">
                <h3><i class="fas fa-list"></i> All Categories</h3>
                <div class="categories-grid">
                    <?php 
                    $default_categories = ['Bedroom', 'Kitchen', 'Bathroom', 'Electricals', 'Dining', 'Living Room', 'Outdoor', 'Decor', 'Office', 'Kids'];
                    
                    // Merge default categories with custom ones
                    $all_categories = [];
                    while($cat = mysqli_fetch_assoc($categories)) {
                        $all_categories[] = $cat['name'];
                    }
                    $all_categories = array_unique(array_merge($default_categories, $all_categories));
                    sort($all_categories);
                    ?>
                    
                    <?php foreach($all_categories as $cat): ?>
                    <div class="category-card">
                        <div class="category-icon">
                            <i class="fas fa-folder"></i>
                        </div>
                        <div class="category-info">
                            <h4><?php echo htmlspecialchars($cat); ?></h4>
                            <span class="product-count">
                                <?php echo isset($counts[$cat]) ? $counts[$cat] : 0; ?> products
                            </span>
                        </div>
                        <div class="category-actions">
                            <a href="products.php?category=<?php echo urlencode($cat); ?>" class="view-products">
                                <i class="fas fa-eye"></i>
                            </a>
                            <?php if(!in_array($cat, $default_categories)): ?>
                            <a href="categories.php?delete=<?php echo $cat; ?>" class="delete-category" onclick="return confirm('Delete this category? Products will not be deleted.')">
                                <i class="fas fa-trash"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
    </div>
</div>

</body>
</html>