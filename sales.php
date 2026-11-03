<?php
require_once 'includes/header.php';

$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['record_sale'])) {
    $product_id = intval($_POST['product_id']);
    $qty = intval($_POST['quantity']);
    $discount = floatval($_POST['unit_discount'] ?? 0.00);

    if ($qty <= 0) {
        $err = "Quantity must be at least 1.";
    } else {
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? FOR UPDATE");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch();

            if (!$product) {
                throw new Exception("Product selection not found.");
            }

            if ($product['stock_qty'] < $qty) {
                throw new Exception("Insufficient stock available. Current balance: " . $product['stock_qty']);
            }

            $cost_price = $product['cost_price'];
            $base_price = $product['selling_price'];
            $final_unit_price = $base_price - $discount;

            $total_amount = $qty * $final_unit_price;
            $total_cost = $qty * $cost_price;
            $gross_profit = $total_amount - $total_cost;
            $receipt_no = 'REC-' . strtoupper(substr(uniqid(), 7, 6));

            $deduct = $pdo->prepare("UPDATE products SET stock_qty = stock_qty - ? WHERE id = ?");
            $deduct->execute([$qty, $product_id]);

            $sale_stmt = $pdo->prepare("
                INSERT INTO sales (
                    receipt_no, product_id, user_id, quantity, unit_cost, 
                    unit_price, unit_discount, final_unit_price, total_amount, 
                    total_cost, gross_profit
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $sale_stmt->execute([
                $receipt_no, $product_id, $_SESSION['user_id'], $qty, $cost_price,
                $base_price, $discount, $final_unit_price, $total_amount,
                $total_cost, $gross_profit
            ]);

            $pdo->commit();
            $msg = "Transaction successfully processed. Receipt: " . $receipt_no;
        } catch (Exception $e) {
            $pdo->rollBack();
            $err = $e->getMessage();
        }
    }
}

$available_products = $pdo->query("SELECT * FROM products WHERE stock_qty > 0 ORDER BY item_name ASC")->fetchAll();
?>

<div class="page-header">
    <h2>Point of Sale (Record Sales)</h2>
</div>

<?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-danger"><?= htmlspecialchars($err) ?></div><?php endif; ?>

<div class="panel">
    <div class="panel-header">
        <h3>New Sale Entry</h3>
    </div>
    <form action="sales.php" method="POST" id="salesForm">
        <input type="hidden" name="record_sale" value="1">
        
        <div class="form-group">
            <label>Product Item</label>
            <select name="product_id" id="product_select" class="form-control" required>
                <option value="" data-price="0" data-cost="0">Select available stock...</option>
                <?php foreach ($available_products as $p): ?>
                    <option value="<?= $p['id'] ?>" 
                            data-price="<?= $p['selling_price'] ?>" 
                            data-cost="<?= $p['cost_price'] ?>"
                            data-stock="<?= $p['stock_qty'] ?>">
                        <?= htmlspecialchars($p['item_name']) ?> | Price: KES <?= number_format($p['selling_price'], 2) ?> | Available: <?= $p['stock_qty'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="layout-three">
            <div class="form-group">
                <label>Sale Quantity</label>
                <input type="number" name="quantity" id="sale_qty" class="form-control" min="1" value="1" required>
            </div>
            <div class="form-group">
                <label>Unit Discount (KES)</label>
                <input type="number" step="0.01" name="unit_discount" id="sale_discount" class="form-control" value="0.00" min="0">
            </div>
            <div class="form-group">
                <label>Calculated Subtotal (KES)</label>
                <input type="text" id="calculated_total" class="form-control input-readonly" readonly value="0.00">
            </div>
        </div>

        <div class="indicator-strip">
            <span>Direct Cost: KES <strong id="preview_cost">0.00</strong></span>
            <span>Realized Margin: KES <strong id="preview_profit">0.00</strong></span>
        </div>

        <button type="submit" class="btn btn-primary mt-15">Confirm Transaction and Deduct Stock</button>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>