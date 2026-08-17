<?php
require_once __DIR__ . '/auth.php';
requireLogin();
$user = currentUser();
$savedPasswords = getVaultEntries($user['id']);
$emailStatus = $_SESSION['email_status'] ?? '';
$emailStatusDetail = $_SESSION['email_status_detail'] ?? '';
unset($_SESSION['email_status'], $_SESSION['email_status_detail']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShieldOS - Cyber Security Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="#home">
                <i class="fa-solid fa-shield me-2"></i>ShieldOS
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="#home">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#audit">Password Audit</a></li>
                    <li class="nav-item"><a class="nav-link" href="#vault">Vault</a></li>
                    <li class="nav-item"><a class="nav-link" href="#generator">Generator</a></li>
                    <li class="nav-item ms-lg-3">
                        <span id="navGreeting" class="nav-link text-muted">Hi, <?= htmlspecialchars(explode(' ', trim($user['full_name']))[0]) ?></span>
                    </li>
                        <li class="nav-item">
                            <a id="logoutBtn" class="btn btn-outline-primary btn-sm ms-2" href="logout.php">Logout</a>
                        </li>
                </ul>
            </div>
        </div>
    </nav>

    <section id="home" class="hero-section">
        <div class="container h-100">
            <div class="row h-100 align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4">
                        <span class="text-primary">Secure</span> Your Digital Identity
                    </h1>
                    <p class="lead mb-4">Advanced cybersecurity tools designed to protect your passwords and personal data from modern threats.</p>
                    <div class="button-group">
                        <a href="#audit" class="btn btn-danger btn-lg me-3">
                            <i class="fa-solid fa-shield-virus me-2"></i>Start Audit
                        </a>
                        <a href="#generator" class="btn btn-outline-light btn-lg">
                            <i class="fa-solid fa-key me-2"></i>Generate Password
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <i class="fa-solid fa-lock display-1 text-primary opacity-25"></i>
                </div>
            </div>  
        </div>
    </section>

    <section id="audit" class="section-spacing">
        <div class="container">
            <h2 class="section-title text-center mb-5">
                <i class="fa-solid fa-shield-halved me-3"></i>Password Vulnerability Audit
            </h2>

            <div class="row g-4 align-items-start">
                <div class="col-lg-7">
                    <div class="card card-custom">
                        <div class="card-header-custom">
                            <h5 class="mb-0"><i class="fa-solid fa-magnifying-glass me-2"></i>Analyze Password Strength</h5>
                        </div>
                        <div class="card-body-custom">
                            <div class="mb-4">
                                <label for="passwordInput" class="form-label">Enter Password to Analyze:</label>
                                <div class="input-group mb-3">
                                    <input type="password" class="form-control form-control-custom" id="passwordInput" placeholder="Enter your password...">
                                    <button class="btn btn-outline-secondary btn-custom" type="button" id="togglePassword">
                                        <i class="fa-solid fa-eye" id="eyeIcon"></i>
                                    </button>
                                </div>
                                <small class="text-muted">Enter a password here to audit and save it locally in your dashboard vault.</small>
                            </div>
                            <div class="d-grid gap-2 mb-4">
                                <button class="btn btn-outline-primary btn-lg" type="button" id="savePasswordBtn">
                                    <i class="fa-solid fa-lock me-2"></i>Save to Vault
                                </button>
                                <div class="small text-muted" id="saveStatus" aria-live="polite">Enter or generate a password, then click Save to Vault.</div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label mb-2">Strength Indicator:</label>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="small text-muted">Entropy Level</span>
                                    <span id="strengthText" class="fw-bold text-primary">Awaiting Input</span>
                                </div>
                                <div class="progress progress-custom" style="height: 12px;">
                                    <div id="strengthBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                                </div>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-sm-6">
                                    <div class="stats-box">
                                        <div class="stats-number" id="passLength">0</div>
                                        <div class="stats-label">Characters</div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="stats-box">
                                        <div class="stats-number" id="passTypes">0</div>
                                        <div class="stats-label">Types</div>
                                    </div>
                                </div>
                            </div>

                            <button class="btn btn-danger btn-lg w-100 fw-bold" id="auditBtn">
                                <i class="fa-solid fa-shield-virus me-2"></i>RUN SECURITY AUDIT
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="card card-custom">
                        <div class="card-header-custom">
                            <h5 class="mb-0"><i class="fa-solid fa-circle-info me-2"></i>Audit Results</h5>
                        </div>
                        <div class="card-body-custom">
                            <div id="statusBox" class="status-box-default">
                                <h6 class="text-warning mb-2"><i class="fa-solid fa-triangle-exclamation me-2"></i>Ready to Audit</h6>
                                <p class="small mb-0">Enter a password and click the audit button to check for public breaches.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="generator" class="section-spacing section-alt">
        <div class="container">
            <h2 class="section-title text-center mb-5">
                <i class="fa-solid fa-wand-magic-sparkles me-3"></i>Secure Password Generator
            </h2>

            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="card card-custom">
                        <div class="card-header-custom">
                            <h5 class="mb-0"><i class="fa-solid fa-key me-2"></i>Generate Strong Password</h5>
                        </div>
                        <div class="card-body-custom">
                            <div class="mb-4">
                                <label for="fullName" class="form-label">Your Full Name:</label>
                                <input type="text" class="form-control form-control-custom" id="fullName" placeholder="e.g., John David Smith">
                                <small class="text-muted">Your name will be incorporated into the password to make it more memorable.</small>
                            </div>

                            <div class="mb-4">
                                <label class="form-label mb-3">Customize Your Password:</label>

                                <div class="mb-3">
                                    <label class="form-label">Password Length</label>
                                    <div class="d-flex align-items-center">
                                        <input type="range" class="form-range" id="lengthSlider" min="8" max="32" value="16">
                                        <span class="ms-3 fw-bold text-primary" id="lengthDisplay">16</span>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label mb-2">Include:</label>
                                    <div class="form-check form-check-custom">
                                        <input class="form-check-input" type="checkbox" id="genUppercase" checked>
                                        <label class="form-check-label" for="genUppercase">Uppercase Letters (A-Z)</label>
                                    </div>
                                    <div class="form-check form-check-custom">
                                        <input class="form-check-input" type="checkbox" id="genLowercase" checked>
                                        <label class="form-check-label" for="genLowercase">Lowercase Letters (a-z)</label>
                                    </div>
                                    <div class="form-check form-check-custom">
                                        <input class="form-check-input" type="checkbox" id="genNumbers" checked>
                                        <label class="form-check-label" for="genNumbers">Numbers (0-9)</label>
                                    </div>
                                    <div class="form-check form-check-custom">
                                        <input class="form-check-input" type="checkbox" id="genSymbols" checked>
                                        <label class="form-check-label" for="genSymbols">Symbols (!@#$%^&*)</label>
                                    </div>
                                </div>

                                <button class="btn btn-danger btn-lg w-100 fw-bold mb-3" id="generateBtn">
                                    <i class="fa-solid fa-rotate me-2"></i>GENERATE PASSWORD
                                </button>

                                <div class="generated-password">
                                    <div class="password-display">
                                        <span id="generatedPassword">Click "Generate" to create a password</span>
                                    </div>
                                    <button class="btn btn-sm btn-outline-secondary" id="copyBtn" style="display:none;">
                                        <i class="fa-solid fa-copy me-1"></i>Copy
                                    </button>
                                </div>
                                <div class="mt-4">
                                    <p class="small text-muted">After generating a password, use the Save to Vault button in the audit section above.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="vault" class="section-spacing section-alt">
        <div class="container">
            <h2 class="section-title text-center mb-5">
                <i class="fa-solid fa-folder-open me-3"></i>Saved Password Vault
            </h2>

            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="card card-custom">
                        <div class="card-header-custom">
                            <h5 class="mb-0"><i class="fa-solid fa-lock me-2"></i>Your saved passwords in the system</h5>
                        </div>
                        <div class="card-body-custom">
                            <?php if ($emailStatus === 'sent'): ?>
                                <div class="alert alert-success">Password sent to your email successfully.</div>
                            <?php elseif ($emailStatus === 'placeholder'): ?>
                                <div class="alert alert-danger">
                                    SMTP is not configured correctly. Open mail_config.php and fix the settings.
                                    <?php if ($emailStatusDetail): ?>
                                        <div class="small text-muted"><?= htmlspecialchars($emailStatusDetail) ?></div>
                                    <?php endif; ?>
                                </div>
                            <?php elseif ($emailStatus === 'smtp'): ?>
                                <div class="alert alert-danger">
                                    SMTP login failed. Check your Gmail App Password, 2-Step Verification, and Gmail address in mail_config.php.
                                    <?php if ($emailStatusDetail): ?>
                                        <div class="small text-muted"><?= htmlspecialchars($emailStatusDetail) ?></div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            <p class="text-muted mb-4">Your saved passwords are stored in the vault and can be emailed to your account.</p>

                            <div id="vaultContent">
                                <?php if (count($savedPasswords) === 0): ?>
                                    <div class="alert alert-info" id="vaultEmptyMessage">
                                        No saved passwords found yet. Generate a password and save it using the button above.
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped align-middle">
                                            <thead>
                                                <tr>
                                                    <th>Label</th>
                                                    <th>Password</th>
                                                    <th>Saved at</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($savedPasswords as $entry): ?>
                                                    <tr>
                                                        <td data-label="Label"><?= htmlspecialchars($entry['label']) ?></td>
                                                        <td data-label="Password"><code><?= htmlspecialchars($entry['password']) ?></code></td>
                                                        <td data-label="Saved at"><?= htmlspecialchars($entry['created_at']) ?></td>
                                                        <td data-label="Action">
                                                            <form method="POST" action="send_to_email.php">
                                                                <input type="hidden" name="entry_id" value="<?= (int)$entry['id'] ?>">
                                                                <button type="submit" class="btn btn-sm btn-outline-primary">Send to My Email</button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Expose current server user to client scripts when available
        window.SHIELDOS_USER = <?= json_encode(['id' => $user['id'], 'email' => $user['email'], 'full_name' => $user['full_name']]); ?>;
    </script>
    <script src="script.js"></script>
</body>
</html>
