<?php
require_once __DIR__ . '/auth.php';

if (currentUser()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } else {
        $result = registerUser($fullName, $email, $password);
        if ($result['success']) {
            // After successful registration, require the user to log in.
            // Redirect to login page with a query flag to show a success message.
            header('Location: login.php?registered=1');
            exit;
        }
        $error = $result['message'] ?? 'Registration failed.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - ShieldOS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">
    <div class="container auth-wrapper">
        <div class="auth-card">
            <div class="auth-card-top text-center mb-4">
                <div class="auth-icon mb-3"><i class="fa-solid fa-user-plus"></i></div>
                <h2 class="mb-2 text-primary">Create Your Account</h2>
                <p class="text-muted">Register to use ShieldOS and protect your passwords.</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="signup.php" novalidate>
                <div class="mb-3">
                    <label for="full_name" class="form-label">Full Name</label>
                    <input type="text" class="form-control form-control-custom" id="full_name" name="full_name" required autocomplete="name" autofocus>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email address</label>
                    <input type="email" class="form-control form-control-custom" id="email" name="email" required autocomplete="email">
                </div>
                <div class="mb-3">
                    <label for="passwordInput" class="form-label">Password</label>
                    <div class="input-group">
                        <input type="password" class="form-control form-control-custom" id="passwordInput" name="password" required minlength="8" autocomplete="new-password" aria-describedby="togglePassword">
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword" aria-label="Toggle password visibility">
                            <i class="fa-solid fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                    <div class="form-text">Password must be at least 8 characters.</div>
                </div>
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control form-control-custom" id="confirm_password" name="confirm_password" required autocomplete="new-password" minlength="8">
                </div>
                <button type="submit" class="btn btn-danger w-100">Sign Up</button>
            </form>

            <p class="mt-4 text-center text-muted">Already have an account? <a href="login.php">Login</a></p>
        </div>
    </div>
</body>
</html>
