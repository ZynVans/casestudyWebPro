<?php
include 'auth.php';
include 'config.php';
if (is_logged_in()) {
    header("Location: index.php");
    exit;
}
$error = "";
if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $stmt = mysqli_prepare($conn, "SELECT id, username, password, role FROM users WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        header("Location: " . ($user['role'] === 'customer' ? "index.php" : ($user['role'] === 'owner' ? "orders.php" : "orders.php")));
        exit;
    }
    $error = "Incorrect username or password.";
}
$title = "Login - Kopipiro";
include 'header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white text-center"><h5 class="mb-0">Login</h5></div>
            <div class="card-body">
                <?php if ($error !== ""): ?>
                    <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="d-grid"><button type="submit" name="login" class="btn btn-dark">Login</button></div>
                </form>
                <p class="small text-center mt-3 mb-0">No account? <a href="register.php">Register</a> for 10% off.</p>
            </div>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
