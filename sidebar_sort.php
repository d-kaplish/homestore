<aside id="sortSidebar" class="sort-sidebar">
    <div class="filter-header">
        <h3><i class="fas fa-sort-amount-down"></i> Sort Products</h3>
        <button id="closeSortBtn" class="close-filter-btn">&times;</button>
    </div>
    
    <form method="GET" action="index.php" id="sortForm">
        <div class="filter-group">
            <h4><i class="fas fa-chart-line"></i> Sort By</h4>
            <div class="sort-options">
                <label class="sort-option <?php echo (!isset($_GET['sort']) || $_GET['sort'] == 'newest') ? 'active' : ''; ?>">
                    <input type="radio" name="sort" value="newest" <?php echo (!isset($_GET['sort']) || $_GET['sort'] == 'newest') ? 'checked' : ''; ?>>
                    <span class="sort-icon"><i class="fas fa-clock"></i></span>
                    <span class="sort-label">Newest First</span>
                </label>
                
                <label class="sort-option <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'price_low_high') ? 'active' : ''; ?>">
                    <input type="radio" name="sort" value="price_low_high" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'price_low_high') ? 'checked' : ''; ?>>
                    <span class="sort-icon"><i class="fas fa-arrow-up"></i></span>
                    <span class="sort-label">Price: Low to High</span>
                </label>
                
                <label class="sort-option <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'price_high_low') ? 'active' : ''; ?>">
                    <input type="radio" name="sort" value="price_high_low" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'price_high_low') ? 'checked' : ''; ?>>
                    <span class="sort-icon"><i class="fas fa-arrow-down"></i></span>
                    <span class="sort-label">Price: High to Low</span>
                </label>
                
                <label class="sort-option <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'rating_high_low') ? 'active' : ''; ?>">
                    <input type="radio" name="sort" value="rating_high_low" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'rating_high_low') ? 'checked' : ''; ?>>
                    <span class="sort-icon"><i class="fas fa-star"></i></span>
                    <span class="sort-label">Highest Rated</span>
                </label>
                
                <label class="sort-option <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'best_selling') ? 'active' : ''; ?>">
                    <input type="radio" name="sort" value="best_selling" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'best_selling') ? 'checked' : ''; ?>>
                    <span class="sort-icon"><i class="fas fa-trophy"></i></span>
                    <span class="sort-label">Best Selling</span>
                </label>
            </div>
        </div>
        
        <button type="button" class="apply-sort-btn" id="applySortBtn">
            <i class="fas fa-check"></i> Apply Sort
        </button>
    </form>
</aside>

<script>
// Apply Sort - Collect sort value and submit
document.getElementById('applySortBtn').addEventListener('click', function() {
    const selectedSort = document.querySelector('input[name="sort"]:checked');
    if(selectedSort) {
        // Preserve existing filters while applying sort
        const currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set('sort', selectedSort.value);
        window.location.href = currentUrl.toString();
    } else {
        window.location.href = 'index.php';
    }
});

// Close sort sidebar
document.getElementById('closeSortBtn').addEventListener('click', function() {
    document.getElementById('sortSidebar').classList.remove('active');
});
</script>