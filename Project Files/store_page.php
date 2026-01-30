<?php
// Boot up DB connection + login authentication guard
require_once 'bootstrap.php';
require_once 'auth_guard.php';
require_once 'user_info_fetcher.php';

$userRole = $_SESSION['role']; // 'enterprise' or 'individual'
$userName = $_SESSION['username'];
$userInitials = strtoupper(substr($userName, 0, 2));

// Query logic
if ($userRole === 'individual') {
    $sql = "
        SELECT IID, name, price, description, availability 
        FROM item 
        WHERE role_id = 3
        ORDER BY name ASC
    ";
} else {
    $sql = "
        SELECT IID, name, price, description, availability 
        FROM item 
        ORDER BY name ASC
    ";
}

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$items = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Store</title>
<link rel="stylesheet" href="css/store_page.css">
<link rel="stylesheet" href="css/sidebar.css">
</head>
<body>

<main class="main-content">

<header class="topbar">
    <div class="search-bar">
        <span class="search-icon">🔍</span>
        <input type="text" id="search-input" placeholder="Search products...">
        <label class="checkbox-label" style="margin-left:12px;">
            <input type="checkbox" id="in-stock-filter">
            <span>Available</span>
        </label>
    </div>
    <div class="topbar-right">
        <a href="cart.php" class="icon-btn"><span>🛒</span></a>
    </div>
</header>

<div class="store-content">

    <!-- FILTER SIDEBAR -->
    <aside class="filters-section">
        <div class="section-header">
            <h2>Filters</h2>
            <button class="text-btn" id="clear-filters-btn">Clear All</button>
        </div>

        <div class="filter-group">
            <label class="checkbox-label">
                <input type="checkbox" id="in-stock-filter-sidebar">
                <span>Available</span>
            </label>
        </div>

        <div class="sidebar-footer">
            <a href="profile.php">
                <div class="user-profile">
                    <div class="avatar"><?= htmlspecialchars($userInitials) ?></div>
                    <div class="user-info">
                        <div class="user-name"><?= htmlspecialchars($userName) ?></div>
                        <div class="user-role"><?= htmlspecialchars(ucfirst($userRole)) ?></div>
                    </div>
                </div>
            </a>
        </div>
    </aside>

    <!-- PRODUCTS -->
    <section class="products-section">
        <div class="products-header">
            <h2>Products</h2>
            <span><span id="product-count"><?= count($items) ?></span> found</span>
        </div>

        <div class="products-grid" id="products-grid">
            <?php if (!$items): ?>
                <div class="empty-state">No products available.</div>
            <?php else: ?>
                <?php foreach ($items as $item): ?>
                    <div class="product-card card-link"
                         data-href="item_page.php?iid=<?= (int)$item['IID'] ?>"
                         data-name="<?= htmlspecialchars(strtolower($item['name'])) ?>"
                         data-description="<?= htmlspecialchars(strtolower($item['description'])) ?>"
                         data-price="<?= (float)$item['price'] ?>"
                         data-availability="<?= (int)$item['availability'] ?>">

                        <div class="product-image">
                            <img src="images/items/computer.webp" alt="Product Image" class="product-img">

                            <?php if ((int)$item['availability'] === 1): ?>
                                <span class="stock-badge in-stock">Available</span>
                            <?php else: ?>
                                <span class="stock-badge out-of-stock">Unavailable</span>
                            <?php endif; ?>
                        </div>

                        <div class="product-info">
                            <h3><?= htmlspecialchars($item['name']) ?></h3>
                            <p><?= htmlspecialchars($item['description']) ?></p>
                            <p class="price">$<?= number_format($item['price'], 2) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
</div>
</main>

<script>
const searchInput = document.getElementById('search-input');
const inStockTop = document.getElementById('in-stock-filter'); // top bar
const inStockSidebar = document.getElementById('in-stock-filter-sidebar'); // sidebar
const productCards = document.querySelectorAll('.product-card');
const productCount = document.getElementById('product-count');

/**
 * Filter products by search and availability
 */
function filterProducts() {
    const search = searchInput.value.toLowerCase();
    const inStock = inStockTop.checked || inStockSidebar.checked; // either checkbox

    let count = 0;

    productCards.forEach(card => {
        const name = card.dataset.name;
        const desc = card.dataset.description;
        const available = parseInt(card.dataset.availability);

        const matches =
            (name.includes(search) || desc.includes(search)) &&
            (!inStock || available === 1);

        card.style.display = matches ? '' : 'none';
        if (matches) count++;
    });

    productCount.textContent = count;
}

/**
 * Keep both checkboxes in sync
 */
inStockTop.addEventListener('change', () => {
    inStockSidebar.checked = inStockTop.checked;
    filterProducts();
});

inStockSidebar.addEventListener('change', () => {
    inStockTop.checked = inStockSidebar.checked;
    filterProducts();
});

// Run filtering when typing in search
searchInput.addEventListener('input', filterProducts);

// Make product cards clickable
document.querySelectorAll('.card-link').forEach(card => {
    card.addEventListener('click', () => {
        window.location.href = card.dataset.href;
    });
});
</script>

</body>
</html>
