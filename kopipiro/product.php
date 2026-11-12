<?php
include 'auth.php';
include 'config.php';
$id = (int) ($_GET['id'] ?? 0);
$stmt = mysqli_prepare($conn, "SELECT p.*, c.name AS cat_name, COALESCE(AVG(r.rating), 0) AS avg_rating, COUNT(r.id) AS n_rating
    FROM products p JOIN categories c ON p.category_id = c.id
    LEFT JOIN reviews r ON r.product_id = p.id WHERE p.id = ? GROUP BY p.id");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$p = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
if (!$p) {
    die("Product not found.");
}
$can_rate = false;
if (is_customer()) {
    $uid = (int) $_SESSION['user_id'];
    $chk = mysqli_prepare($conn, "SELECT 1 FROM order_items oi JOIN orders o ON oi.order_id = o.id
        WHERE o.user_id = ? AND oi.product_id = ? AND o.status <> 'cancelled' LIMIT 1");
    mysqli_stmt_bind_param($chk, "ii", $uid, $id);
    mysqli_stmt_execute($chk);
    $can_rate = (bool) mysqli_fetch_row(mysqli_stmt_get_result($chk));
    if ($can_rate && isset($_POST['rate'])) {
        $rating = max(1, min(5, (int) $_POST['rating']));
        $comment = trim($_POST['comment']);
        $ins = mysqli_prepare($conn, "INSERT INTO reviews (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE rating = VALUES(rating), comment = VALUES(comment)");
        mysqli_stmt_bind_param($ins, "iiis", $id, $uid, $rating, $comment);
        mysqli_stmt_execute($ins);
        $_SESSION['flash'] = "Thanks for your rating!";
        header("Location: product.php?id=$id");
        exit;
    }
}
$rev = mysqli_prepare($conn, "SELECT r.rating, r.comment, r.created_at, u.username FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.product_id = ? ORDER BY r.id DESC");
mysqli_stmt_bind_param($rev, "i", $id);
mysqli_stmt_execute($rev);
$reviews = mysqli_stmt_get_result($rev);
$title = $p['name'] . " - Kopipiro";
include 'header.php';
?>
<div class="row g-4">
    <div class="col-md-5">
        <?php if ($p['image']): ?>
            <img src="uploads/<?= htmlspecialchars($p['image']) ?>" class="img-fluid rounded shadow-sm" alt="">
        <?php else: ?>
            <div class="bg-secondary text-white d-flex align-items-center justify-content-center rounded" style="height:300px">No image</div>
        <?php endif; ?>
    </div>
    <div class="col-md-7">
        <span class="badge bg-secondary"><?= htmlspecialchars($p['cat_name']) ?></span>
        <h3 class="mt-1"><?= htmlspecialchars($p['name']) ?></h3>
        <div class="text-warning"><?= stars($p['avg_rating']) ?>
            <span class="text-muted small">(<?= number_format($p['avg_rating'], 1) ?> from <?= (int) $p['n_rating'] ?> ratings)</span></div>
        <p class="mt-2"><?= nl2br(htmlspecialchars($p['description'] ?? '')) ?></p>
        <h4>
            <?php if (is_customer()): ?><del class="text-muted fs-6"><?= rupiah($p['price']) ?></del><?php endif; ?>
            <?= rupiah(price_for($p['price'])) ?>
        </h4>
        <?php if (!is_owner() && !is_cashier()): ?>
            <form method="POST" action="cart.php" class="d-flex gap-2" style="max-width:260px">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="product_id" value="<?= (int) $p['id'] ?>">
                <input type="number" name="qty" value="1" min="1" max="99" class="form-control" style="width:80px">
                <button class="btn btn-success flex-grow-1">Add to Cart</button>
            </form>
        <?php endif; ?>
    </div>
</div>
<hr>
<h5>Ratings &amp; Reviews</h5>
<?php if ($can_rate): ?>
    <form method="POST" class="card card-body mb-3">
        <div class="row g-2">
            <div class="col-md-2">
                <select name="rating" class="form-select">
                    <?php for ($i = 5; $i >= 1; $i--): ?><option value="<?= $i ?>"><?= $i ?> ★</option><?php endfor; ?>
                </select>
            </div>
            <div class="col-md-8"><input type="text" name="comment" class="form-control" maxlength="255" placeholder="Comment (optional)"></div>
            <div class="col-md-2 d-grid"><button name="rate" class="btn btn-dark">Submit</button></div>
        </div>
    </form>
<?php endif; ?>
<?php while ($r = mysqli_fetch_assoc($reviews)): ?>
    <div class="border-bottom py-2">
        <strong><?= htmlspecialchars($r['username']) ?></strong>
        <span class="text-warning"><?= stars($r['rating']) ?></span>
        <div class="small"><?= htmlspecialchars($r['comment'] ?? '') ?></div>
    </div>
<?php endwhile; ?>
<?php if (mysqli_num_rows($reviews) === 0): ?><p class="text-muted">No reviews yet.</p><?php endif; ?>
<?php include 'footer.php'; ?>
