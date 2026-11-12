<nav class="navbar navbar-expand navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">☕ Kopipiro</a>
        <div class="d-flex align-items-center gap-2">
            <a href="index.php" class="btn btn-outline-light btn-sm">Menu</a>
            <?php if (is_owner()): ?>
                <a href="products_manage.php" class="btn btn-outline-light btn-sm">Products</a>
                <a href="orders.php" class="btn btn-outline-light btn-sm">Orders &amp; Sales</a>
                <a href="users.php" class="btn btn-outline-light btn-sm">Users</a>
            <?php elseif (is_cashier()): ?>
                <a href="orders.php" class="btn btn-outline-light btn-sm">My Orders</a>
            <?php else: ?>
                <?php if (is_customer()): ?>
                    <a href="orders.php" class="btn btn-outline-light btn-sm">My Orders</a>
                <?php endif; ?>
                <a href="cart.php" class="btn btn-warning btn-sm">Cart
                    <span class="badge bg-dark"><?= cart_count() ?></span></a>
            <?php endif; ?>
            <?php if (is_logged_in()): ?>
                <span class="text-white small"><?= htmlspecialchars($_SESSION['username']) ?>
                    <span class="badge bg-secondary"><?= htmlspecialchars($_SESSION['role']) ?></span></span>
                <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-light btn-sm">Login</a>
                <a href="register.php" class="btn btn-success btn-sm">Register</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
