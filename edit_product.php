<?php
session_start();
include("db.php");

// Admin check BEFORE any output
if(!isset($_SESSION['isadmin']) || $_SESSION['isadmin'] !== 'yes') {
    header("Location: login.php");
    exit();
}

// Check if product ID exists
if (!isset($_GET['id'])) {
    header("Location: admin_dashboard.php");
    exit();
}

$prod_id = $_GET['id'];
$query = "SELECT * FROM products WHERE prod_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $prod_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result) == 0){
    echo "Product not found.";
    exit();
}

$product = mysqli_fetch_assoc($result);

// Process form submission BEFORE any HTML output
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
        
        // Delete old image
        if($product['image'] && file_exists("images/".$product['image'])) {
            unlink("images/".$product['image']);
        }
    } else {
        $image_name = $product['image'];
    }

    $update_query = "UPDATE products SET 
                        p_name=?, category=?, price=?, color=?, material=?, brand=?, description=?, stock=?, image=? 
                     WHERE prod_id=?";
    $stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt, "ssdssssisi", $p_name, $category, $price, $color, $material, $brand, $description, $stock, $image_name, $prod_id);
    
    if(mysqli_stmt_execute($stmt)){
        header("Location: admin_dashboard.php?msg=updated");
        exit();
    } else {
        echo "Update failed: ".mysqli_error($conn);
    }
}

// Include navbar AFTER all redirects
include("a_navbar.php");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Product - HomeStyle Admin</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="admin-main-container">
    <div class="add-product-container">
        <div class="add-product-card">
            <div class="add-product-header">
                <h2>
                    <i class="fas fa-edit"></i>
                    Edit Product
                </h2>
                <p>Update product information</p>
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
                            <input type="text" name="p_name" value="<?php echo htmlspecialchars($product['p_name']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-layer-group"></i> Category <span class="required">*</span></label>
                            <select name="category" required>
                                <option value="">Select Category</option>
                                <?php
                                $categories = ['Bedroom','Kitchen','Bathroom','Electricals','Dining','Living Room','Outdoor','Decor','Office','Kids'];
                                foreach($categories as $cat){
                                    $selected = ($cat == $product['category']) ? 'selected' : '';
                                    echo "<option value='$cat' $selected>$cat</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-rupee-sign"></i> Price <span class="required">*</span></label>
                            <input type="number" step="0.01" name="price" value="<?php echo $product['price']; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-palette"></i> Color <span class="required">*</span></label>
                            <input type="text" name="color" value="<?php echo htmlspecialchars($product['color']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-cube"></i> Material <span class="required">*</span></label>
                            <select name="material" required>
                                <option value="">Select Material</option>
                                <?php
                                $materials = ['Wood','Plastic','Metal','Glass','Fabric','Leather','Marble','Electronic','Other'];
                                foreach($materials as $mat){
                                    $selected = ($mat == $product['material']) ? 'selected' : '';
                                    echo "<option value='$mat' $selected>$mat</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-building"></i> Brand <span class="required">*</span></label>
                            <input type="text" name="brand" value="<?php echo $product['brand']; ?>" required>
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
                        <textarea name="description" rows="5" required><?php echo htmlspecialchars($product['description']); ?></textarea>
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
                            <input type="number" name="stock" value="<?php echo $product['stock']; ?>" required min="0">
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-image"></i> Product Image</label>
                            <div class="current-image">
                                <p>Current Image:</p>
                                <img src="images/<?php echo $product['image']; ?>" alt="Current Product Image" class="current-product-img">
                                <div class="image-upload-area" onclick="document.getElementById('imageInput').click()">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <p>Click to change image (optional)</p>
                                    <small>Supported formats: JPG, PNG, GIF (Max 5MB)</small>
                                    <input type="file" name="image" id="imageInput" accept="image/*" style="display: none;" onchange="previewImage(this)">
                                </div>
                                <div class="image-preview" id="imagePreview">
                                    <img id="previewImg" src="" alt="Preview">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Form Actions -->
                <div class="form-actions">
                    <button type="submit" name="submit" class="btn-submit">
                        <i class="fas fa-save"></i> Update Product
                    </button>
                    <a href="admin_dashboard.php" class="btn-reset">
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