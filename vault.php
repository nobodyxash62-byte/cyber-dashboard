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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
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
                <div class="vault-list">
                    <?php foreach ($entries as $entryNumber => $entry): ?>
                        <div class="vault-item">
                            <div class="vault-item-header">
                                <div class="vault-item-title-wrap">
                                    <span class="vault-badge">#<?= $entryNumber + 1 ?></span>
                                    <h6 class="vault-label mb-0"><?= htmlspecialchars($entry['label']) ?></h6>
                                </div>
                                <span class="vault-date"><?= htmlspecialchars($entry['created_at']) ?></span>
                            </div>

                            <div class="vault-password-box">
                                <span class="vault-password-text"><?= htmlspecialchars($entry['password']) ?></span>
                                <button type="button" class="btn btn-sm btn-outline-primary vault-copy-btn" data-password="<?= htmlspecialchars($entry['password'], ENT_QUOTES, 'UTF-8') ?>" title="Copy password"><i class="fa-solid fa-copy"></i> Copy</button>
                            </div>

                            <div class="vault-actions">
                                <form method="POST" action="vault.php" class="vault-delete-form">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="entry_id" value="<?= (int)$entry['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger"><i class="fa-solid fa-trash me-1"></i>Delete</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="text-center mt-4">
                <a href="index.php" class="btn btn-outline-primary">Back to Dashboard</a>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteVaultModal" tabindex="-1" aria-labelledby="deleteVaultModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteVaultModalLabel"><i class="fa-solid fa-trash text-danger me-2"></i>Delete Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Delete this saved password?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteVaultBtn">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
</body>
</html>
