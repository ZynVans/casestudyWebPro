<?php
include 'auth.php';
include 'config.php';
require_role(['owner']);
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=Laporan_Penjualan_Kopipiro.xls");
header("Pragma: no-cache");
header("Expires: 0");
$result = mysqli_query($conn, "SELECT o.*, cu.username AS cashier_name,
    GROUP_CONCAT(CONCAT(oi.product_name, ' x', oi.qty) SEPARATOR ', ') AS items
    FROM orders o
    LEFT JOIN users cu ON o.cashier_id = cu.id
    JOIN order_items oi ON oi.order_id = o.id
    GROUP BY o.id ORDER BY o.id DESC");
?>
<meta charset="UTF-8">
<table border="1">
    <thead>
        <tr><th>No</th><th>Order ID</th><th>Date</th><th>Customer</th><th>Cashier</th><th>Items</th><th>Subtotal</th><th>Discount</th><th>Total</th><th>Status</th></tr>
    </thead>
    <tbody>
    <?php $no = 1; while ($r = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= (int) $r['id'] ?></td>
            <td><?= $r['created_at'] ?></td>
            <td><?= htmlspecialchars($r['customer_name']) ?></td>
            <td><?= htmlspecialchars($r['cashier_name'] ?? '-') ?></td>
            <td><?= htmlspecialchars($r['items']) ?></td>
            <td><?= (int) $r['subtotal'] ?></td>
            <td><?= (int) $r['discount'] ?></td>
            <td><?= (int) $r['total'] ?></td>
            <td><?= htmlspecialchars($r['status']) ?></td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>
