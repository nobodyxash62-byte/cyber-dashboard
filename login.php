<?php
require_once __DIR__ . '/auth.php';

if (currentUser()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $res = loginUser($email, $password);
    if ($res['success']) {
        header('Location: index.php');
        exit;
    }
    $error = $res['message'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ShieldOS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">
    <div class="container auth-wrapper">
        <div class="auth-card">
            <div class="auth-card-top text-center mb-4">
                <div class="auth-icon mb-3"><i class="fa-solid fa-lock"></i></div>
                <span class="d-inline-block px-3 py-1 rounded-pill bg-primary-subtle text-primary fw-semibold small mb-3">Secure Access</span>
                <h2 class="mb-2 text-primary">Welcome back</h2>
                <p class="text-muted mb-0">Sign in to your ShieldOS account and access your vault.</p>
            </div>

            <div id="loginMessage" class="d-none"></div>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form id="loginForm" method="POST" action="login.php" class="mt-3">
                <div class="mb-3">
                    <label for="loginEmail" class="form-label">Email address</label>
                    <input type="email" class="form-control form-control-custom" id="loginEmail" name="email" autocomplete="email" required autofocus>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label for="loginPassword" class="form-label mb-0">Password</label>
                        <a href="#" class="small text-decoration-none">Need help?</a>
                    </div>
                    <div class="input-group">
                        <input type="password" class="form-control form-control-custom" id="loginPassword" name="password" autocomplete="current-password" required>
                        <button class="btn btn-outline-secondary" type="button" id="loginTogglePassword" aria-label="Toggle password visibility">
                            <i class="fa-solid fa-eye" id="loginEyeIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="rememberMe">
                        <label class="form-check-label small text-muted" for="rememberMe">Remember me</label>
                    </div>
                    <small class="text-muted">Use your account email</small>
                </div>

                <button type="submit" class="btn btn-danger w-100"><i class="fa-solid fa-right-to-bracket me-2"></i>Sign In</button>
            </form>

            <p class="mt-4 text-center text-muted mb-0">Don't have an account? <a href="signup.php">Create one</a></p>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="script.js"></script>
</body>
</html>