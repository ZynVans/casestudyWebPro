<?php
include 'auth.php';
include 'config.php';
require_role(['owner']);
$result = mysqli_query($conn, "SELECT p.*, c.name AS cat_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC");
$title = "Manage Products - Kopipiro";
include 'header.php';
?>
<div class="d-flex justify-content-between mb-3">
    <h4>Manage Products</h4>
    <a href="product_form.php" class="btn btn-success">+ Add Product</a>
</div>
<table class="table table-bordered table-striped align-middle bg-white">
    <thead class="table-dark"><tr><th>No</th><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th class="text-center">Actions</th></tr></thead>
    <tbody>
    <?php $no = 1; while ($p = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?php if ($p['image']): ?><img src="uploads/<?= htmlspecialchars($p['image']) ?>" style="height:50px" alt=""><?php endif; ?></td>
            <td><?= htmlspecialchars($p['name']) ?></td>
            <td><?= htmlspecialchars($p['cat_name']) ?></td>
            <td><?= rupiah($p['price']) ?></td>
            <td class="text-center">
                <a href="product_form.php?id=<?= (int) $p['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                <form method="POST" action="product_delete.php" class="d-inline" onsubmit="return confirm('Delete this product?')">
                    <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                    <button class="btn btn-danger btn-sm">Delete</button>
                </form>
            </td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>
<?php include 'footer.php'; ?>
