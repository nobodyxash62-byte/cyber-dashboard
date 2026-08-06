<?php
require_once __DIR__ . '/auth.php';
header('Content-Type: application/json; charset=utf-8');

// Basic method and auth checks
if (!currentUser()) {
    echo json_encode(['success' => false, 'message' => 'Authentication required.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$password = $_POST['password'] ?? '';
$label = $_POST['label'] ?? '';

if (trim($password) === '') {
    echo json_encode(['success' => false, 'message' => 'Password is required.']);
    exit;
}

$user = currentUser();
try {
    $ok = saveVaultEntry($user['id'], $label, $password);
    if ($ok) {
        echo json_encode(['success' => true, 'message' => 'Password saved to vault.']);
        exit;
    }
    // If saveVaultEntry returned false without exception, return a useful message
    error_log('save_password.php: saveVaultEntry returned false for user_id=' . $user['id']);
    echo json_encode(['success' => false, 'message' => 'Could not save password (unknown error).']);
    exit;
} catch (Exception $e) {
    // Log the full exception server-side for debugging and return a safe message client-side
    $msg = 'Exception while saving vault entry: ' . $e->getMessage();
    error_log($msg);
    echo json_encode(['success' => false, 'message' => 'Server error while saving password: ' . $e->getMessage()]);
    exit;
}
