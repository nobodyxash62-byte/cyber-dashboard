<?php
require_once __DIR__ . '/auth.php';
requireLogin();
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - ShieldOS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<script>
    window.SHIELDOS_USER = <?= json_encode($user) ?>;
</script>

<nav class="navbar navbar-expand-lg navbar-light bg-white">
  <div class="container">
    <a class="navbar-brand" href="index.php">ShieldOS</a>
    <div class="ms-auto d-flex align-items-center">
      <span class="me-3 fw-bold text-primary" id="navGreeting">Hi, <?= htmlspecialchars(explode(' ', $user['full_name'])[0]) ?></span>
      <a href="vault.php" class="btn btn-outline-primary me-2"><i class="fa-solid fa-vault me-1"></i> Vault</a>
      <a href="logout.php" class="btn btn-outline-danger"><i class="fa-solid fa-right-from-bracket me-1"></i> Logout</a>
    </div>
  </div>
</nav>

<div class="container py-5">
    <div class="row g-4">
        <!-- Generator Section -->
        <div class="col-lg-7">
            <div class="card card-custom">
                <div class="card-header-custom">
                    <i class="fa-solid fa-key me-2"></i> Password Generator
                </div>
                <div class="card-body card-body-custom">
                    <div class="mb-3">
                        <label for="fullName" class="form-label">Name for Seed Customization</label>
                        <input type="text" id="fullName" class="form-control form-control-custom" value="<?= htmlspecialchars($user['full_name']) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label d-flex justify-content-between">
                            <span>Password Length</span>
                            <span id="lengthDisplay" class="fw-bold text-primary">16</span>
                        </label>
                        <input type="range" class="form-range" id="lengthSlider" min="8" max="32" value="16">
                    </div>

                    <div class="row mb-4">
                        <div class="col-6">
                            <div class="form-check form-check-custom">
                                <input class="form-check-input" type="checkbox" id="genUppercase" checked>
                                <label class="form-check-label" for="genUppercase">Uppercase (A-Z)</label>
                            </div>
                            <div class="form-check form-check-custom">
                                <input class="form-check-input" type="checkbox" id="genLowercase" checked>
                                <label class="form-check-label" for="genLowercase">Lowercase (a-z)</label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-check form-check-custom">
                                <input class="form-check-input" type="checkbox" id="genNumbers" checked>
                                <label class="form-check-label" for="genNumbers">Numbers (0-9)</label>
                            </div>
                            <div class="form-check form-check-custom">
                                <input class="form-check-input" type="checkbox" id="genSymbols" checked>
                                <label class="form-check-label" for="genSymbols">Symbols (!@#$)</label>
                            </div>
                        </div>
                    </div>

                    <button id="generateBtn" class="btn btn-danger w-100 mb-3"><i class="fa-solid fa-wand-magic-sparkles me-2"></i>Generate Password</button>

                    <div class="generated-password">
                        <span id="generatedPassword" class="password-display">Click "Generate" to create a password</span>
                        <button id="copyBtn" class="btn btn-sm btn-outline-light ms-2" style="display:none;"><i class="fa-solid fa-copy me-1"></i>Copy</button>
                    </div>

                    <div class="mt-3 d-flex justify-content-between align-items-center">
                        <button id="savePasswordBtn" class="btn btn-custom" disabled><i class="fa-solid fa-bookmark me-1"></i> Save to Vault</button>
                        <span id="saveStatus" class="small text-muted"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Password Strength & Audit Section -->
        <div class="col-lg-5">
            <div class="card card-custom mb-4">
                <div class="card-header-custom">
                    <i class="fa-solid fa-shield-halved me-2"></i> Security Audit
                </div>
                <div class="card-body card-body-custom">
                    <div class="mb-3">
                        <label for="passwordInput" class="form-label">Audit Custom Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control form-control-custom" id="passwordInput" placeholder="Type password to check...">
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="fa-solid fa-eye" id="eyeIcon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span>Strength Rating</span>
                            <span id="strengthText" class="fw-bold">Awaiting Input</span>
                        </div>
                        <div class="progress progress-custom" style="height: 10px;">
                            <div id="strengthBar" class="progress-bar" role="progressbar" style="width: 0%"></div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-around small text-muted mb-4 border py-2 rounded">
                        <div>Length: <strong id="passLength">0</strong></div>
                        <div>Character Types: <strong id="passTypes">0</strong>/4</div>
                    </div>

                    <button id="auditBtn" class="btn btn-outline-light w-100"><i class="fa-solid fa-magnifying-glass me-2"></i>Check for Breaches</button>
                </div>
            </div>

            <div id="statusBox" class="status-box-default text-center">
                <h6 class="text-muted"><i class="fa-solid fa-circle-info me-2"></i>Audit Status</h6>
                <p class="small text-muted mb-0">Run a check to see if your password has appeared in known breach databases.</p>
            </div>
        </div>
    </div>
</div>

<!-- Save Modal -->
<div class="modal fade" id="saveVaultModal" tabindex="-1" aria-labelledby="saveVaultModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="saveVaultModalLabel"><i class="fa-solid fa-vault text-primary me-2"></i>Save Password to Vault</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="text-muted small">Specify which service or website this password belongs to.</p>
        <div class="mb-3">
            <label for="accountLabelInput" class="form-label">Account / Service Name</label>
            <input type="text" class="form-control form-control-custom" id="accountLabelInput" placeholder="e.g. Google, Netflix, Bank, Facebook" autofocus>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="confirmSaveVaultBtn">Save Password</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="script.js"></script>
</body>
</html>