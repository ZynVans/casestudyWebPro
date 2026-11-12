<?php
include 'auth.php';
include 'config.php';
if (is_owner() || is_cashier()) {
    header("Location: index.php");
    exit;
}
list($lines, $sub, $tot) = cart_lines($conn);
if (!$lines) {
    header("Location: cart.php");
    exit;
}
$discount = $sub - $tot;
$error = "";
if (isset($_POST['place_order'])) {
    $name = is_customer() ? $_SESSION['username'] : trim($_POST['customer_name'] ?? '');
    if ($name === "") {
        $error = "Please enter your name.";
    } else {
        $uid = is_customer() ? (int) $_SESSION['user_id'] : null;
        mysqli_begin_transaction($conn);
        try {
            $res = mysqli_query($conn, "SELECT u.id FROM users u
                LEFT JOIN orders o ON o.cashier_id = u.id AND o.status IN ('pending','processing')
                WHERE u.role = 'cashier' GROUP BY u.id ORDER BY COUNT(o.id), u.id LIMIT 1");
            $row = mysqli_fetch_row($res);
            $cashier = $row ? (int) $row[0] : null;
            $stmt = mysqli_prepare($conn, "INSERT INTO orders (user_id, customer_name, cashier_id, subtotal, discount, total) VALUES (?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "isiiii", $uid, $name, $cashier, $sub, $discount, $tot);
            mysqli_stmt_execute($stmt);
            $oid = mysqli_insert_id($conn);
            $it = mysqli_prepare($conn, "INSERT INTO order_items (order_id, product_id, product_name, qty, unit_price) VALUES (?, ?, ?, ?, ?)");
            foreach ($lines as $l) {
                mysqli_stmt_bind_param($it, "iisii", $oid, $l['id'], $l['name'], $l['qty'], $l['unit']);
                mysqli_stmt_execute($it);
            }
            mysqli_commit($conn);
            unset($_SESSION['cart']);
            $_SESSION['last_order'] = $oid;
            header("Location: receipt.php?id=$oid");
            exit;
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error = "Failed to place order. Please try again.";
        }
    }
}
$title = "Checkout - Kopipiro";
include 'header.php';
?>
<h4>Checkout</h4>
<?php if ($error !== ""): ?><div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<table class="table table-bordered bg-white">
    <thead class="table-dark"><tr><th>Product</th><th>Price</th><th>Qty</th><th>Subtotal</th></tr></thead>
    <tbody>
    <?php foreach ($lines as $l): ?>
        <tr>
            <td><?= htmlspecialchars($l['name']) ?></td>
            <td><?php if (is_customer()): ?><del class="text-muted small"><?= rupiah($l['price']) ?></del><?php endif; ?> <?= rupiah($l['unit']) ?></td>
            <td><?= $l['qty'] ?></td>
            <td><?= rupiah($l['line']) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr><td colspan="3" class="text-end">Subtotal</td><td><?= rupiah($sub) ?></td></tr>
        <tr><td colspan="3" class="text-end">Member discount (10%)</td><td>- <?= rupiah($discount) ?></td></tr>
        <tr><th colspan="3" class="text-end">Total</th><th><?= rupiah($tot) ?></th></tr>
    </tfoot>
</table>
<?php if (!is_customer()): ?>
    <div class="alert alert-info py-2">Log in as a customer to get 10% off. <a href="login.php">Login</a> | <a href="register.php">Register</a></div>
<?php endif; ?>
<form method="POST" class="row g-2" style="max-width:480px">
    <?php if (!is_customer()): ?>
        <div class="col-12"><label class="form-label">Your name</label>
            <input type="text" name="customer_name" class="form-control" required></div>
    <?php endif; ?>
    <div class="col-12 d-flex gap-2">
        <a href="cart.php" class="btn btn-secondary">Back</a>
        <button type="submit" name="place_order" class="btn btn-success">Place Order</button>
    </div>
</form>
<?php include 'footer.php'; ?>
