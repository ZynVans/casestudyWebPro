<?php
include 'auth.php';
include 'config.php';
require_role(['owner', 'cashier', 'customer']);
$uid = (int) $_SESSION['user_id'];
$statuses = ['pending', 'processing', 'completed', 'cancelled'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (is_owner() || is_cashier())) {
    $oid = (int) $_POST['order_id'];
    if (($_POST['action'] ?? '') === 'status' && in_array($_POST['status'], $statuses, true)) {
        if (is_owner()) {
            $st = mysqli_prepare($conn, "UPDATE orders SET status = ? WHERE id = ?");
            mysqli_stmt_bind_param($st, "si", $_POST['status'], $oid);
        } else {
            $st = mysqli_prepare($conn, "UPDATE orders SET status = ? WHERE id = ? AND cashier_id = ?");
            mysqli_stmt_bind_param($st, "sii", $_POST['status'], $oid, $uid);
        }
        mysqli_stmt_execute($st);
    }
    if (is_owner() && ($_POST['action'] ?? '') === 'assign') {
        $cid = (int) $_POST['cashier_id'];
        $st = mysqli_prepare($conn, "UPDATE orders SET cashier_id = (SELECT id FROM users WHERE id = ? AND role = 'cashier') WHERE id = ?");
        mysqli_stmt_bind_param($st, "ii", $cid, $oid);
        mysqli_stmt_execute($st);
    }
    header("Location: orders.php");
    exit;
}

$sql = "SELECT o.*, cu.username AS cashier_name,
        GROUP_CONCAT(CONCAT(oi.product_name, ' x', oi.qty) SEPARATOR ', ') AS items
        FROM orders o
        LEFT JOIN users cu ON o.cashier_id = cu.id
        JOIN order_items oi ON oi.order_id = o.id";
if (is_owner()) {
    $stmt = mysqli_prepare($conn, $sql . " GROUP BY o.id ORDER BY o.id DESC");
} else {
    $col = is_cashier() ? "o.cashier_id" : "o.user_id";
    $stmt = mysqli_prepare($conn, $sql . " WHERE $col = ? GROUP BY o.id ORDER BY o.id DESC");
    mysqli_stmt_bind_param($stmt, "i", $uid);
}
mysqli_stmt_execute($stmt);
$orders = mysqli_stmt_get_result($stmt);

$page_scripts = "";
if (is_owner()) {
    $cashiers = [];
    $cr = mysqli_query($conn, "SELECT id, username FROM users WHERE role = 'cashier' ORDER BY username");
    while ($c = mysqli_fetch_assoc($cr)) {
        $cashiers[] = $c;
    }
    $sum = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS n, COALESCE(SUM(total), 0) AS rev FROM orders WHERE status <> 'cancelled'"));
    $dl = $dv = $pl = $pv = [];
    $r = mysqli_query($conn, "SELECT DATE(created_at) AS d, SUM(total) AS t FROM orders
        WHERE status <> 'cancelled' AND created_at >= CURDATE() - INTERVAL 29 DAY GROUP BY d ORDER BY d");
    while ($x = mysqli_fetch_assoc($r)) {
        $dl[] = $x['d'];
        $dv[] = (int) $x['t'];
    }
    $r = mysqli_query($conn, "SELECT oi.product_name, SUM(oi.qty) AS q FROM order_items oi
        JOIN orders o ON oi.order_id = o.id WHERE o.status <> 'cancelled'
        GROUP BY oi.product_name ORDER BY q DESC LIMIT 5");
    while ($x = mysqli_fetch_assoc($r)) {
        $pl[] = $x['product_name'];
        $pv[] = (int) $x['q'];
    }
    $page_scripts = '<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script><script>
new Chart(document.getElementById("dailyChart"), {type: "line", data: {labels: ' . json_encode($dl) . ', datasets: [{label: "Revenue (Rp)", data: ' . json_encode($dv) . ', borderColor: "#6f4e37", tension: 0.2}]}});
new Chart(document.getElementById("topChart"), {type: "bar", data: {labels: ' . json_encode($pl) . ', datasets: [{label: "Qty sold", data: ' . json_encode($pv) . ', backgroundColor: "#c69c6d"}]}});
</script>';
}
$title = is_owner() ? "Orders & Sales - Kopipiro" : "Orders - Kopipiro";
include 'header.php';
?>
<?php if (is_owner()): ?>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Sales Overview</h4>
        <a href="export.php" class="btn btn-success">Download Excel</a>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="card card-body"><div class="text-muted small">Orders (not cancelled)</div><h4><?= (int) $sum['n'] ?></h4></div></div>
        <div class="col-md-3"><div class="card card-body"><div class="text-muted small">Revenue</div><h4><?= rupiah($sum['rev']) ?></h4></div></div>
        <div class="col-md-6 d-none d-md-block"></div>
        <div class="col-md-7"><div class="card card-body"><h6>Daily revenue (last 30 days)</h6><canvas id="dailyChart"></canvas></div></div>
        <div class="col-md-5"><div class="card card-body"><h6>Top 5 products</h6><canvas id="topChart"></canvas></div></div>
    </div>
<?php endif; ?>
<h4><?= is_owner() ? "All Orders" : (is_cashier() ? "Orders Assigned To Me" : "My Orders") ?></h4>
<div class="table-responsive">
<table class="table table-bordered table-striped align-middle bg-white">
    <thead class="table-dark"><tr><th>#</th><th>Date</th><th>Customer</th><th>Items</th><th>Total</th><?php if (!is_cashier()): ?><th>Cashier</th><?php endif; ?><th>Status</th></tr></thead>
    <tbody>
    <?php while ($o = mysqli_fetch_assoc($orders)): ?>
        <tr>
            <td><a href="receipt.php?id=<?= (int) $o['id'] ?>"><?= (int) $o['id'] ?></a></td>
            <td><?= $o['created_at'] ?></td>
            <td><?= htmlspecialchars($o['customer_name']) ?></td>
            <td><?= htmlspecialchars($o['items']) ?></td>
            <td><?= rupiah($o['total']) ?></td>
            <?php if (is_owner()): ?>
                <td>
                    <form method="POST" class="d-flex gap-1">
                        <input type="hidden" name="action" value="assign">
                        <input type="hidden" name="order_id" value="<?= (int) $o['id'] ?>">
                        <select name="cashier_id" class="form-select form-select-sm" onchange="this.form.submit()">
                            <?php foreach ($cashiers as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= $o['cashier_id'] == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['username']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </td>
            <?php elseif (is_customer()): ?>
                <td><?= htmlspecialchars($o['cashier_name'] ?? '-') ?></td>
            <?php endif; ?>
            <td>
                <?php if (is_owner() || is_cashier()): ?>
                    <form method="POST">
                        <input type="hidden" name="action" value="status">
                        <input type="hidden" name="order_id" value="<?= (int) $o['id'] ?>">
                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                            <?php foreach ($statuses as $s): ?>
                                <option value="<?= $s ?>" <?= $o['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                <?php else: ?>
                    <span class="badge bg-secondary"><?= htmlspecialchars($o['status']) ?></span>
                <?php endif; ?>
            </td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>
</div>
<?php include 'footer.php'; ?>
