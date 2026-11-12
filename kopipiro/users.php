<?php
include 'auth.php';
include 'config.php';
require_role(['owner']);
$message = "";
if (isset($_POST['add_user'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    if (strlen($password) < 6) {
        $message = "<div class='alert alert-danger'>Password must be at least 6 characters.</div>";
    } elseif (!in_array($role, ['owner', 'cashier', 'customer'], true)) {
        $message = "<div class='alert alert-danger'>Invalid role.</div>";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($conn, "INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sss", $username, $hash, $role);
        try {
            mysqli_stmt_execute($stmt);
            $message = "<div class='alert alert-success'>User added successfully.</div>";
        } catch (mysqli_sql_exception $e) {
            $message = "<div class='alert alert-danger'>Username is already taken.</div>";
        }
    }
}
$users = mysqli_query($conn, "SELECT id, username, role FROM users ORDER BY id");
$title = "User Management - Kopipiro";
include 'header.php';
?>
<?= $message ?>
<div class="row g-4">
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white"><h5 class="mb-0">Add User</h5></div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3"><label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required></div>
                    <div class="mb-3"><label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" minlength="6" required></div>
                    <div class="mb-4"><label for="role" class="form-label">Role</label>
                        <select class="form-select" id="role" name="role">
                            <option value="cashier">Cashier</option>
                            <option value="customer">Customer</option>
                            <option value="owner">Owner</option>
                        </select></div>
                    <div class="d-grid"><button type="submit" name="add_user" class="btn btn-success">Save User</button></div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white"><h5 class="mb-0">User List</h5></div>
            <div class="card-body">
                <table class="table table-bordered table-striped align-middle mb-0">
                    <thead class="table-dark"><tr><th>No</th><th>Username</th><th>Role</th></tr></thead>
                    <tbody>
                    <?php $no = 1; while ($u = mysqli_fetch_assoc($users)): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($u['username']) ?>
                                <?php if ($u['id'] == $_SESSION['user_id']): ?><span class="badge bg-info">you</span><?php endif; ?></td>
                            <td><span class="badge <?= $u['role'] === 'owner' ? 'bg-danger' : ($u['role'] === 'cashier' ? 'bg-primary' : 'bg-secondary') ?>"><?= htmlspecialchars($u['role']) ?></span></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
