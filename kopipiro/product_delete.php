<?php
include 'auth.php';
include 'config.php';
require_role(['owner']);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int) $_POST['id'];
    $s = mysqli_prepare($conn, "SELECT image FROM products WHERE id = ?");
    mysqli_stmt_bind_param($s, "i", $id);
    mysqli_stmt_execute($s);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($s));
    if ($row) {
        $stmt = mysqli_prepare($conn, "DELETE FROM products WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        if ($row['image'] && is_file(__DIR__ . "/uploads/" . $row['image'])) {
            unlink(__DIR__ . "/uploads/" . $row['image']);
        }
    }
}
header("Location: products_manage.php");
exit;
