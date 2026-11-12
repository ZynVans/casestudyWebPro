<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!defined('DISCOUNT_RATE')) {
    define('DISCOUNT_RATE', 0.10);
}
function is_logged_in() { return isset($_SESSION['user_id']); }
function current_role() { return $_SESSION['role'] ?? 'guest'; }
function is_owner() { return current_role() === 'owner'; }
function is_cashier() { return current_role() === 'cashier'; }
function is_customer() { return current_role() === 'customer'; }
function require_login()
{
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}
function require_role(array $roles)
{
    require_login();
    if (!in_array(current_role(), $roles, true)) {
        http_response_code(403);
        die('403 - Access denied.');
    }
}
function price_for($price)
{
    return is_customer() ? (int) round($price * (1 - DISCOUNT_RATE)) : (int) $price;
}
function rupiah($n) { return 'Rp ' . number_format($n, 0, ',', '.'); }
function stars($avg)
{
    $r = (int) round($avg);
    return str_repeat('★', $r) . str_repeat('☆', 5 - $r);
}
function cart_count() { return array_sum($_SESSION['cart'] ?? []); }
function cart_lines($conn)
{
    $cart = $_SESSION['cart'] ?? [];
    $lines = [];
    $sub = 0;
    $tot = 0;
    if (!$cart) {
        return [[], 0, 0];
    }
    $in = implode(',', array_map('intval', array_keys($cart)));
    $res = mysqli_query($conn, "SELECT id, name, price, image FROM products WHERE id IN ($in)");
    while ($r = mysqli_fetch_assoc($res)) {
        $qty = (int) $cart[$r['id']];
        $unit = price_for($r['price']);
        $r['qty'] = $qty;
        $r['unit'] = $unit;
        $r['line'] = $unit * $qty;
        $lines[] = $r;
        $sub += $r['price'] * $qty;
        $tot += $unit * $qty;
    }
    return [$lines, $sub, $tot];
}
