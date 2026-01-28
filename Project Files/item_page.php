<?php
$connection = new mysqli('localhost', 'root', '', 'mydb');

if ($connection->connect_error) {
    die('Database connection failed: ' . $connection->connect_error);
}

$iid = filter_input(INPUT_GET, 'iid', FILTER_VALIDATE_INT);
$item = null;
$sizes = [];
$colours = [];

if ($iid !== null && $iid !== false) {
    $sql = 'SELECT name, price, description, IID, availability FROM item WHERE IID = ? LIMIT 1';
    $stmt = $connection->prepare($sql);
    $stmt->bind_param('i', $iid);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();
    $stmt->close();
}

$sizeSql = 'SELECT SID, size, size_price_multi FROM size ORDER BY size ASC';
$sizeStmt = $connection->prepare($sizeSql);
$sizeStmt->execute();
$sizeResult = $sizeStmt->get_result();
$sizes = $sizeResult->fetch_all(MYSQLI_ASSOC);
$sizeStmt->close();

$colourSql = 'SELECT CID, name FROM colour ORDER BY name ASC';
$colourStmt = $connection->prepare($colourSql);
$colourStmt->execute();
$colourResult = $colourStmt->get_result();
$colours = $colourResult->fetch_all(MYSQLI_ASSOC);
$colourStmt->close();

$connection->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $item ? htmlspecialchars($item['name']) : 'Item Details'; ?></title>
    <link rel="stylesheet" href="css/item_page.css">
</head>
<body>
    <main class="main-content">
        <header class="topbar">
            <div class="topbar-left">
                <a class="back-link" href="store_page.php">← Back to Store</a>
            </div>
            <div class="topbar-spacer"></div>
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

        <div class="item-content">
            <?php if (!$item): ?>
                <div class="empty-state">
                    <h2>Item not found</h2>
                    <p>The item you're looking for doesn't exist or is no longer available.</p>
                    <a class="primary-btn" href="store_page.php">Return to Store</a>
                </div>
            <?php else: ?>
                <div class="item-card">
                    <div class="item-image">
                        <div class="image-placeholder">🏭</div>
                        <?php if ((int)$item['availability'] === 1): ?>
                            <span class="stock-badge in-stock">In Stock</span>
                        <?php else: ?>
                            <span class="stock-badge out-of-stock">Out of Stock</span>
                        <?php endif; ?>
                    </div>
                    <div class="item-info">
                        <div class="item-meta">IID: <?php echo htmlspecialchars((string)$item['IID']); ?></div>
                        <h1 class="item-name"><?php echo htmlspecialchars($item['name']); ?></h1>
                        <p class="item-description"><?php echo htmlspecialchars($item['description']); ?></p>
                        <div class="item-options">
                            <label class="option-group">
                                <span class="option-label">Size</span>
                                <select class="option-select" id="size-select">
                                    <option value="" selected disabled>Select size</option>
                                    <?php foreach ($sizes as $size): ?>
                                        <option value="<?php echo htmlspecialchars((string)$size['SID']); ?>" data-multi="<?php echo htmlspecialchars((string)$size['size_price_multi']); ?>">
                                            <?php echo htmlspecialchars($size['size']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                            <label class="option-group">
                                <span class="option-label">Colour</span>
                                <select class="option-select" id="colour-select">
                                    <option value="" selected disabled>Select colour</option>
                                    <?php foreach ($colours as $colour): ?>
                                        <option value="<?php echo htmlspecialchars((string)$colour['CID']); ?>">
                                            <?php echo htmlspecialchars($colour['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                        </div>
                        <div class="item-price" id="item-price" data-base-price="<?php echo htmlspecialchars((float)$item['price']); ?>">
                            $<?php echo number_format((float)$item['price'], 2); ?>
                        </div>
                        <button class="add-cart-btn" type="button" <?php echo ((int)$item['availability'] === 1) ? '' : 'disabled'; ?> data-iid="<?php echo htmlspecialchars((string)$item['IID']); ?>" data-name="<?php echo htmlspecialchars($item['name']); ?>" data-price="<?php echo htmlspecialchars((float)$item['price']); ?>">
                            <?php echo ((int)$item['availability'] === 1) ? 'Add to Cart' : 'Out of Stock'; ?>
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        const addCartBtn = document.querySelector('.add-cart-btn');
        const sizeSelect = document.getElementById('size-select');
        const colourSelect = document.getElementById('colour-select');
        const priceDisplay = document.getElementById('item-price');

        function updatePrice() {
            if (!priceDisplay) {
                return;
            }

            const basePrice = parseFloat(priceDisplay.dataset.basePrice || '0');
            const selectedOption = sizeSelect ? sizeSelect.selectedOptions[0] : null;
            const multiplier = selectedOption && selectedOption.dataset.multi ? parseFloat(selectedOption.dataset.multi) : 1;
            const finalPrice = basePrice * (isNaN(multiplier) ? 1 : multiplier);

            priceDisplay.textContent = `$${finalPrice.toFixed(2)}`;
        }

        if (sizeSelect) {
            sizeSelect.addEventListener('change', updatePrice);
        }

        updatePrice();

        if (addCartBtn && !addCartBtn.disabled) {
            addCartBtn.addEventListener('click', () => {
                const selectedSize = sizeSelect ? sizeSelect.value : '';
                const selectedOption = sizeSelect ? sizeSelect.selectedOptions[0] : null;
                const selectedMultiplier = selectedOption && selectedOption.dataset.multi ? parseFloat(selectedOption.dataset.multi) : 1;
                const basePrice = priceDisplay ? parseFloat(priceDisplay.dataset.basePrice || '0') : parseFloat(addCartBtn.dataset.price);
                const finalPrice = basePrice * (isNaN(selectedMultiplier) ? 1 : selectedMultiplier);
                const cartItem = {
                    iid: addCartBtn.dataset.iid,
                    name: addCartBtn.dataset.name,
                    price: parseFloat(finalPrice.toFixed(2)),
                    size: selectedSize,
                    colour: colourSelect ? colourSelect.value : '',
                    qty: 1
                };

                const existing = JSON.parse(localStorage.getItem('cartItems') || '[]');
                const match = existing.find(item => item.iid === cartItem.iid);

                if (match) {
                    match.qty += 1;
                } else {
                    existing.push(cartItem);
                }

                localStorage.setItem('cartItems', JSON.stringify(existing));
            });
        }
    </script>
</body>
</html>
