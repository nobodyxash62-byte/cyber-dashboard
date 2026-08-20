document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('loginForm')) {
        initLoginPage();
        return;
    }

    if (document.getElementById('signupForm')) {
        initSignupPage();
        return;
    }

    if (document.getElementById('savePasswordBtn') || document.getElementById('vaultContent')) {
        initDashboardPage();
        return;
    }

    if (document.querySelector('.vault-copy-btn') || document.querySelector('.vault-delete-form')) {
        initVaultPage();
        return;
    }

    initPublicPage();
});

function initPublicPage() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) target.scrollIntoView({ behavior: 'smooth' });
        });
    });
}

function initLoginPage() {
    const toggleButton = document.getElementById('loginTogglePassword');
    const passwordInput = document.getElementById('loginPassword');
    const eyeIcon = document.getElementById('loginEyeIcon');
    const messageEl = document.getElementById('loginMessage');

    showQueryMessage(messageEl);
    setPasswordToggle(toggleButton, passwordInput, eyeIcon);
}

function initSignupPage() {
    const toggleButton = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('passwordInput');
    const eyeIcon = document.getElementById('eyeIcon');

    setPasswordToggle(toggleButton, passwordInput, eyeIcon);
}

function initDashboardPage() {
    const user = window.SHIELDOS_USER || null;
    const savePasswordBtn = document.getElementById('savePasswordBtn');
    const copyBtn = document.getElementById('copyBtn');
    const passwordInput = document.getElementById('passwordInput');
    const generatedPassword = document.getElementById('generatedPassword');
    const fullNameField = document.getElementById('fullName');
    const lengthSlider = document.getElementById('lengthSlider');
    const lengthDisplay = document.getElementById('lengthDisplay');
    const genUppercase = document.getElementById('genUppercase');
    const genLowercase = document.getElementById('genLowercase');
    const genNumbers = document.getElementById('genNumbers');
    const genSymbols = document.getElementById('genSymbols');
    const generateBtn = document.getElementById('generateBtn');
    const auditBtn = document.getElementById('auditBtn');
    const statusBox = document.getElementById('statusBox');
    const strengthBar = document.getElementById('strengthBar');
    const strengthText = document.getElementById('strengthText');
    const passLength = document.getElementById('passLength');
    const passTypes = document.getElementById('passTypes');
    const saveStatus = document.getElementById('saveStatus');

    showQueryToast();

    setPasswordToggle(document.getElementById('togglePassword'), passwordInput, document.getElementById('eyeIcon'));

    if (lengthSlider && lengthDisplay) {
        lengthSlider.addEventListener('input', event => {
            lengthDisplay.textContent = event.target.value;
        });
    }

    if (passwordInput) {
        passwordInput.addEventListener('input', event => {
            updatePasswordStrength(event.target.value, passLength, passTypes, strengthBar, strengthText);
            updateSaveButtonState(savePasswordBtn, passwordInput, generatedPassword);
        });
    }

    if (generateBtn) {
        generateBtn.addEventListener('click', () => {
            const length = parseInt(lengthSlider.value, 10) || 16;
            const name = fullNameField ? fullNameField.value.trim() : '';
            const useUppercase = genUppercase.checked;
            const useLowercase = genLowercase.checked;
            const useNumbers = genNumbers.checked;
            const useSymbols = genSymbols.checked;

            const password = generateMemorablePassword(name, length, useUppercase, useLowercase, useNumbers, useSymbols);
            if (!password) return;

            generatedPassword.textContent = password;
            copyBtn.style.display = 'inline-block';
            passwordInput.value = password;
            passwordInput.setAttribute('type', 'text');
            updatePasswordStrength(password, passLength, passTypes, strengthBar, strengthText);
            updateSaveButtonState(savePasswordBtn, passwordInput, generatedPassword);
            if (saveStatus) {
                saveStatus.textContent = 'Password ready to save.';
                saveStatus.className = 'small text-muted';
            }
        });
    }

    if (copyBtn) {
        copyBtn.addEventListener('click', async () => {
            const text = generatedPassword.textContent.trim();
            if (!text || text === 'Click "Generate" to create a password') return;
            try {
                await navigator.clipboard.writeText(text);
                copyBtn.textContent = 'Copied';
                showToast('Password copied to clipboard.', 'success');
                setTimeout(() => { copyBtn.innerHTML = '<i class="fa-solid fa-copy me-1"></i>Copy'; }, 1500);
            } catch (err) {
                copyBtn.textContent = 'Copy Failed';
                showToast('Could not copy the password.', 'error');
            }
        });
    }

    // Modal triggered Save behavior
    if (savePasswordBtn) {
        const saveModalElement = document.getElementById('saveVaultModal');
        const saveModal = saveModalElement ? new bootstrap.Modal(saveModalElement) : null;
        const confirmSaveBtn = document.getElementById('confirmSaveVaultBtn');
        const accountLabelInput = document.getElementById('accountLabelInput');

        savePasswordBtn.addEventListener('click', () => {
            const password = (passwordInput && passwordInput.value.trim()) || (generatedPassword && generatedPassword.textContent.trim());
            if (!password || password === 'Click "Generate" to create a password') {
                if (saveStatus) {
                    saveStatus.textContent = 'Enter or generate a password first.';
                    saveStatus.className = 'small text-danger';
                }
                return;
            }
            if (saveModal) {
                accountLabelInput.value = '';
                saveModal.show();
            }
        });

        if (confirmSaveBtn) {
            confirmSaveBtn.addEventListener('click', async () => {
                const password = (passwordInput && passwordInput.value.trim()) || (generatedPassword && generatedPassword.textContent.trim());
                const label = accountLabelInput.value.trim();

                if (!label) {
                    showToast('Enter what this password is for, such as LinkedIn.', 'error');
                    accountLabelInput.focus();
                    return;
                }

                const serverUser = window.SHIELDOS_USER || null;

                if (serverUser) {
                    setButtonLoading(confirmSaveBtn, true, 'Saving and emailing...');
                    savePasswordBtn.disabled = true;
                    try {
                        const response = await fetch('save_password.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: new URLSearchParams({ password, label }).toString()
                        });
                        const data = await response.json();
                        if (saveModal) saveModal.hide();
                        if (data && data.success) {
                            if (saveStatus) {
                                saveStatus.textContent = data.email_sent ? 'Saved and emailed.' : 'Saved, but email could not be sent.';
                                saveStatus.className = data.email_sent ? 'small text-success' : 'small text-warning';
                            }
                            showToast(data.email_sent ? 'Password saved and sent to your email.' : 'Password saved, but email delivery failed.', data.email_sent ? 'success' : 'warning');
                            setTimeout(() => { location.href = 'vault.php?saved=1'; }, 1200);
                        } else {
                            throw new Error(data.message || 'Could not save password.');
                        }
                    } catch (err) {
                        if (saveModal) saveModal.hide();
                        if (saveStatus) {
                            saveStatus.textContent = 'Server error while saving password.';
                            saveStatus.className = 'small text-danger';
                        }
                        showToast(err.message || 'Server error while saving password.', 'error');
                    } finally {
                        setButtonLoading(confirmSaveBtn, false);
                        updateSaveButtonState(savePasswordBtn, passwordInput, generatedPassword);
                    }
                } else if (saveStatus) {
                    saveStatus.textContent = 'Your session has expired. Please log in again.';
                    saveStatus.className = 'small text-danger';
                    showToast('Your session has expired. Please log in again.', 'error');
                }
            });
        }
    }

    if (auditBtn) {
        auditBtn.addEventListener('click', async () => {
            const generatedText = generatedPassword ? generatedPassword.textContent.trim() : '';
            const targetString = passwordInput.value.trim() || (generatedText !== 'Click "Generate" to create a password' ? generatedText : '');
            if (!targetString) {
                statusBox.innerHTML = `<h6 class="text-danger"><i class="fa-solid fa-circle-exclamation me-2"></i>Error: Empty Password</h6><p class="small mb-0">Please enter or generate a password to audit.</p>`;
                return;
            }

            statusBox.innerHTML = `<h6 class="text-info"><i class="fa-solid fa-spinner fa-spin me-2"></i>Analyzing...</h6><p class="small text-muted mb-0">Checking password strength and breaches...</p>`;
            setButtonLoading(auditBtn, true, 'Checking...');

            const breachCount = await checkPasswordBreach(targetString).catch(() => -1);
            const strength = getPasswordStrength(targetString);
            setButtonLoading(auditBtn, false);
            if (breachCount < 0) {
                statusBox.innerHTML = `
                    <h6 class="text-info fw-bold mb-2"><i class="fa-solid fa-circle-info me-2"></i>Audit Complete</h6>
                    <p class="small text-muted mb-2">Breach check unavailable in this environment.</p>
                    <p class="small mb-0"><strong>Strength:</strong> ${strength.label} (${strength.score}/6)</p>
                `;
                showToast('Breach service is unavailable right now.', 'warning');
                return;
            }

            if (breachCount > 0) {
                statusBox.innerHTML = `
                    <h6 class="text-danger fw-bold mb-2"><i class="fa-solid fa-triangle-exclamation me-2"></i>Warning: Breach Detected</h6>
                    <p class="small text-muted mb-2">Exposed in ${breachCount.toLocaleString()} data breaches.</p>
                    <p class="small mb-0"><strong>Strength:</strong> ${strength.label} (${strength.score}/6)</p>
                `;
                showToast('Warning: this password was found in breach data.', 'error');
            } else {
                statusBox.innerHTML = `
                    <h6 class="text-success fw-bold mb-2"><i class="fa-solid fa-circle-check me-2"></i>Secure</h6>
                    <p class="small text-muted mb-2">No breaches detected.</p>
                    <p class="small mb-0"><strong>Strength:</strong> ${strength.label} (${strength.score}/6)</p>
                `;
                showToast('No breach found for this password.', 'success');
            }
        });
    }

}

function initVaultPage() {
    showQueryToast();

    const deleteModalElement = document.getElementById('deleteVaultModal');
    const confirmDeleteBtn = document.getElementById('confirmDeleteVaultBtn');
    let pendingDeleteForm = null;

    document.querySelectorAll('.vault-copy-btn').forEach(button => {
        button.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(button.dataset.password || '');
                showToast('Password copied to clipboard.', 'success');
            } catch (err) {
                showToast('Could not copy the password.', 'error');
            }
        });
    });

    document.querySelectorAll('.vault-delete-form').forEach(form => {
        form.addEventListener('submit', event => {
            event.preventDefault();
            pendingDeleteForm = form;

            if (deleteModalElement && window.bootstrap) {
                const modal = bootstrap.Modal.getOrCreateInstance(deleteModalElement);
                modal.show();
            } else {
                form.submit();
            }
        });
    });

    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', () => {
            if (deleteModalElement && window.bootstrap) {
                const modal = bootstrap.Modal.getInstance(deleteModalElement);
                if (modal) modal.hide();
            }

            if (pendingDeleteForm) {
                setButtonLoading(pendingDeleteForm.querySelector('button[type="submit"]'), true, 'Deleting...');
                pendingDeleteForm.submit();
            }
        });
    }
}

function showToast(message, type = 'info') {
    const colors = {
        success: '#198754',
        error: '#dc3545',
        warning: '#f59e0b',
        info: '#0d6efd'
    };

    if (typeof Toastify === 'function') {
        Toastify({
            text: message,
            duration: 3500,
            gravity: 'top',
            position: 'right',
            close: true,
            style: { background: colors[type] || colors.info }
        }).showToast();
    }
}

function showQueryToast() {
    const params = new URLSearchParams(window.location.search);
    if (params.get('saved') === '1') showToast('Password saved to your vault.', 'success');
    if (params.get('deleted') === '1') showToast('Password removed from your vault.', 'success');
    if (params.get('email') === 'sent') showToast('Password sent to your email.', 'success');
}

function setButtonLoading(button, loading, label = 'Loading...') {
    if (!button) return;
    if (loading) {
        button.dataset.originalContent = button.innerHTML;
        button.disabled = true;
        button.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>${label}`;
    } else {
        button.disabled = false;
        if (button.dataset.originalContent) button.innerHTML = button.dataset.originalContent;
    }
}

function setPasswordToggle(button, passwordInput, eyeIcon) {
    if (!button || !passwordInput || !eyeIcon) return;
    button.addEventListener('click', () => {
        const isPassword = passwordInput.getAttribute('type') === 'password';
        passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
        eyeIcon.classList.toggle('fa-eye');
        eyeIcon.classList.toggle('fa-eye-slash');
    });
}

function showQueryMessage(element) {
    if (!element) return;
    const params = new URLSearchParams(window.location.search);
    if (params.get('registered') === '1') {
        element.className = 'alert alert-success';
        element.textContent = 'Account created successfully. Please log in.';
        element.classList.remove('d-none');
        showToast('Account created successfully. Please log in.', 'success');
    }
}

function getCurrentUser() {
    try {
        return JSON.parse(localStorage.getItem('shieldosCurrentUser')) || null;
    } catch (err) {
        return null;
    }
}

function getVaultEntriesForUser(email) {
    try {
        const raw = localStorage.getItem(`shieldosVault_${email}`);
        return raw ? JSON.parse(raw) : [];
    } catch (err) {
        return [];
    }
}

function saveVaultEntriesForUser(email, entries) {
    localStorage.setItem(`shieldosVault_${email}`, JSON.stringify(entries));
}

function addVaultEntry(email, entry) {
    const entries = getVaultEntriesForUser(email);
    entries.unshift(entry);
    saveVaultEntriesForUser(email, entries);
}

function deleteVaultEntry(email, index) {
    const entries = getVaultEntriesForUser(email);
    if (index < 0 || index >= entries.length) return;
    entries.splice(index, 1);
    saveVaultEntriesForUser(email, entries);
}

function renderVaultEntries(userEmail) {
    const vaultContent = document.getElementById('vaultContent');
    if (!vaultContent) return;

    const entries = getVaultEntriesForUser(userEmail);
    vaultContent.innerHTML = '';

    if (!entries.length) {
        vaultContent.innerHTML = `<div class="alert alert-info">No saved passwords found yet.</div>`;
        return;
    }

    const table = document.createElement('div');
    table.className = 'table-responsive';
    table.innerHTML = `
        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Account / Service</th>
                    <th>Password</th>
                    <th>Saved at</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    `;

    const tbody = table.querySelector('tbody');
    entries.forEach((entry, index) => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td><strong>#${index + 1}</strong></td>
            <td>${escapeHtml(entry.label)}</td>
            <td><code>${escapeHtml(entry.password)}</code></td>
            <td>${escapeHtml(entry.created_at)}</td>
            <td class="text-end">
                <button class="btn btn-sm btn-danger vault-delete-btn" data-index="${index}">Delete</button>
            </td>
        `;
        tbody.appendChild(row);
    });

    vaultContent.appendChild(table);
    vaultContent.querySelectorAll('.vault-delete-btn').forEach(button => {
        button.addEventListener('click', () => {
            deleteVaultEntry(userEmail, Number(button.dataset.index));
            renderVaultEntries(userEmail);
        });
    });
}

function escapeHtml(text) {
    return String(text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function updateSaveButtonState(button, passwordInput, generatedPassword) {
    if (!button) return;
    const current = (passwordInput && passwordInput.value.trim()) || (generatedPassword && generatedPassword.textContent.trim());
    button.disabled = !current || current === 'Click "Generate" to create a password';
}

function updatePasswordStrength(pwd, passLength, passTypes, strengthBar, strengthText) {
    if (!passLength || !passTypes || !strengthBar || !strengthText) return;

    passLength.textContent = pwd.length;
    let typeCount = 0;
    if (/[a-z]/.test(pwd)) typeCount++;
    if (/[A-Z]/.test(pwd)) typeCount++;
    if (/[0-9]/.test(pwd)) typeCount++;
    if (/[^A-Za-z0-9]/.test(pwd)) typeCount++;
    passTypes.textContent = typeCount;

    if (!pwd) {
        strengthBar.style.width = '0%';
        strengthText.textContent = 'Awaiting Input';
        strengthBar.className = 'progress-bar bg-danger';
        return;
    }

    const strength = getPasswordStrength(pwd);
    strengthBar.style.width = `${strength.percentage}%`;
    strengthBar.className = `progress-bar progress-bar-striped progress-bar-animated ${strength.color}`;
    strengthText.textContent = strength.label;
}

function getPasswordStrength(pwd) {
    const score = [
        pwd.length >= 8,
        pwd.length >= 12,
        pwd.length >= 16,
        /[a-z]/.test(pwd) && /[A-Z]/.test(pwd),
        /[0-9]/.test(pwd),
        /[^A-Za-z0-9]/.test(pwd)
    ].reduce((sum, value) => sum + (value ? 1 : 0), 0);

    if (score <= 1) return { score, percentage: 15, label: 'Very Weak', color: 'bg-danger' };
    if (score <= 2) return { score, percentage: 30, label: 'Weak', color: 'bg-danger' };
    if (score <= 3) return { score, percentage: 50, label: 'Fair', color: 'bg-warning' };
    if (score <= 4) return { score, percentage: 75, label: 'Good', color: 'bg-info' };
    if (score <= 5) return { score, percentage: 90, label: 'Strong', color: 'bg-success' };
    return { score, percentage: 100, label: 'Very Strong', color: 'bg-success' };
}

function generateMemorablePassword(name, length, useUppercase, useLowercase, useNumbers, useSymbols) {
    if (!name || name.trim() === '') {
        alert('Please enter your name to generate a password');
        return null;
    }

    let charset = '';
    if (useUppercase) charset += 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    if (useLowercase) charset += 'abcdefghijklmnopqrstuvwxyz';
    if (useNumbers) charset += '0123456789';
    if (useSymbols) charset += '!@#$%^&*-_=+';
    if (!charset) {
        alert('Select at least one character set');
        return null;
    }

    const nameParts = name.trim().split(/\s+/);
    let base = '';
    nameParts.forEach((part, index) => {
        if (part.length > 0) {
            let letter = (index === 0 || index === nameParts.length - 1) ? part.substring(0, 2) : part.charAt(0);
            if (useUppercase && !useLowercase) letter = letter.toUpperCase();
            else if (useLowercase && !useUppercase) letter = letter.toLowerCase();
            base += letter;
        }
    });

    let password = base;
    while (password.length < length) {
        const randomIndex = Math.floor(Math.random() * charset.length);
        password += charset[randomIndex];
    }
    return password.substring(0, length);
}

async function checkPasswordBreach(password) {
    try {
        const response = await fetch('process.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ target: password }).toString()
        });
        if (!response.ok) throw new Error('API unavailable');
        const data = await response.json();
        if (data.status !== 'success') throw new Error(data.message || 'API unavailable');
        return Number(data.breach_count) || 0;
    } catch (err) {
        return -1;
    }
}