<?php
session_start();  // Add this at the very top
include("db.php");

// Check admin access
if(!isset($_SESSION['isadmin']) || $_SESSION['isadmin'] !== 'yes') {
    header("Location: login.php");
    exit();
}

// Process form submission FIRST (before any HTML output)
if(isset($_POST['submit'])) {
    $p_name = $_POST['p_name'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $color = $_POST['color'];
    $material = $_POST['material'];
    $brand = $_POST['brand'];
    $description = $_POST['description'];
    $stock = $_POST['stock'];

    if(isset($_FILES['image']) && $_FILES['image']['name'] != '') {
        $image_name = time().'_'.$_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], "images/".$image_name);
    } else {
        $image_name = '';
    }

    $insert_query = "INSERT INTO products (p_name, category, price, color, material, brand, description, stock, image)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $insert_query);
    mysqli_stmt_bind_param($stmt, "ssdssssis", $p_name, $category, $price, $color, $material, $brand, $description, $stock, $image_name);
    
    if(mysqli_stmt_execute($stmt)){
        header("Location: products.php?msg=added");
        exit();
    } else {
        echo "Insert failed: ".mysqli_error($conn);
    }
}

// ONLY AFTER all redirects are done, include the navbar
include("a_navbar.php");
?>


<html>
<head>
    <title>Add Product - HomeStyle Admin</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="admin-main-container">
    <div class="add-product-container">
        <div class="add-product-card">
            <div class="add-product-header">
                <h2>
                    <i class="fas fa-plus-circle"></i>
                    Add New Product
                </h2>
                <p>Fill in the details to add a new product to your store</p>
            </div>
            
            <form class="add-product-form" method="POST" enctype="multipart/form-data">
                <!-- Basic Information Section -->
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="fas fa-info-circle"></i>
                        Basic Information
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label><i class="fas fa-tag"></i> Product Name <span class="required">*</span></label>
                            <input type="text" name="p_name" placeholder="Enter product name" required>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-layer-group"></i> Category <span class="required">*</span></label>
                            <select name="category" required>
                                <option value="">Select Category</option>
                                <?php
                                $categories = ['Bedroom','Kitchen','Bathroom','Electricals','Dining','Living Room','Outdoor','Decor','Office','Kids'];
                                foreach($categories as $cat){
                                    echo "<option value='$cat'>$cat</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-rupee-sign"></i> Price <span class="required">*</span></label>
                            <input type="number" step="0.01" name="price" placeholder="Enter price" required>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-palette"></i> Color <span class="required">*</span></label>
                            <input type="text" name="color" placeholder="e.g., Red, Blue, Black" required>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-cube"></i> Material <span class="required">*</span></label>
                            <select name="material" required>
                                <option value="">Select Material</option>
                                <?php
                                $materials = ['Wood','Plastic','Metal','Glass','Fabric','Leather','Marble','Electronic','Other'];
                                foreach($materials as $mat){
                                    echo "<option value='$mat'>$mat</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-building"></i> Brand <span class="required">*</span></label>
                            <input type="text" name="brand" placeholder="Enter brand name" required>
                        </div>
                    </div>
                </div>
                
                <!-- Description Section -->
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="fas fa-align-left"></i>
                        Product Description
                    </div>
                    <div class="form-group form-group-full">
                        <textarea name="description" placeholder="Describe your product features, specifications, benefits..." required></textarea>
                    </div>
                </div>
                
                <!-- Inventory Section -->
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="fas fa-boxes"></i>
                        Inventory
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label><i class="fas fa-box"></i> Stock Quantity <span class="required">*</span></label>
                            <input type="number" name="stock" placeholder="Number of units available" required min="0">
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-image"></i> Product Image <span class="required">*</span></label>
                            <div class="image-upload-area" onclick="document.getElementById('imageInput').click()">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <p>Click to upload product image</p>
                                <small>Supported formats: JPG, PNG, GIF (Max 5MB)</small>
                                <input type="file" name="image" id="imageInput" accept="image/*" style="display: none;" required onchange="previewImage(this)">
                            </div>
                            <div class="image-preview" id="imagePreview">
                                <img id="previewImg" src="" alt="Preview">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Form Actions -->
                <div class="form-actions">
                    <button type="submit" name="submit" class="btn-submit">
                        <i class="fas fa-save"></i> Save Product
                    </button>
                    <button type="reset" class="btn-reset">
                        <i class="fas fa-undo"></i> Reset
                    </button>
                    <a href="admin_dashboard.php" class="btn-reset" style="text-decoration: none;">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function previewImage(input) {
    const preview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    
    if(input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.style.display = 'block';
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

</body>
</html>