<?php
require_once 'includes/header.php';

$total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$low_stock = $pdo->query("SELECT COUNT(*) FROM products WHERE stock_qty <= 5")->fetchColumn();

$sales_stmt = $pdo->query("
    SELECT 
        COALESCE(SUM(total_amount), 0) AS total_revenue,
        COALESCE(SUM(total_cost), 0) AS total_cost,
        COALESCE(SUM(gross_profit), 0) AS total_profit
    FROM sales
");
$metrics = $sales_stmt->fetch();

$recent_sales = $pdo->query("
    SELECT s.*, p.item_name, u.username 
    FROM sales s
    JOIN products p ON s.product_id = p.id
    JOIN users u ON s.user_id = u.id
    ORDER BY s.id DESC LIMIT 5
")->fetchAll();
?>

<div class="page-header">
    <h2>Management Dashboard</h2>
</div>

<div class="kpi-grid">
    <div class="kpi-box">
        <span class="kpi-label">Gross Revenue</span>
        <span class="kpi-value">KES <?= number_format($metrics['total_revenue'], 2) ?></span>
    </div>
    <div class="kpi-box">
        <span class="kpi-label">Direct Cost (COGS)</span>
        <span class="kpi-value">KES <?= number_format($metrics['total_cost'], 2) ?></span>
    </div>
    <div class="kpi-box">
        <span class="kpi-label">Gross Profit / Margin</span>
        <span class="kpi-value <?= $metrics['total_profit'] >= 0 ? 'text-profit' : 'text-loss' ?>">
            KES <?= number_format($metrics['total_profit'], 2) ?>
        </span>
    </div>
    <div class="kpi-box">
        <span class="kpi-label">Low Stock Alerts (&le; 5)</span>
        <span class="kpi-value <?= $low_stock > 0 ? 'text-loss' : '' ?>"><?= $low_stock ?> Items</span>
    </div>
</div>

<div class="panel mt-20">
    <div class="panel-header">
        <h3>Recent Completed Transactions</h3>
    </div>
    <div class="table-container">
        <table class="grid-table">
            <thead>
                <tr>
                    <th>Receipt Number</th>
                    <th>Date / Time</th>
                    <th>Item Description</th>
                    <th>Units</th>
                    <th>Discount/Unit</th>
                    <th>Total Charged</th>
                    <th>Profit</th>
                    <th>Operator</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recent_sales)): ?>
                    <tr><td colspan="8" class="text-center">No transaction records logged.</td></tr>
                <?php else: ?>
                    <?php foreach ($recent_sales as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['receipt_no']) ?></td>
                            <td><?= date('d-M-Y H:i', strtotime($row['sale_date'])) ?></td>
                            <td><?= htmlspecialchars($row['item_name']) ?></td>
                            <td><?= $row['quantity'] ?></td>
                            <td>KES <?= number_format($row['unit_discount'], 2) ?></td>
                            <td>KES <?= number_format($row['total_amount'], 2) ?></td>
                            <td class="<?= $row['gross_profit'] >= 0 ? 'text-profit' : 'text-loss' ?>">
                                KES <?= number_format($row['gross_profit'], 2) ?>
                            </td>
                            <td><?= htmlspecialchars($row['username']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>