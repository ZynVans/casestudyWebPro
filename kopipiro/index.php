<?php
include 'auth.php';
include 'config.php';
$cat = (int) ($_GET['cat'] ?? 0);
$q = trim($_GET['q'] ?? '');
$like = "%" . $q . "%";
$cats = mysqli_query($conn, "SELECT id, name FROM categories ORDER BY id");
$stmt = mysqli_prepare($conn, "SELECT p.*, c.name AS cat_name, COALESCE(AVG(r.rating), 0) AS avg_rating, COUNT(r.id) AS n_rating
    FROM products p
    JOIN categories c ON p.category_id = c.id
    LEFT JOIN reviews r ON r.product_id = p.id
    WHERE (? = 0 OR p.category_id = ?) AND p.name LIKE ?
    GROUP BY p.id ORDER BY p.name");
mysqli_stmt_bind_param($stmt, "iis", $cat, $cat, $like);
mysqli_stmt_execute($stmt);
$products = mysqli_stmt_get_result($stmt);
$can_order = !is_owner() && !is_cashier();
$title = "Menu - Kopipiro";
include 'header.php';
?>
<?php if (!is_logged_in()): ?>
    <div class="alert alert-info py-2">Register and log in to get <strong>10% off</strong> every order.</div>
<?php elseif (is_customer()): ?>
    <div class="alert alert-success py-2">Member discount of 10% is applied to all prices.</div>
<?php endif; ?>
<form class="row g-2 mb-3" method="GET">
    <div class="col-md-4">
        <select name="cat" class="form-select" onchange="this.form.submit()">
            <option value="0">All categories</option>
            <?php while ($c = mysqli_fetch_assoc($cats)): ?>
                <option value="<?= $c['id'] ?>" <?= $cat == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
            <?php endwhile; ?>
        </select>
    </div>
    <div class="col-md-6"><input type="text" name="q" class="form-control" placeholder="Search product..." value="<?= htmlspecialchars($q) ?>"></div>
    <div class="col-md-2 d-grid"><button class="btn btn-dark">Search</button></div>
</form>
<div class="row g-3">
    <?php while ($p = mysqli_fetch_assoc($products)): ?>
        <div class="col-md-4 col-lg-3">
            <div class="card h-100 shadow-sm">
                <?php if ($p['image']): ?>
                    <img src="uploads/<?= htmlspecialchars($p['image']) ?>" class="card-img-top" style="height:180px;object-fit:cover" alt="">
                <?php else: ?>
                    <div class="bg-secondary text-white d-flex align-items-center justify-content-center" style="height:180px">No image</div>
                <?php endif; ?>
                <div class="card-body d-flex flex-column">
                    <span class="badge bg-secondary align-self-start mb-1"><?= htmlspecialchars($p['cat_name']) ?></span>
                    <h6 class="card-title"><a class="text-decoration-none text-dark" href="product.php?id=<?= (int) $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></a></h6>
                    <div class="text-warning small"><?= stars($p['avg_rating']) ?>
                        <span class="text-muted">(<?= number_format($p['avg_rating'], 1) ?>, <?= (int) $p['n_rating'] ?>)</span></div>
                    <p class="small text-muted flex-grow-1"><?= htmlspecialchars(mb_strimwidth($p['description'] ?? '', 0, 70, '...')) ?></p>
                    <div class="mb-2">
                        <?php if (is_customer()): ?>
                            <del class="text-muted small"><?= rupiah($p['price']) ?></del>
                        <?php endif; ?>
                        <strong><?= rupiah(price_for($p['price'])) ?></strong>
                    </div>
                    <?php if ($can_order): ?>
                        <form method="POST" action="cart.php" class="d-flex gap-1">
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="product_id" value="<?= (int) $p['id'] ?>">
                            <input type="number" name="qty" value="1" min="1" max="99" class="form-control form-control-sm" style="width:70px">
                            <button class="btn btn-success btn-sm flex-grow-1">Add</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
    <?php if (mysqli_num_rows($products) === 0): ?>
        <div class="col-12 text-center text-muted">No products found.</div>
    <?php endif; ?>
</div>
<?php include 'footer.php'; ?>
