<?php
include 'config.php';
$accounts = [
    ['owner', 'owner123', 'owner'],
    ['cashier1', 'cashier123', 'cashier'],
    ['cashier2', 'cashier123', 'cashier'],
];
$stmt = mysqli_prepare($conn, "INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
foreach ($accounts as $a) {
    $hash = password_hash($a[1], PASSWORD_DEFAULT);
    mysqli_stmt_bind_param($stmt, "sss", $a[0], $hash, $a[2]);
    mysqli_stmt_execute($stmt);
}
echo "Created owner/owner123, cashier1/cashier123, cashier2/cashier123. Delete this file now!";
