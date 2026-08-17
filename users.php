<?php
require_once __DIR__ . '/auth.php';

// Require a logged-in user to access this page
requireLogin();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Basic validation: ensure action and id are present
    $action = $_POST['action'] ?? '';
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    if ($action === 'delete' && $id > 0) {
        // Prevent CSRF and other checks would be recommended in production.
        if (deleteUserById($id)) {
            // If the deleted user was the current logged-in user, log them out
            if (isset($_SESSION['user']) && (int)$_SESSION['user']['id'] === $id) {
                logoutUser();
                header('Location: login.php?deleted_self=1');
                exit;
            }

            header('Location: users.php?deleted=1');
            exit;
        } else {
            $message = 'User not found or could not be deleted.';
        }
    }
}

// Fetch users from the database
$stmt = $pdo->query('SELECT id, full_name, email, created_at FROM users ORDER BY created_at DESC');
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Users - ShieldOS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-white">
  <div class="container">
    <a class="navbar-brand" href="index.php">ShieldOS</a>
    <div class="ms-auto">
      <a href="logout.php" class="btn btn-outline-light">Logout</a>
    </div>
  </div>
</nav>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Users</h3>
        <a href="index.php" class="btn btn-custom">Back to Dashboard</a>
    </div>

    <?php if (!empty($_GET['deleted'])): ?>
        <div class="alert alert-success">User deleted successfully.</div>
    <?php endif; ?>

    <?php if (!empty($_GET['deleted_self'])): ?>
        <div class="alert alert-success">Your account was deleted. You have been logged out.</div>
    <?php endif; ?>

    <?php if ($message): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if (count($users) === 0): ?>
        <div class="alert alert-info">No users found in the database.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Full name</th>
                        <th>Email</th>
                        <th>Created at</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td data-label="ID"><?= (int)$u['id'] ?></td>
                            <td data-label="Full name"><?= htmlspecialchars($u['full_name']) ?></td>
                            <td data-label="Email"><?= htmlspecialchars($u['email']) ?></td>
                            <td data-label="Created at"><?= htmlspecialchars($u['created_at']) ?></td>
                            <td data-label="Action" class="text-end">
                                <form method="POST" action="users.php" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');" style="display:inline-block">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
