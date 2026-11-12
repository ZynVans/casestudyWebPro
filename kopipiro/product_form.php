<?php
include 'auth.php';
include 'config.php';
require_role(['owner']);
$id = (int) ($_GET['id'] ?? 0);
$p = ['category_id' => 0, 'name' => '', 'description' => '', 'price' => '', 'image' => null];
if ($id) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM products WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $p = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    if (!$p) {
        die("Product not found.");
    }
}
$error = "";
if (isset($_POST['save'])) {
    $name = trim($_POST['name']);
    $desc = trim($_POST['description']);
    $price = (int) $_POST['price'];
    $cat = (int) $_POST['category_id'];
    $image = $p['image'];
    if ($name === "" || $price <= 0 || $cat <= 0) {
        $error = "Name, category and a positive price are required.";
    }
    if ($error === "" && isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $mime = mime_content_type($_FILES['image']['tmp_name']);
        $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime] ?? null;
        if (!$ext || $_FILES['image']['size'] > 2 * 1024 * 1024) {
            $error = "Image must be JPG, PNG or WEBP, max 2 MB.";
        } else {
            $new = bin2hex(random_bytes(8)) . "." . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . "/uploads/" . $new);
            if ($image && is_file(__DIR__ . "/uploads/" . $image)) {
                unlink(__DIR__ . "/uploads/" . $image);
            }
            $image = $new;
        }
    }
    if ($error === "") {
        if ($id) {
            $st = mysqli_prepare($conn, "UPDATE products SET category_id = ?, name = ?, description = ?, price = ?, image = ? WHERE id = ?");
            mysqli_stmt_bind_param($st, "issisi", $cat, $name, $desc, $price, $image, $id);
        } else {
            $st = mysqli_prepare($conn, "INSERT INTO products (category_id, name, description, price, image) VALUES (?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($st, "issis", $cat, $name, $desc, $price, $image);
        }
        mysqli_stmt_execute($st);
        header("Location: products_manage.php");
        exit;
    }
    $p = array_merge($p, ['name' => $name, 'description' => $desc, 'price' => $price, 'category_id' => $cat]);
}
$cats = mysqli_query($conn, "SELECT id, name FROM categories ORDER BY id");
$title = ($id ? "Edit" : "Add") . " Product - Kopipiro";
include 'header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header <?= $id ? 'bg-warning' : 'bg-success text-white' ?>"><h5 class="mb-0"><?= $id ? "Edit" : "Add" ?> Product</h5></div>
            <div class="card-body">
                <?php if ($error !== ""): ?><div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div><?php endif; ?>
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3"><label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($p['name']) ?>" required></div>
                    <div class="mb-3"><label class="form-label">Category</label>
                        <select name="category_id" class="form-select" required>
                            <option value="" disabled <?= $p['category_id'] ? '' : 'selected' ?>>Select...</option>
                            <?php while ($c = mysqli_fetch_assoc($cats)): ?>
                                <option value="<?= $c['id'] ?>" <?= $p['category_id'] == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                            <?php endwhile; ?>
                        </select></div>
                    <div class="mb-3"><label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($p['description'] ?? '') ?></textarea></div>
                    <div class="mb-3"><label class="form-label">Price (Rp)</label>
                        <input type="number" name="price" min="1" class="form-control" value="<?= htmlspecialchars($p['price']) ?>" required></div>
                    <div class="mb-4"><label class="form-label">Image</label>
                        <?php if ($p['image']): ?><div class="mb-1"><img src="uploads/<?= htmlspecialchars($p['image']) ?>" style="height:70px" alt=""></div><?php endif; ?>
                        <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/webp"></div>
                    <div class="d-flex gap-2 justify-content-end">
                        <a href="products_manage.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" name="save" class="btn btn-success">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
