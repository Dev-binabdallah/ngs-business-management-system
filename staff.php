<?php
require_once 'includes/header.php';
require_admin();

$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_user') {
        $fullname = trim($_POST['fullname']);
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        $role = $_POST['role'];

        if (!empty($fullname) && !empty($username) && !empty($password)) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            try {
                $stmt = $pdo->prepare("INSERT INTO users (fullname, username, password, role) VALUES (?, ?, ?, ?)");
                $stmt->execute([$fullname, $username, $hashed, $role]);
                $msg = "Staff account initialized.";
            } catch (PDOException $e) {
                $err = "Username already exists in system records.";
            }
        } else {
            $err = "All fields are required.";
        }
    }
}

if (isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    if ($del_id === (int)$_SESSION['user_id']) {
        $err = "Administrative action denied: Cannot remove current active session user.";
    } else {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$del_id]);
        $msg = "User record successfully removed.";
    }
}

$all_users = $pdo->query("SELECT id, fullname, username, role, created_at FROM users ORDER BY id ASC")->fetchAll();
?>

<div class="page-header">
    <h2>User Access Management (Administrator Only)</h2>
</div>

<?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-danger"><?= htmlspecialchars($err) ?></div><?php endif; ?>

<div class="layout-split">
    <div class="panel">
        <div class="panel-header">
            <h3>Register New User</h3>
        </div>
        <form action="staff.php" method="POST">
            <input type="hidden" name="action" value="add_user">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="fullname" class="form-control" required placeholder="e.g. John Doe">
            </div>
            <div class="form-group">
                <label>System Username</label>
                <input type="text" name="username" class="form-control" required placeholder="e.g. jdoe">
            </div>
            <div class="form-group">
                <label>Password (Will be securely hashed)</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Account Role</label>
                <select name="role" class="form-control">
                    <option value="staff">Staff (Sales/Inventory view only)</option>
                    <option value="admin">Administrator (Full Access)</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Create User Account</button>
        </form>
    </div>

    <div class="panel">
        <div class="panel-header">
            <h3>Authorized User Accounts</h3>
        </div>
        <div class="table-container">
            <table class="grid-table">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_users as $u): ?>
                        <tr>
                            <td><?= htmlspecialchars($u['fullname']) ?></td>
                            <td><?= htmlspecialchars($u['username']) ?></td>
                            <td><span class="tag <?= $u['role'] === 'admin' ? 'tag-admin' : 'tag-staff' ?>"><?= strtoupper($u['role']) ?></span></td>
                            <td>
                                <?php if ($u['id'] !== (int)$_SESSION['user_id']): ?>
                                    <a href="staff.php?delete=<?= $u['id'] ?>" 
                                       onclick="return confirm('Confirm removal of this user account?');"
                                       class="btn btn-sm btn-danger">Delete</a>
                                <?php else: ?>
                                    <span class="text-dim">Current Session</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>