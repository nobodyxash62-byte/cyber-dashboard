<?php
require_once __DIR__ . '/auth.php';
requireLogin();
$user = currentUser();

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $entryId = isset($_POST['entry_id']) ? (int)$_POST['entry_id'] : 0;

    if ($action === 'delete' && $entryId > 0) {
        if (deleteVaultEntry($entryId, $user['id'])) {
            header('Location: vault.php?deleted=1');
            exit;
        }
        $message = 'Unable to delete the selected password entry.';
    }
}

$entries = getVaultEntries($user['id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vault - ShieldOS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">
    <div class="container auth-wrapper">
        <div class="auth-card">
            <div class="auth-card-top text-center mb-4">
                <div class="auth-icon mb-3"><i class="fa-solid fa-lock-open"></i></div>
                <h2 class="mb-2 text-primary">Your Password Vault</h2>
                <p class="text-muted">Saved generated passwords are encrypted and available only when you are logged in.</p>
            </div>

            <?php if (!empty($_GET['deleted'])): ?>
                <div class="alert alert-success" role="alert">Password saved entry deleted successfully.</div>
            <?php endif; ?>

            <?php if ($message): ?>
                <div class="alert alert-danger" role="alert"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <?php if (count($entries) === 0): ?>
                <div class="alert alert-info">No saved passwords found. Generate one and save it from the dashboard.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Label</th>
                                <th>Saved at</th>
                                <th>Password</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($entries as $entry): ?>
                                <tr>
                                    <td><?= htmlspecialchars($entry['label']) ?></td>
                                    <td><?= htmlspecialchars($entry['created_at']) ?></td>
                                    <td><code><?= htmlspecialchars($entry['password']) ?></code></td>
                                    <td class="text-end">
                                        <form method="POST" action="vault.php" onsubmit="return confirm('Delete this saved password?');" style="display:inline-block;">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="entry_id" value="<?= (int)$entry['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <div class="text-center mt-4">
                <a href="index.php" class="btn btn-outline-primary">Back to Dashboard</a>
            </div>
        </div>
    </div>
</body>
</html>
