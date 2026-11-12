<?php include_once 'auth.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Kopipiro') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include 'navbar.php'; ?>
<div class="container my-4">
<?php if (!empty($_SESSION['flash'])): ?>
    <div class="alert alert-success py-2"><?= htmlspecialchars($_SESSION['flash']) ?></div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>
