<?php
include 'auth.php';
include 'config.php';
$id = (int) ($_GET['id'] ?? 0);
$stmt = mysqli_prepare($conn, "SELECT o.*, u.username AS cashier_name FROM orders o LEFT JOIN users u ON o.cashier_id = u.id WHERE o.id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$o = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
$allowed = $o && (
    ($_SESSION['last_order'] ?? 0) == $id ||
    is_owner() ||
    (is_cashier() && $o['cashier_id'] == $_SESSION['user_id']) ||
    (is_customer() && $o['user_id'] == $_SESSION['user_id'])
);
if (!$allowed) {
    http_response_code(403);
    die("403 - Access denied.");
}
$it = mysqli_prepare($conn, "SELECT product_name, qty, unit_price FROM order_items WHERE order_id = ?");
mysqli_stmt_bind_param($it, "i", $id);
mysqli_stmt_execute($it);
$items = mysqli_stmt_get_result($it);
$title = "Order #$id - Kopipiro";
include 'header.php';
?>
<div class="card shadow-sm" style="max-width:560px">
    <div class="card-header bg-success text-white">Order #<?= $id ?> - <?= htmlspecialchars($o['status']) ?></div>
    <div class="card-body">
        <p class="mb-1">Customer: <strong><?= htmlspecialchars($o['customer_name']) ?></strong></p>
        <p class="small text-muted"><?= $o['created_at'] ?> | Cashier: <?= htmlspecialchars($o['cashier_name'] ?? '-') ?></p>
        <table class="table table-sm">
            <?php while ($r = mysqli_fetch_assoc($items)): ?>
                <tr><td><?= htmlspecialchars($r['product_name']) ?> x<?= $r['qty'] ?></td><td class="text-end"><?= rupiah($r['unit_price'] * $r['qty']) ?></td></tr>
            <?php endwhile; ?>
            <tr><td>Subtotal</td><td class="text-end"><?= rupiah($o['subtotal']) ?></td></tr>
            <tr><td>Discount</td><td class="text-end">- <?= rupiah($o['discount']) ?></td></tr>
            <tr><th>Total</th><th class="text-end"><?= rupiah($o['total']) ?></th></tr>
        </table>
        <a href="index.php" class="btn btn-dark">Back to Menu</a>
    </div>
</div>
<?php include 'footer.php'; ?>
