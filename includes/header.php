<?php
require_once __DIR__ . '/../config.php';
check_auth();
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEW GENERATION MULTI-PURPOSE SHOP</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="navbar">
        <div class="nav-brand">
            <span class="brand-text">NEW GENERATION MULTI-PURPOSE SHOP</span>
        </div>
        <nav class="nav-links">
            <a href="index.php" class="<?= $current_page === 'index.php' ? 'active' : '' ?>">Dashboard</a>
            <a href="sales.php" class="<?= $current_page === 'sales.php' ? 'active' : '' ?>">Point of Sale</a>
            <a href="inventory.php" class="<?= $current_page === 'inventory.php' ? 'active' : '' ?>">Inventory</a>
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="staff.php" class="<?= $current_page === 'staff.php' ? 'active' : '' ?>">Staff Accounts</a>
                <a href="report.php" class="<?= $current_page === 'report.php' ? 'active' : '' ?>">Financial Audit</a>
            <?php endif; ?>
        </nav>
        <div class="nav-user">
            <span class="user-info"><?= htmlspecialchars($_SESSION['fullname']) ?> [<?= strtoupper($_SESSION['role']) ?>]</span>
            <a href="logout.php" class="btn btn-sm btn-outline">Exit</a>
        </div>
    </header>
    <main class="container">