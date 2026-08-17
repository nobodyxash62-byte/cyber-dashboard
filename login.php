<?php
require_once __DIR__ . '/auth.php';

// If already logged in, redirect to dashboard unless caller requests the login form explicitly
if (currentUser() && !isset($_GET['force'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if (isset($_GET['registered']) && $_GET['registered'] == '1') {
    $success = 'Account created successfully. Please log in.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (loginUser($email, $password)) {
        header('Location: index.php');
        exit;
    }

    $error = 'Invalid email or password.';
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
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">
    <div class="container auth-wrapper">
        <div class="auth-card">
            <div class="auth-card-top text-center mb-4">
                <div class="auth-icon mb-3"><i class="fa-solid fa-lock"></i></div>
                <h2 class="mb-2 text-primary">Welcome Back</h2>
                <p class="text-muted">Sign in to access your ShieldOS dashboard securely.</p>
            </div>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success" role="alert"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="login.php" novalidate>
                <div class="mb-3">
                    <label for="email" class="form-label">Email address</label>
                    <input type="email" class="form-control form-control-custom" id="email" name="email" required autocomplete="email" autofocus>
                </div>

                <div class="mb-3">
                    <label for="passwordInput" class="form-label">Password</label>
                    <div class="input-group">
                        <input type="password" class="form-control form-control-custom" id="passwordInput" name="password" required minlength="8" autocomplete="current-password" aria-describedby="togglePassword">
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword" aria-label="Toggle password visibility">
                            <i class="fa-solid fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>
                    <div><a href="#" class="small">Forgot password?</a></div>
                </div>

                <button type="submit" class="btn btn-danger w-100">Login</button>
            </form>

            <p class="mt-4 text-center text-muted">Don’t have an account? <a href="signup.php">Sign up</a></p>
        </div>
    </div>
</body>
</html>
