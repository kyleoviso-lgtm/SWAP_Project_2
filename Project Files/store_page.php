<?php
$connection = new mysqli('localhost', 'root', '', 'mydb');

if ($connection->connect_error) {
    die('Database connection failed: ' . $connection->connect_error);
}

$minPrice = filter_input(INPUT_GET, 'min_price', FILTER_VALIDATE_FLOAT);
$maxPrice = filter_input(INPUT_GET, 'max_price', FILTER_VALIDATE_FLOAT);
$inStock = isset($_GET['in_stock']);
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';

$sql = 'SELECT name, price, description, IID, availability FROM item ORDER BY name ASC';

$stmt = $connection->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$items = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$connection->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manufacturing Products Store</title>
    <link rel="stylesheet" href="css/store_page.css?v=<?php echo filemtime(__DIR__ . '/css/store_page.css'); ?>">
    <link rel="stylesheet" href="css/sidebar.css">
</head>
<body>
    <main class="main-content">
        <header class="topbar">
            <div class="search-bar">
                <span class="search-icon">🔍</span>
                <input type="text" id="search-input" placeholder="Search products, categories, or suppliers...">
            </div>
            <div class="topbar-right">
                <button class="icon-btn">
                    <span>🔔</span>
                    <span class="badge-small">5</span>
                </button>
                <button class="icon-btn">
                    <span>🛒</span>
                </button>
            </div>
        </header>

        <div class="store-content">
            <div class="filters-section">
                <div class="section-header">
                    <h2>Filters</h2>
                    <a class="text-btn" id="clear-filters-btn">Clear All</a>
                </div>

                <div id="filters-form">
                    <div class="filter-group">
                        <h3 class="filter-title">Availability</h3>
                        <label class="checkbox-label">
                            <input type="checkbox" id="in-stock-filter">
                            <span>Available for manufacturing</span>
                        </label>
                    </div>

                    <div class="filter-group">
                        <h3 class="filter-title">Price Range</h3>
                        <div class="price-inputs">
                            <input type="number" step="0.01" min="0" id="min-price-filter" placeholder="Min" class="price-input">
                            <span>—</span>
                            <input type="number" step="0.01" min="0" id="max-price-filter" placeholder="Max" class="price-input">
                        </div>
                    </div>
                </div>

                <div class="footer-separator">

                </div>

                <div class="sidebar-footer">
                    <div class="user-profile">
                        <div class="avatar">JD</div>
                        <div class="user-info">
                            <div class="user-name">John Doe</div>
                            <div class="user-role">Admin</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="products-section">
                <div class="products-header">
                    <div class="results-info">
                        <h2>All Products</h2>
                        <span class="results-count"><span id="product-count"><?php echo count($items); ?></span> products found</span>
                    </div>
                </div>

                <div class="products-grid" id="products-grid">
                    <?php if (!$items): ?>
                        <div class="empty-state">No products match your filters.</div>
                    <?php else: ?>
                        <?php foreach ($items as $item): ?>
                            <div class="product-card card-link" data-href="item_page.php?iid=<?php echo urlencode((string)$item['IID']); ?>" data-name="<?php echo htmlspecialchars(strtolower($item['name'])); ?>" data-description="<?php echo htmlspecialchars(strtolower($item['description'])); ?>" data-price="<?php echo htmlspecialchars((float)$item['price']); ?>" data-availability="<?php echo htmlspecialchars((int)$item['availability']); ?>">
                                <div class="product-image">
                                    <div class="image-placeholder">🏭</div>
                                    <?php if ((int)$item['availability'] === 1): ?>
                                        <span class="stock-badge in-stock">Available</span>
                                    <?php else: ?>
                                        <span class="stock-badge out-of-stock">Unavailable</span>
                                    <?php endif; ?>
                                </div>
                                <div class="product-info">
                                    <div class="product-category">IID: <?php echo htmlspecialchars((string)$item['IID']); ?></div>
                                    <h3 class="product-name"><?php echo htmlspecialchars($item['name']); ?></h3>
                                    <div class="product-specs">
                                        <span><?php echo htmlspecialchars($item['description']); ?></span>
                                    </div>
                                    <div class="product-footer">
                                        <div class="product-price">$<?php echo number_format((float)$item['price'], 2); ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script>
        const searchInput = document.getElementById('search-input');
        const minPriceInput = document.getElementById('min-price-filter');
        const maxPriceInput = document.getElementById('max-price-filter');
        const inStockCheckbox = document.getElementById('in-stock-filter');
        const clearFiltersBtn = document.getElementById('clear-filters-btn');
        const productCards = document.querySelectorAll('.product-card');
        const clickableCards = document.querySelectorAll('.card-link');
        const productCount = document.getElementById('product-count');

        function filterProducts() {
            const searchTerm = searchInput.value.toLowerCase();
            const minPrice = parseFloat(minPriceInput.value) || 0;
            const maxPrice = parseFloat(maxPriceInput.value) || Infinity;
            const filterInStock = inStockCheckbox.checked;

            let visibleCount = 0;

            productCards.forEach(card => {
                const name = card.dataset.name;
                const description = card.dataset.description;
                const price = parseFloat(card.dataset.price);
                const availability = parseInt(card.dataset.availability);

                const matchesSearch = name.includes(searchTerm) || description.includes(searchTerm);
                const matchesPrice = price >= minPrice && price <= maxPrice;
                const matchesStock = !filterInStock || availability === 1;

                const isVisible = matchesSearch && matchesPrice && matchesStock;

                if (isVisible) {
                    card.style.display = '';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            productCount.textContent = visibleCount;
        }

        function clearFilters() {
            searchInput.value = '';
            minPriceInput.value = '';
            maxPriceInput.value = '';
            inStockCheckbox.checked = false;
            filterProducts();
        }

        searchInput.addEventListener('input', filterProducts);
        minPriceInput.addEventListener('input', filterProducts);
        maxPriceInput.addEventListener('input', filterProducts);
        inStockCheckbox.addEventListener('change', filterProducts);
        clearFiltersBtn.addEventListener('click', (e) => {
            e.preventDefault();
            clearFilters();
        });

        clickableCards.forEach(card => {
            card.addEventListener('click', () => {
                const href = card.dataset.href;
                if (href) {
                    window.location.href = href;
                }
            });
        });
    </script>
</body>
</html>