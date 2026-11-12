<?php
include 'auth.php';
include 'config.php';
if (is_owner() || is_cashier()) {
    header("Location: index.php");
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $pid = (int) ($_POST['product_id'] ?? 0);
    $qty = max(1, min(99, (int) ($_POST['qty'] ?? 1)));
    if ($action === 'add' && $pid > 0) {
        $_SESSION['cart'][$pid] = min(99, ($_SESSION['cart'][$pid] ?? 0) + $qty);
        $_SESSION['flash'] = "Added to cart.";
        header("Location: index.php");
        exit;
    }
    if ($action === 'update' && isset($_SESSION['cart'][$pid])) {
        $_SESSION['cart'][$pid] = $qty;
    }
    if ($action === 'remove') {
        unset($_SESSION['cart'][$pid]);
    }
    if ($action === 'clear') {
        unset($_SESSION['cart']);
    }
    header("Location: cart.php");
    exit;
}
list($lines, $sub, $tot) = cart_lines($conn);
$title = "Cart - Kopipiro";
include 'header.php';
?>
<h4>Your Cart</h4>
<?php if (!$lines): ?>
    <p class="text-muted">Your cart is empty. <a href="index.php">Browse the menu</a>.</p>
<?php else: ?>
    <table class="table table-bordered align-middle bg-white">
        <thead class="table-dark"><tr><th>Product</th><th>Price</th><th style="width:150px">Qty</th><th>Subtotal</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($lines as $l): ?>
            <tr>
                <td><?= htmlspecialchars($l['name']) ?></td>
                <td><?php if (is_customer()): ?><del class="text-muted small"><?= rupiah($l['price']) ?></del><?php endif; ?> <?= rupiah($l['unit']) ?></td>
                <td>
                    <form method="POST" class="d-flex gap-1">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="product_id" value="<?= (int) $l['id'] ?>">
                        <input type="number" name="qty" value="<?= $l['qty'] ?>" min="1" max="99" class="form-control form-control-sm">
                        <button class="btn btn-outline-dark btn-sm">OK</button>
                    </form>
                </td>
                <td><?= rupiah($l['line']) ?></td>
                <td>
                    <form method="POST">
                        <input type="hidden" name="action" value="remove">
                        <input type="hidden" name="product_id" value="<?= (int) $l['id'] ?>">
                        <button class="btn btn-danger btn-sm">Remove</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot><tr><th colspan="3" class="text-end">Total</th><th colspan="2"><?= rupiah($tot) ?></th></tr></tfoot>
    </table>
    <div class="d-flex gap-2">
        <form method="POST"><input type="hidden" name="action" value="clear"><button class="btn btn-outline-danger">Clear Cart</button></form>
        <a href="checkout.php" class="btn btn-success">Checkout</a>
    </div>
<?php endif; ?>
<?php include 'footer.php'; ?>
