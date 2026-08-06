<?php
require_once __DIR__ . '/auth.php';
header('Content-Type: application/json');

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
if (saveVaultEntry($user['id'], $label, $password)) {
    echo json_encode(['success' => true, 'message' => 'Password saved to vault.']);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Could not save password.']);
exit;
