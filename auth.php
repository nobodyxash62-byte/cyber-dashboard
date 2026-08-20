<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';

function currentUser() {
    return $_SESSION['user'] ?? null;
}

function requireLogin() {
    if (!currentUser()) {
        header('Location: login.php');
        exit;
    }
}

function registerUser($fullName, $email, $password) {
    global $pdo;
    
    if (strlen($password) < 8) {
        return ['success' => false, 'message' => 'Password must be at least 8 characters long.'];
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare('INSERT INTO users (full_name, email, password_hash) VALUES (:full_name, :email, :hash)');
        $stmt->execute([
            'full_name' => $fullName,
            'email' => $email,
            'hash' => $hash
        ]);
        return ['success' => true];
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            return ['success' => false, 'message' => 'An account with that email already exists.'];
        }
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

function loginUser($email, $password) {
    global $pdo;

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email');
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        unset($user['password_hash']);
        $_SESSION['user'] = $user;
        return ['success' => true, 'user' => $user];
    }

    return ['success' => false, 'message' => 'Invalid email or password.'];
}

function logoutUser() {
    unset($_SESSION['user']);
    session_destroy();
}

function getVaultEntries($userId) {
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM vault_entries WHERE user_id = :user_id ORDER BY created_at DESC');
    $stmt->execute(['user_id' => $userId]);
    return $stmt->fetchAll();
}

function saveVaultEntry($userId, $label, $password) {
    global $pdo;
    $stmt = $pdo->prepare('INSERT INTO vault_entries (user_id, label, password) VALUES (:user_id, :label, :password)');
    return $stmt->execute([
        'user_id' => $userId,
        'label' => $label ?: 'Saved password',
        'password' => $password
    ]);
}

function deleteVaultEntry($entryId, $userId) {
    global $pdo;
    $stmt = $pdo->prepare('DELETE FROM vault_entries WHERE id = :id AND user_id = :user_id');
    return $stmt->execute(['id' => $entryId, 'user_id' => $userId]);
}

function deleteUserById($userId) {
    global $pdo;
    $stmt = $pdo->prepare('DELETE FROM users WHERE id = :id');
    return $stmt->execute(['id' => $userId]);
}
?>