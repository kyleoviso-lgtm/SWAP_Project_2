<?php

require_once 'payment_flow.php'; // payment/session handling file

// csrf validation
require_once 'csrf.php'; 

require_once 'db.php';

// Require login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login_page.php');
    exit;
}



// validate user perms to view item since using iid in url
$userRole = $_SESSION['role']; 
$userName = $_SESSION['username'];

$iid = filter_input(INPUT_GET, 'iid', FILTER_VALIDATE_INT);

if ($iid === null || $iid === false) {
    die('Invalid item ID.');
}

// Prepare base query
$sql = "SELECT IID, name, price, description, availability, role_ID 
        FROM item 
        WHERE IID = ? 
        LIMIT 1";

$stmt = $connection->prepare($sql);
$stmt->bind_param('i', $iid);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Block access if the item does not exist
if (!$item) {
    die('Item not found.');
}


if ($userRole === 'individual' && (int)$item['role_ID'] === 4) {
    die('You do not have permission to view this item.');
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

// Fetch sizes
$sizeSql = 'SELECT SID, size, size_price_multi FROM size ORDER BY size ASC';
$sizeStmt = $connection->prepare($sizeSql);
$sizeStmt->execute();
$sizeResult = $sizeStmt->get_result();
$sizes = $sizeResult->fetch_all(MYSQLI_ASSOC);
$sizeStmt->close();

// Fetch colours
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
<title><?php echo $item ? htmlspecialchars($item['name']) : 'Item Details'; ?></title>
<link rel="stylesheet" href="css/item_page.css">
<style>
.option-select.error { border: 1px solid red; }
</style>
</head>
<body>
<main class="main-content">
    <header class="topbar">
        <div class="topbar-left">
            <a class="back-link" href="store_page.php">← Back to Store</a>
        </div>
        <div class="topbar-spacer"></div>
        <div class="topbar-right">
            <a href="cart.php" class="icon-btn"><span>🛒</span></a>
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

                <button class="add-cart-btn" type="button" <?php echo ((int)$item['availability'] === 1) ? '' : 'disabled'; ?> 
                        data-iid="<?php echo htmlspecialchars((string)$item['IID']); ?>"
                        data-name="<?php echo htmlspecialchars($item['name']); ?>"
                        data-base-price="<?php echo htmlspecialchars((float)$item['price']); ?>">
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
    const basePrice = parseFloat(priceDisplay.dataset.basePrice || '0');
    const selectedOption = sizeSelect.selectedOptions[0];
    const multiplier = selectedOption?.dataset.multi ? parseFloat(selectedOption.dataset.multi) : 1;
    priceDisplay.textContent = (!selectedOption || !selectedOption.value) ? '-' : `$${(basePrice * multiplier).toFixed(2)}`;
}

sizeSelect.addEventListener('change', () => { sizeSelect.classList.remove('error'); updatePrice(); });
colourSelect.addEventListener('change', () => colourSelect.classList.remove('error'));
updatePrice();

addCartBtn.addEventListener('click', () => {
    const selectedSize = sizeSelect.selectedOptions[0];
    const selectedColour = colourSelect.selectedOptions[0];

    let hasError = false;
    if (!selectedSize?.value) { sizeSelect.classList.add('error'); hasError = true; }
    if (!selectedColour?.value) { colourSelect.classList.add('error'); hasError = true; }
    if (hasError) return;

    const formData = new FormData();
    formData.append('action', 'add');
    formData.append('iid', addCartBtn.dataset.iid);
    formData.append('size', selectedSize.value);
    formData.append('size_text', selectedSize.text); 
    formData.append('colour', selectedColour.value);
    formData.append('colour_text', selectedColour.text); 
    formData.append('qty', 1);
    formData.append('csrf_token', '<?= csrf_token() ?>');


    fetch('cart.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) alert('Added to cart!');
            else alert(data.error || 'Failed to add to cart');
        })
        .catch(() => alert('Network error'));
});


</script>

</body>
</html>
