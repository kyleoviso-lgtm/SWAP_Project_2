<?php

require_once 'db.php';
require_once 'payment_flow.php';

// verify session token
require_once 'csrf.php';
csrf_verify();


// require login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login_page.php');
    exit;
}

// if redirected from failed checkout, clear the checkout lock
if (isset($_GET['checkout_cancelled'])) {
    unset($_SESSION['checkout_in_progress']);
    unset($_SESSION['pending_order_id']);
}



if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {

    // prevent race condition
    if (!empty($_SESSION['checkout_in_progress'])) {
        header('Location: checkout.php');
        exit;
    }

    if (empty($_SESSION['cart'])) {
        $error = 'Your cart is empty.';
    } else {
        $_SESSION['checkout_in_progress'] = true;
        enable_checkout();
        header('Location: checkout.php');
        exit;
    }
}



// ajax
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    $action = $_POST['action'];

    // add to cart
    if ($action === 'add') {
        $iid = (int)($_POST['iid'] ?? 0);

        $sizeId = (int)($_POST['size'] ?? 0);

        $colourId = (int)($_POST['colour'] ?? 0);

        $stmt = $connection->prepare(
            'SELECT name FROM colour WHERE CID = ? LIMIT 1'
        );
        $stmt->bind_param('i', $colourId);
        $stmt->execute();
        $colourData = $stmt->get_result()->fetch_assoc();
        $stmt->close();


        if (!$colourData) {
            echo json_encode(['success' => false, 'error' => 'Invalid colour']);
            exit;
        }

        $colourText = $colourData['name'];


        $qty = max(1, (int)($_POST['qty'] ?? 1));

        // fetch item price from db
        $stmt = $connection->prepare('SELECT name, price, availability FROM item WHERE IID = ? LIMIT 1');
        $stmt->bind_param('i', $iid);
        $stmt->execute();
        $item = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$item || (int)$item['availability'] !== 1) {
            echo json_encode(['success' => false, 'error' => 'Item not available']);
            exit;
        }

        // fetch size multiplier
        $stmt = $connection->prepare('SELECT size, size_price_multi FROM size WHERE SID = ? LIMIT 1');
        $stmt->bind_param('i', $sizeId);
        $stmt->execute();
        $sizeData = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$sizeData) {
            echo json_encode(['success' => false, 'error' => 'Invalid size']);
            exit;
        }

        $price = $item['price'] * (float)$sizeData['size_price_multi'];

        // create unique key: iid-size-colour
        $key = $iid . '-' . $sizeId . '-' . $colourId;

        if (isset($_SESSION['cart'][$key])) {
            $_SESSION['cart'][$key]['qty'] += $qty;
        } else {
            $_SESSION['cart'][$key] = [
                'iid' => $iid,
                'name' => $item['name'],
                'size' => $sizeId,
                'size_text' => $sizeData['size'],
                'colour' => $colourId,
                'colour_text' => $colourText,
                'price' => $price,
                'qty' => $qty
            ];
        }

        echo json_encode(['success' => true]);
        exit;
    }

    // update quantity
    if ($action === 'update_qty') {
        $key = $_POST['key'] ?? '';
        $delta = (int)($_POST['delta'] ?? 0);
        if (isset($_SESSION['cart'][$key])) {
            $_SESSION['cart'][$key]['qty'] += $delta;
            if ($_SESSION['cart'][$key]['qty'] < 1) {
                unset($_SESSION['cart'][$key]);
            }
        }
        echo json_encode(['success' => true]);
        exit;
    }

    // remove item
    if ($action === 'remove') {
        $key = $_POST['key'] ?? '';
        unset($_SESSION['cart'][$key]);
        echo json_encode(['success' => true]);
        exit;
    }

    echo json_encode(['success' => false, 'error' => 'Unknown action']);
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Your Cart</title>
<link rel="stylesheet" href="css/cart.css">
</head>
<body>

<main class="main-content">
    <header class="topbar">
        <div class="topbar-left">
            <a class="back-link" href="store_page.php">← Back to Store</a>
        </div>
    </header>

    <div class="cart-container">
        <h1>Your Cart</h1>

        <?php if (empty($_SESSION['cart'])): ?>
            <p>Your cart is empty.</p>
        <?php else: ?>

            <?php
            $grandTotal = 0;
            foreach ($_SESSION['cart'] as $key => $item):
                $itemTotal = $item['price'] * $item['qty'];
                $grandTotal += $itemTotal;
            ?>
                <div class="cart-item">
                    <div class="item-info">
                        <h3 class="item-title"><?= htmlspecialchars($item['name']) ?></h3>
                        <p>Size: <?= htmlspecialchars($item['size_text']) ?></p>
                        <p>Colour: <?= htmlspecialchars($item['colour_text']) ?></p>


                        <p class="qty-row">
                            Qty:
                            <span class="qty-controls">
                                <button type="button" class="qty-btn" onclick="updateQty('<?= $key ?>', -1)">−</button>
                                <span class="qty-value"><?= $item['qty'] ?></span>
                                <button type="button" class="qty-btn" onclick="updateQty('<?= $key ?>', 1)">+</button>
                            </span>
                        </p>

                        <p>Price: $<?= number_format($itemTotal, 2) ?></p>

                        <button type="button" class="remove-btn" onclick="removeItem('<?= $key ?>')">Remove</button>
                    </div>
                </div>
            <?php endforeach; ?>

            <h3>Total: $<?= number_format($grandTotal, 2) ?></h3>

            <?php if (!empty($error)): ?>
                <p style="color:red;"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <button type="submit" name="checkout" class="checkout-btn">
                    Proceed to Checkout
                </button>
            </form>

        <?php endif; ?>
    </div>
</main>

<script>
function updateQty(key, delta) {
    fetch('cart.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({
            action: 'update_qty',
            key: key,
            delta: delta,
            csrf_token: '<?= csrf_token() ?>'
        })
    }).then(() => location.reload());
}

function removeItem(key) {
    fetch('cart.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({
            action: 'remove',
            key: key,
            csrf_token: '<?= csrf_token() ?>'
        })
    }).then(() => location.reload());
}

</script>

</body>
</html>
