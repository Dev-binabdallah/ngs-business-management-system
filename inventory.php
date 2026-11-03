<?php
require_once 'includes/header.php';

$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_product') {
        $item_code = trim($_POST['item_code']);
        $item_name = trim($_POST['item_name']);
        $cost_price = floatval($_POST['cost_price']);
        $selling_price = floatval($_POST['selling_price']);
        $stock_qty = intval($_POST['stock_qty']);

        if ($selling_price < $cost_price) {
            $err = "Warning: Selling price cannot sit below purchase cost.";
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO products (item_code, item_name, cost_price, selling_price, stock_qty) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$item_code, $item_name, $cost_price, $selling_price, $stock_qty]);
                $msg = "Item successfully registered in catalog.";
            } catch (PDOException $e) {
                $err = "Error: Product code already exists or format is invalid.";
            }
        }
    }

    if ($_POST['action'] === 'restock') {
        $product_id = intval($_POST['product_id']);
        $additional_qty = intval($_POST['additional_qty']);

        if ($additional_qty > 0) {
            $stmt = $pdo->prepare("UPDATE products SET stock_qty = stock_qty + ? WHERE id = ?");
            $stmt->execute([$additional_qty, $product_id]);
            $msg = "Stock balance successfully replenished.";
        } else {
            $err = "Quantity must be greater than zero.";
        }
    }
}

$products = $pdo->query("SELECT * FROM products ORDER BY item_name ASC")->fetchAll();
?>

<div class="page-header">
    <h2>Inventory Control Register</h2>
</div>

<?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-danger"><?= htmlspecialchars($err) ?></div><?php endif; ?>

<div class="layout-split">
    <div class="panel">
        <div class="panel-header">
            <h3>Register New Product</h3>
        </div>
        <form action="inventory.php" method="POST">
            <input type="hidden" name="action" value="add_product">
            <div class="form-group">
                <label>Item SKU / Code</label>
                <input type="text" name="item_code" class="form-control" required placeholder="e.g. SKU-101">
            </div>
            <div class="form-group">
                <label>Item Description</label>
                <input type="text" name="item_name" class="form-control" required placeholder="e.g. Washing Powder 1kg">
            </div>
            <div class="form-group">
                <label>Unit Cost Price (KES)</label>
                <input type="number" step="0.01" name="cost_price" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Standard Selling Price (KES)</label>
                <input type="number" step="0.01" name="selling_price" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Initial Opening Stock</label>
                <input type="number" name="stock_qty" class="form-control" required value="0">
            </div>
            <button type="submit" class="btn btn-primary">Save to Inventory</button>
        </form>
    </div>

    <div class="panel">
        <div class="panel-header">
            <h3>Restock Existing Inventory</h3>
        </div>
        <form action="inventory.php" method="POST">
            <input type="hidden" name="action" value="restock">
            <div class="form-group">
                <label>Select Item</label>
                <select name="product_id" class="form-control" required>
                    <option value="">Choose item...</option>
                    <?php foreach ($products as $p): ?>
                        <option value="<?= $p['id'] ?>">
                            <?= htmlspecialchars($p['item_name']) ?> (Current: <?= $p['stock_qty'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Units Received</label>
                <input type="number" name="additional_qty" class="form-control" min="1" required>
            </div>
            <button type="submit" class="btn btn-success">Update Stock Level</button>
        </form>
    </div>
</div>

<div class="panel mt-20">
    <div class="panel-header">
        <h3>Master Product Catalog</h3>
    </div>
    <div class="table-container">
        <table class="grid-table">
            <thead>
                <tr>
                    <th>Item Code</th>
                    <th>Description</th>
                    <th>Unit Cost</th>
                    <th>Selling Price</th>
                    <th>Stock Balance</th>
                    <th>Inventory Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                    <tr><td colspan="6" class="text-center">No inventory found in database.</td></tr>
                <?php else: ?>
                    <?php foreach ($products as $p): ?>
                        <tr>
                            <td><?= htmlspecialchars($p['item_code']) ?></td>
                            <td><?= htmlspecialchars($p['item_name']) ?></td>
                            <td>KES <?= number_format($p['cost_price'], 2) ?></td>
                            <td>KES <?= number_format($p['selling_price'], 2) ?></td>
                            <td><strong><?= $p['stock_qty'] ?></strong></td>
                            <td>
                                <?php if ($p['stock_qty'] <= 0): ?>
                                    <span class="tag tag-danger">Depleted</span>
                                <?php elseif ($p['stock_qty'] <= 5): ?>
                                    <span class="tag tag-warning">Low Level</span>
                                <?php else: ?>
                                    <span class="tag tag-success">In Stock</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>