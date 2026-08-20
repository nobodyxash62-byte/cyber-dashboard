<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db.php';

function currentUser() {
    return $_SESSION['user'] ?? null;
}

function requireLogin() {
    if (!isset($_SESSION['user'])) {
        header('Location: login.php');
        exit;
    }
}

function getUserByEmail(string $email) {
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email');
    $stmt->execute(['email' => $email]);
    return $stmt->fetch();
}

function getVaultCipherKey(): string {
    $key = defined('VAULT_SECRET_KEY') ? VAULT_SECRET_KEY : 'shieldos-default-vault-secret';
    return substr(hash('sha256', $key, true), 0, 32);
}

function encryptVaultPassword(string $password): string {
    $cipher = 'aes-256-cbc';
    $key = getVaultCipherKey();
    $ivLength = openssl_cipher_iv_length($cipher);
    $iv = random_bytes($ivLength);
    $encrypted = openssl_encrypt($password, $cipher, $key, OPENSSL_RAW_DATA, $iv);
    return base64_encode($iv . $encrypted);
}

function decryptVaultPassword(string $payload): string {
    $cipher = 'aes-256-cbc';
    $key = getVaultCipherKey();
    $decoded = base64_decode($payload, true);
    if ($decoded === false) {
        return '';
    }
    $ivLength = openssl_cipher_iv_length($cipher);
    $iv = substr($decoded, 0, $ivLength);
    $encrypted = substr($decoded, $ivLength);
    $decrypted = openssl_decrypt($encrypted, $cipher, $key, OPENSSL_RAW_DATA, $iv);
    return $decrypted === false ? '' : $decrypted;
}

function getVaultEntries(int $userId): array {
    global $pdo;
    $stmt = $pdo->prepare('SELECT id, label, password, created_at FROM vault_entries WHERE user_id = :user_id ORDER BY created_at DESC');
    $stmt->execute(['user_id' => $userId]);
    $rows = $stmt->fetchAll();
    foreach ($rows as &$row) {
        $row['password'] = decryptVaultPassword($row['password']);
    }
    return $rows;
}

function saveVaultEntry(int $userId, string $label, string $password): bool {
    global $pdo;
    $label = trim($label) ?: 'Saved password';
    $encryptedPassword = encryptVaultPassword($password);
    $stmt = $pdo->prepare('INSERT INTO vault_entries (user_id, label, password) VALUES (:user_id, :label, :password)');
    return $stmt->execute([
        'user_id' => $userId,
        'label' => $label,
        'password' => $encryptedPassword,
    ]);
}

function deleteVaultEntry(int $id, int $userId): bool {
    global $pdo;
    $stmt = $pdo->prepare('DELETE FROM vault_entries WHERE id = :id AND user_id = :user_id');
    return $stmt->execute(['id' => $id, 'user_id' => $userId]);
}

function deleteUserById(int $id): bool {
    global $pdo;
    try {
        $pdo->beginTransaction();
        $stmt1 = $pdo->prepare('DELETE FROM vault_entries WHERE user_id = :id');
        $stmt2 = $pdo->prepare('DELETE FROM users WHERE id = :id');
        $ok1 = $stmt1->execute(['id' => $id]);
        $ok2 = $stmt2->execute(['id' => $id]);
        if ($ok1 && $ok2) {
            $pdo->commit();
            return true;
        }
        $pdo->rollBack();
        return false;
    } catch (PDOException $e) {
        $pdo->rollBack();
        return false;
    }
}

function registerUser(string $fullName, string $email, string $password): array {
    global $pdo;

    $fullName = trim($fullName);
    $email = trim(strtolower($email));

    if ($fullName === '') {
        return ['success' => false, 'message' => 'Please enter your full name.'];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Please enter a valid email address.'];
    }

    if (strlen($password) < 8) {
        return ['success' => false, 'message' => 'Password must be at least 8 characters long.'];
    }

    if (getUserByEmail($email)) {
        return ['success' => false, 'message' => 'An account with that email already exists.'];
    }
    try {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users (full_name, email, password_hash) VALUES (:full_name, :email, :password_hash)');
        $ok = $stmt->execute([
            'full_name' => $fullName,
            'email' => $email,
            'password_hash' => $passwordHash,
        ]);

        if ($ok) {
            return ['success' => true];
        }

        return ['success' => false, 'message' => 'Failed to create account. Please try again.'];
    } catch (PDOException $e) {
        $msg = $e->getMessage();
        if (strpos($msg, 'UNIQUE') !== false || strpos($msg, 'unique') !== false) {
            return ['success' => false, 'message' => 'An account with that email already exists.'];
        }
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

function loginUser(string $email, string $password): bool {
    $user = getUserByEmail($email);
    if (!$user) {
        return false;
    }

    if (!password_verify($password, $user['password_hash'])) {
        return false;
    }

    $_SESSION['user'] = [
        'id' => $user['id'],
        'full_name' => $user['full_name'],
        'email' => $user['email'],
    ];

    return true;
}

function logoutUser() {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}
?>