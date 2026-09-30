<?php
include 'db.php';
include 'navbar.php';

$id = intval($_GET['id']);
$sql = "SELECT * FROM products WHERE prod_id=$id";
$result = mysqli_query($conn,$sql);
$row = mysqli_fetch_assoc($result);

if(!$row) {
    header("Location: index.php");
    exit();
}

// Handle review submission
if(isset($_POST['submit_review'])) {
    if(!isset($_SESSION['user_id'])) {
        echo "<script>alert('Please login to submit a review'); window.location='login.php';</script>";
        exit();
    }
    
    $user_id = $_SESSION['user_id'];
    $prod_id = intval($_POST['prod_id']);
    $rating = intval($_POST['rating']);
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);
    
    // Check if user has purchased this product (or is admin)
    $check_purchase = "SELECT * FROM order_items oi 
                       JOIN orders o ON oi.order_id = o.order_id 
                       WHERE o.user_id = ? AND oi.prod_id = ? AND o.status = 'Delivered'";
    $stmt = mysqli_prepare($conn, $check_purchase);
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $prod_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    
    if(mysqli_stmt_num_rows($stmt) == 0 && (!isset($_SESSION['isadmin']) || $_SESSION['isadmin'] !== 'yes')) {
        echo "<script>alert('You can only review products you have purchased and received!');</script>";
    } else {
        // Check if already reviewed
        $check_review = "SELECT * FROM reviews WHERE user_id = ? AND prod_id = ?";
        $stmt = mysqli_prepare($conn, $check_review);
        mysqli_stmt_bind_param($stmt, "ii", $user_id, $prod_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        
        if(mysqli_stmt_num_rows($stmt) > 0) {
            // Update existing review
            $update_review = "UPDATE reviews SET rating = ?, comment = ?, created_at = NOW() WHERE user_id = ? AND prod_id = ?";
            $stmt = mysqli_prepare($conn, $update_review);
            mysqli_stmt_bind_param($stmt, "isii", $rating, $comment, $user_id, $prod_id);
        } else {
            // Insert new review
            $insert_review = "INSERT INTO reviews (user_id, prod_id, rating, comment) VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $insert_review);
            mysqli_stmt_bind_param($stmt, "iiis", $user_id, $prod_id, $rating, $comment);
        }
        
        if(mysqli_stmt_execute($stmt)) {
            // Update average rating in products table
            $update_avg = "UPDATE products p 
                           SET p.rating = (SELECT AVG(rating) FROM reviews WHERE prod_id = ?) 
                           WHERE p.prod_id = ?";
            $stmt = mysqli_prepare($conn, $update_avg);
            mysqli_stmt_bind_param($stmt, "ii", $prod_id, $prod_id);
            mysqli_stmt_execute($stmt);
            
            echo "<script>alert('Review submitted successfully!'); window.location='display.php?id=$prod_id';</script>";
            exit();
        } else {
            echo "<script>alert('Failed to submit review!');</script>";
        }
    }
}

// Fetch reviews
$review_query = "SELECT r.*, u.name FROM reviews r 
                 JOIN users u ON r.user_id = u.userid 
                 WHERE r.prod_id = $id ORDER BY r.created_at DESC";
$reviews = mysqli_query($conn, $review_query);

// Fetch variants
$variant_query = "SELECT * FROM product_variants WHERE prod_id = $id AND stock > 0";
$variants = mysqli_query($conn, $variant_query);
?>

<!DOCTYPE html>
<html>
<head>
<title><?php echo htmlspecialchars($row['p_name']); ?> - HomeStyle</title>
<link rel="stylesheet" href="styles.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>

<div class="main-container">

<div class="product-display">

<div class="image-section">
    <img id="mainImage" src="images/<?php echo $row['image']; ?>" alt="<?php echo htmlspecialchars($row['p_name']); ?>">
</div>

<div class="details-section">

<h2><?php echo htmlspecialchars($row['p_name']); ?></h2>

<p class="brand"><i class="fas fa-tag"></i> <?php echo htmlspecialchars($row['brand']); ?></p>

<div class="rating-display">
    <?php 
    $rating = $row['rating'] ?: 0;
    $full_stars = floor($rating);
    $half_star = ($rating - $full_stars) >= 0.5;
    for($i = 1; $i <= 5; $i++):
    ?>
        <?php if($i <= $full_stars): ?>
            <i class="fas fa-star" style="color: #ffc107;"></i>
        <?php elseif($half_star && $i == $full_stars + 1): ?>
            <i class="fas fa-star-half-alt" style="color: #ffc107;"></i>
        <?php else: ?>
            <i class="far fa-star" style="color: #ffc107;"></i>
        <?php endif; ?>
    <?php endfor; ?>
    <span class="rating-count">(<?php echo number_format($rating, 1); ?> out of 5)</span>
</div>

<p class="price">
    <i class="fa-solid fa-indian-rupee-sign"></i> <?php echo number_format($row['price'], 2); ?>
</p>

<p><i class="fas fa-info-circle"></i> <?php echo htmlspecialchars($row['description']); ?></p>

<p><i class="fas fa-palette"></i> <strong>Color:</strong> <?php echo htmlspecialchars($row['color']); ?></p>
<p><i class="fas fa-cube"></i> <strong>Material:</strong> <?php echo htmlspecialchars($row['material']); ?></p>
<p><i class="fas fa-boxes"></i> <strong>Stock:</strong> <?php echo $row['stock']; ?> units</p>

<!-- Variants Section -->
<?php if(mysqli_num_rows($variants) > 0): ?>
<div class="variants-section">
    <h3><i class="fas fa-layer-group"></i> Available Variants:</h3>
    <div class="variant-options" id="variantOptions">
        <div class="variant-option standard-option" onclick="selectVariant(0, 0, 'Standard', 'Default')" data-variant-id="0" data-additional="0" data-size="Standard" data-color="Default">
            <strong>Standard</strong>
            <span class="variant-price">₹<?php echo number_format($row['price'], 2); ?></span>
        </div>
        <?php while($variant = mysqli_fetch_assoc($variants)): ?>
        <div class="variant-option" onclick="selectVariant(<?php echo $variant['variant_id']; ?>, <?php echo $variant['additional_price']; ?>, '<?php echo $variant['size']; ?>', '<?php echo $variant['color']; ?>')" 
             data-variant-id="<?php echo $variant['variant_id']; ?>" 
             data-additional="<?php echo $variant['additional_price']; ?>"
             data-size="<?php echo $variant['size']; ?>"
             data-color="<?php echo $variant['color']; ?>">
            <strong><?php echo $variant['size']; ?></strong> - <?php echo $variant['color']; ?>
            <span class="variant-price">+₹<?php echo number_format($variant['additional_price'], 2); ?></span>
        </div>
        <?php endwhile; ?>
    </div>
    <input type="hidden" id="selected_variant" value="0">
</div>
<?php endif; ?>

<div class="action-buttons">
    <a href="add_to_cart.php?id=<?php echo $row['prod_id']; ?>" id="addToCartLink">
        <button><i class="fas fa-shopping-cart"></i> Add to Cart</button>
    </a>

    <a href="add_to_wishlist.php?id=<?php echo $row['prod_id']; ?>">
        <button class="like-btn" onclick="toggleLike(this)">
            <i class="fa-regular fa-heart"></i>
        </button>
    </a>
</div>

</div>

</div>

<!-- Reviews Section -->
<div class="reviews-section">
    <h3><i class="fas fa-star"></i> Customer Reviews</h3>
    
    <?php if(isset($_SESSION['user_id'])): ?>
        <!-- Add Review Form -->
        <div class="add-review">
            <h4>Write a Review</h4>
            <form method="POST" action="display.php?id=<?php echo $id; ?>">
                <input type="hidden" name="prod_id" value="<?php echo $id; ?>">
                <div class="rating-input">
                    <label>Rating:</label>
                    <select name="rating" required>
                        <option value="5">5 Stars - Excellent</option>
                        <option value="4">4 Stars - Good</option>
                        <option value="3">3 Stars - Average</option>
                        <option value="2">2 Stars - Poor</option>
                        <option value="1">1 Star - Very Poor</option>
                    </select>
                </div>
                <textarea name="comment" placeholder="Share your experience with this product..." rows="4" required></textarea>
                <button type="submit" name="submit_review">Submit Review</button>
            </form>
        </div>
    <?php else: ?>
        <div class="login-to-review">
            <p><a href="login.php">Login</a> to write a review</p>
        </div>
    <?php endif; ?>
    
    <!-- Display Reviews -->
    <div class="reviews-list">
        <?php if(mysqli_num_rows($reviews) > 0): ?>
            <?php while($review = mysqli_fetch_assoc($reviews)): ?>
            <div class="review-item">
                <div class="review-header">
                    <strong><?php echo htmlspecialchars($review['name']); ?></strong>
                    <span class="review-rating">
                        <?php for($i = 1; $i <= 5; $i++): ?>
                            <?php if($i <= $review['rating']): ?>
                                <i class="fas fa-star"></i>
                            <?php else: ?>
                                <i class="far fa-star"></i>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </span>
                    <span class="review-date"><?php echo date('d-m-Y', strtotime($review['created_at'])); ?></span>
                </div>
                <p><?php echo htmlspecialchars($review['comment']); ?></p>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="no-reviews">No reviews yet. Be the first to review this product!</p>
        <?php endif; ?>
    </div>
</div>

</div>

<script>
let selectedVariantId = 0;
let selectedSize = 'Standard';
let selectedColor = 'Default';
let additionalPrice = 0;

function selectVariant(variantId, addPrice, size, color) {
    selectedVariantId = variantId;
    selectedSize = size;
    selectedColor = color;
    additionalPrice = addPrice;
    
    // Update price display
    var basePrice = <?php echo $row['price']; ?>;
    var newPrice = basePrice + additionalPrice;
    document.querySelector('.details-section .price').innerHTML = '<i class="fa-solid fa-indian-rupee-sign"></i> ' + newPrice.toFixed(2);
    
    // Update add to cart link
    var cartLink = document.getElementById('addToCartLink');
    if(variantId > 0) {
        cartLink.href = 'add_to_cart.php?id=<?php echo $id; ?>&variant=' + variantId;
    } else {
        cartLink.href = 'add_to_cart.php?id=<?php echo $id; ?>';
    }
    
    // Highlight selected variant
    document.querySelectorAll('.variant-option').forEach(opt => opt.classList.remove('selected'));
    event.currentTarget.classList.add('selected');
}

function toggleLike(btn) {
    const icon = btn.querySelector("i");
    icon.classList.toggle("fa-solid");
    icon.classList.toggle("fa-regular");
    btn.classList.toggle("liked");
}
</script>

</body>
</html>