<aside id="filterSidebar" class="filter-sidebar">
    <div class="filter-header">
        <h3><i class="fas fa-filter"></i> Filter Products</h3>
        <button id="closeFilterBtn" class="close-filter-btn">&times;</button>
    </div>
    
    <form method="GET" action="index.php" id="filterForm">
        <!-- Price Range Filter -->
        <div class="filter-group price-filter">
            <h4><i class="fas fa-rupee-sign"></i> Price Range</h4>
            <div class="price-range">
                <div class="price-inputs">
                    <div class="price-input">
                        <label>Min</label>
                        <input type="number" name="min_price" id="min_price" placeholder="₹0" value="<?php echo isset($_GET['min_price']) ? $_GET['min_price'] : ''; ?>">
                    </div>
                    <span class="price-separator">-</span>
                    <div class="price-input">
                        <label>Max</label>
                        <input type="number" name="max_price" id="max_price" placeholder="₹100000" value="<?php echo isset($_GET['max_price']) ? $_GET['max_price'] : ''; ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Categories Filter -->
        <div class="filter-group">
            <h4><i class="fas fa-layer-group"></i> Categories</h4>
            <div class="filter-options">
                <?php
                $categories = ["Bedroom","Kitchen","Bathroom","Electricals",
                               "Dining","Living Room","Outdoor",
                               "Decor","Office","Kids"];
                foreach ($categories as $cat) {
                    $checked = '';
                    if(isset($_GET['category']) && in_array($cat, $_GET['category'])) $checked='checked';
                    echo '<label class="filter-checkbox">
                            <input type="checkbox" name="category[]" value="'.$cat.'" '.$checked.'>
                            <span class="checkmark"></span>
                            <span class="filter-label">'.$cat.'</span>
                          </label>';
                }
                ?>
            </div>
        </div>

        <!-- Material Filter -->
        <div class="filter-group">
            <h4><i class="fas fa-cube"></i> Material</h4>
            <div class="filter-options">
                <?php
                $materials = ["Wood","Plastic","Metal","Glass","Fabric","Leather","Marble","Electronic","Other"];
                foreach ($materials as $mat) {
                    $checked = '';
                    if(isset($_GET['material']) && in_array($mat, $_GET['material'])) $checked='checked';
                    echo '<label class="filter-checkbox">
                            <input type="checkbox" name="material[]" value="'.$mat.'" '.$checked.'>
                            <span class="checkmark"></span>
                            <span class="filter-label">'.$mat.'</span>
                          </label>';
                }
                ?>
            </div>
        </div>

        <!-- Color Filter -->
        <div class="filter-group">
            <h4><i class="fas fa-palette"></i> Color</h4>
            <div class="color-options">
                <?php
                $colors = ["White","Black","Brown","Gray","Blue","Red","Green",
                           "Yellow","Beige","Pink","Orange","Purple","Silver","Gold"];
                foreach ($colors as $color) {
                    $checked = '';
                    if(isset($_GET['color']) && in_array($color, $_GET['color'])) $checked='checked';
                    $color_lower = strtolower($color);
                    echo '<label class="color-option" style="background-color: '.$color_lower.';" title="'.$color.'">
                            <input type="checkbox" name="color[]" value="'.$color.'" '.$checked.'>
                            <span class="color-check"></span>
                          </label>';
                }
                ?>
            </div>
        </div>

        <!-- Brand Filter -->
        <div class="filter-group">
            <h4><i class="fas fa-building"></i> Brand</h4>
            <div class="filter-options">
                <?php
                $brands = ["IKEA","Home Depot","Wayfair","Ashley","La-Z-Boy","Ferguson","BottoChino","WoodenStreet","Sleepwell","UrbanLadder"];
                foreach ($brands as $brand) {
                    $checked = '';
                    if(isset($_GET['brand']) && in_array($brand, $_GET['brand'])) $checked='checked';
                    echo '<label class="filter-checkbox">
                            <input type="checkbox" name="brand[]" value="'.$brand.'" '.$checked.'>
                            <span class="checkmark"></span>
                            <span class="filter-label">'.$brand.'</span>
                          </label>';
                }
                ?>
            </div>
        </div>

        <!-- Availability Filter -->
        <div class="filter-group">
            <h4><i class="fas fa-check-circle"></i> Availability</h4>
            <div class="filter-options">
                <label class="filter-radio">
                    <input type="radio" name="availability" value="all" <?php echo (!isset($_GET['availability']) || $_GET['availability'] == 'all') ? 'checked' : ''; ?>>
                    <span class="radio-mark"></span>
                    <span class="filter-label">All Products</span>
                </label>
                <label class="filter-radio">
                    <input type="radio" name="availability" value="in_stock" <?php echo (isset($_GET['availability']) && $_GET['availability'] == 'in_stock') ? 'checked' : ''; ?>>
                    <span class="radio-mark"></span>
                    <span class="filter-label">In Stock</span>
                </label>
                <label class="filter-radio">
                    <input type="radio" name="availability" value="out_of_stock" <?php echo (isset($_GET['availability']) && $_GET['availability'] == 'out_of_stock') ? 'checked' : ''; ?>>
                    <span class="radio-mark"></span>
                    <span class="filter-label">Out of Stock</span>
                </label>
            </div>
        </div>

        <!-- Rating Filter -->
        <div class="filter-group">
            <h4><i class="fas fa-star"></i> Customer Rating</h4>
            <div class="rating-options">
                <?php
                $ratings = [4,3,2,1];
                $selected_rating = isset($_GET['rating']) ? intval($_GET['rating']) : 0;
                foreach($ratings as $rating):
                ?>
                <label class="rating-option <?php echo ($selected_rating == $rating) ? 'active' : ''; ?>">
                    <input type="radio" name="rating" value="<?php echo $rating; ?>" <?php echo ($selected_rating == $rating) ? 'checked' : ''; ?>>
                    <span class="rating-stars">
                        <?php for($i = 1; $i <= 5; $i++): ?>
                            <?php if($i <= $rating): ?>
                                <i class="fas fa-star"></i>
                            <?php else: ?>
                                <i class="far fa-star"></i>
                            <?php endif; ?>
                        <?php endfor; ?>
                        <span class="rating-text">& Up</span>
                    </span>
                </label>
                <?php endforeach; ?>
                <label class="rating-option <?php echo ($selected_rating == 0) ? 'active' : ''; ?>">
                    <input type="radio" name="rating" value="0" <?php echo ($selected_rating == 0) ? 'checked' : ''; ?>>
                    <span class="rating-stars">All Ratings</span>
                </label>
            </div>
        </div>

        <!-- Filter Actions -->
        <div class="filter-actions">
            <button type="button" class="apply-filters-btn" id="applyFiltersBtn">
                <i class="fas fa-check"></i> Apply Filters
            </button>
            <button type="button" class="reset-filters-btn" id="resetFiltersBtn">
                <i class="fas fa-undo"></i> Reset All
            </button>
        </div>
    </form>
</aside>

<script>
// Apply Filters - Collect all filter values and submit
document.getElementById('applyFiltersBtn').addEventListener('click', function() {
    const form = document.getElementById('filterForm');
    const formData = new FormData(form);
    const params = new URLSearchParams();
    
    // Collect all checked categories
    document.querySelectorAll('input[name="category[]"]:checked').forEach(cb => {
        params.append('category[]', cb.value);
    });
    
    // Collect all checked materials
    document.querySelectorAll('input[name="material[]"]:checked').forEach(cb => {
        params.append('material[]', cb.value);
    });
    
    // Collect all checked colors
    document.querySelectorAll('input[name="color[]"]:checked').forEach(cb => {
        params.append('color[]', cb.value);
    });
    
    // Collect all checked brands
    document.querySelectorAll('input[name="brand[]"]:checked').forEach(cb => {
        params.append('brand[]', cb.value);
    });
    
    // Price range
    const minPrice = document.getElementById('min_price').value;
    const maxPrice = document.getElementById('max_price').value;
    if(minPrice) params.append('min_price', minPrice);
    if(maxPrice) params.append('max_price', maxPrice);
    
    // Availability
    const availability = document.querySelector('input[name="availability"]:checked');
    if(availability && availability.value !== 'all') {
        params.append('availability', availability.value);
    }
    
    // Rating
    const rating = document.querySelector('input[name="rating"]:checked');
    if(rating && rating.value != 0) {
        params.append('rating', rating.value);
    }
    
    // Redirect to index with filters
    window.location.href = 'index.php?' + params.toString();
});

// Reset all filters
document.getElementById('resetFiltersBtn').addEventListener('click', function() {
    window.location.href = 'index.php';
});

// Close filter sidebar
document.getElementById('closeFilterBtn').addEventListener('click', function() {
    document.getElementById('filterSidebar').classList.remove('active');
});

// Prevent accidental form submission on enter key
document.getElementById('filterForm').addEventListener('keypress', function(e) {
    if(e.key === 'Enter') {
        e.preventDefault();
        document.getElementById('applyFiltersBtn').click();
    }
});
</script>