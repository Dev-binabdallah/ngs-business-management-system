<?php
require_once 'includes/header.php';
require_admin();

$from_date = $_GET['from_date'] ?? date('Y-m-01');
$to_date = $_GET['to_date'] ?? date('Y-m-d');

$query = "
    SELECT s.*, p.item_name, u.fullname AS staff_name
    FROM sales s
    JOIN products p ON s.product_id = p.id
    JOIN users u ON s.user_id = u.id
    WHERE DATE(s.sale_date) BETWEEN ? AND ?
    ORDER BY s.id ASC
";
$stmt = $pdo->prepare($query);
$stmt->execute([$from_date, $to_date]);
$records = $stmt->fetchAll();

$sum_rev = 0;
$sum_cost = 0;
$sum_profit = 0;
$sum_discount = 0;

foreach ($records as $r) {
    $sum_rev += $r['total_amount'];
    $sum_cost += $r['total_cost'];
    $sum_profit += $r['gross_profit'];
    $sum_discount += ($r['unit_discount'] * $r['quantity']);
}
?>

<div class="toolbar-box no-print">
    <form method="GET" action="report.php" class="filter-strip">
        <label>From: <input type="date" name="from_date" value="<?= htmlspecialchars($from_date) ?>" class="filter-input"></label>
        <label>To: <input type="date" name="to_date" value="<?= htmlspecialchars($to_date) ?>" class="filter-input"></label>
        <button type="submit" class="btn btn-primary">Run Audit</button>
        <button type="button" onclick="window.print()" class="btn btn-success">Print / Save as PDF</button>
    </form>
</div>

<div class="document-sheet">
    <div class="document-header">
        <h1>NEW GENERATION MULTI-PURPOSE SHOP</h1>
        <h2>OFFICIAL FINANCIAL PERFORMANCE & INVENTORY AUDIT</h2>
        <p>Audit Window: <strong><?= date('d M Y', strtotime($from_date)) ?></strong> through <strong><?= date('d M Y', strtotime($to_date)) ?></strong></p>
        <p>Auditor: <?= htmlspecialchars($_SESSION['fullname']) ?> | Generated: <?= date('d-M-Y H:i:s') ?></p>
    </div>

    <table class="financial-summary-table">
        <tr>
            <td>Total Net Revenue (Billed to Customers):</td>
            <td class="text-right"><strong>KES <?= number_format($sum_rev, 2) ?></strong></td>
        </tr>
        <tr>
            <td>Direct Cost of Goods Sold (COGS):</td>
            <td class="text-right"><strong>KES <?= number_format($sum_cost, 2) ?></strong></td>
        </tr>
        <tr>
            <td>Cumulative Discounts Conceded:</td>
            <td class="text-right">KES <?= number_format($sum_discount, 2) ?></td>
        </tr>
        <tr class="summary-final-row">
            <td><strong>Net Realized Gross Margin:</strong></td>
            <td class="text-right"><strong>KES <?= number_format($sum_profit, 2) ?></strong></td>
        </tr>
    </table>

    <h3 class="mt-20">Transaction Breakdown</h3>
    <table class="grid-table document-table">
        <thead>
            <tr>
                <th>Receipt #</th>
                <th>Date</th>
                <th>Item</th>
                <th>Staff</th>
                <th>Units</th>
                <th>List Price</th>
                <th>Discount</th>
                <th>Net Price</th>
                <th>Total Billed</th>
                <th>Stock Cost</th>
                <th>Gross Margin</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($records)): ?>
                <tr><td colspan="11" class="text-center">No sales entries recorded for selected parameters.</td></tr>
            <?php else: ?>
                <?php foreach ($records as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['receipt_no']) ?></td>
                        <td><?= date('d/m/Y', strtotime($row['sale_date'])) ?></td>
                        <td><?= htmlspecialchars($row['item_name']) ?></td>
                        <td><?= htmlspecialchars($row['staff_name']) ?></td>
                        <td><?= $row['quantity'] ?></td>
                        <td><?= number_format($row['unit_price'], 2) ?></td>
                        <td><?= number_format($row['unit_discount'], 2) ?></td>
                        <td><?= number_format($row['final_unit_price'], 2) ?></td>
                        <td><?= number_format($row['total_amount'], 2) ?></td>
                        <td><?= number_format($row['total_cost'], 2) ?></td>
                        <td class="<?= $row['gross_profit'] >= 0 ? 'text-profit' : 'text-loss' ?>">
                            <?= number_format($row['gross_profit'], 2) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="authorization-block">
        <div class="sig-space">
            <p>Prepared By: ___________________________________</p>
        </div>
        <div class="sig-space">
            <p>Management Signature: ___________________________</p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>