<?php
include 'auth.php';
include 'config.php';
if (is_logged_in()) {
    header("Location: index.php");
    exit;
}
$error = "";
if (isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    if ($username === "" || strlen($password) < 6) {
        $error = "Username is required and password must be at least 6 characters.";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($conn, "INSERT INTO users (username, password, role) VALUES (?, ?, 'customer')");
        mysqli_stmt_bind_param($stmt, "ss", $username, $hash);
        try {
            mysqli_stmt_execute($stmt);
            $_SESSION['flash'] = "Registration successful. Please log in to get 10% off.";
            header("Location: login.php");
            exit;
        } catch (mysqli_sql_exception $e) {
            $error = "Username is already taken.";
        }
    }
}
$title = "Register - Kopipiro";
include 'header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white text-center"><h5 class="mb-0">Register</h5></div>
            <div class="card-body">
                <?php if ($error !== ""): ?>
                    <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" minlength="6" required>
                    </div>
                    <div class="d-grid"><button type="submit" name="register" class="btn btn-success">Create Account</button></div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
