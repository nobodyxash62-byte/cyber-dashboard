document.addEventListener('DOMContentLoaded', () => {
    // Initialize behavior based on presence of page-specific elements.
    if (document.getElementById('loginForm')) {
        initLoginPage();
        return;
    }

    if (document.getElementById('signupForm')) {
        initSignupPage();
        return;
    }

    // Dashboard page checks for a known dashboard element
    if (document.getElementById('savePasswordBtn') || document.getElementById('vaultContent')) {
        initDashboardPage();
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
    // For server-backed login form, keep only UI enhancements (no client-side auth interception).
    const toggleButton = document.getElementById('loginTogglePassword');
    const passwordInput = document.getElementById('loginPassword');
    const eyeIcon = document.getElementById('loginEyeIcon');
    const messageEl = document.getElementById('loginMessage');

    showQueryMessage(messageEl);
    setPasswordToggle(toggleButton, passwordInput, eyeIcon);
}

function initSignupPage() {
    // For server-backed signup form, only provide UI helpers (no client-side registration).
    const toggleButton = document.getElementById('signupTogglePassword');
    const passwordInput = document.getElementById('signupPassword');
    const eyeIcon = document.getElementById('signupEyeIcon');
    const messageEl = document.getElementById('signupMessage');

    setPasswordToggle(toggleButton, passwordInput, eyeIcon);
}

function initDashboardPage() {
    // If a client-side session exists use it, otherwise the server-side page renders user info.
    const user = getCurrentUser();
    const greeting = document.getElementById('navGreeting');
    const logoutBtn = document.getElementById('logoutBtn');
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

    if (user && greeting) {
        const firstName = user.full_name.split(' ')[0] || user.full_name;
        greeting.textContent = `Hi, ${firstName}`;
    }

    if (logoutBtn) {
        // If server logout is available, navigate to it; otherwise fallback to client logout.
        logoutBtn.addEventListener('click', () => {
            if (typeof fetch === 'function') {
                // Navigate to server logout endpoint.
                location.href = 'logout.php';
            } else {
                logoutUser();
            }
        });
    }

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
            const name = fullNameField.value.trim();
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
                saveStatus.textContent = 'Password ready to save locally to the vault.';
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
                setTimeout(() => { copyBtn.innerHTML = '<i class="fa-solid fa-copy me-1"></i>Copy'; }, 1500);
            } catch (err) {
                copyBtn.textContent = 'Copy Failed';
            }
        });
    }

    if (savePasswordBtn) {
        if (user) {
            savePasswordBtn.addEventListener('click', () => {
                const password = passwordInput.value.trim() || generatedPassword.textContent.trim();
                if (!password || password === 'Click "Generate" to create a password') {
                    saveStatus.textContent = 'Enter or generate a password first.';
                    saveStatus.className = 'small text-danger';
                    return;
                }

                const entry = {
                    label: 'Saved password',
                    password,
                    created_at: new Date().toLocaleString()
                };

                addVaultEntry(user.email, entry);
                saveStatus.textContent = 'Password saved to your vault.';
                saveStatus.className = 'small text-success';
                renderVaultEntries(user.email);
            });
        } else {
            // If no client-side user, redirect to server login when save is clicked.
            savePasswordBtn.addEventListener('click', () => {
                location.href = 'login.php';
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

            statusBox.innerHTML = `<h6 class="text-info"><i class="fa-solid fa-spinner fa-spin me-2"></i>Analyzing...</h6><p class="small text-muted mb-0">Checking password strength...</p>`;

            const breachCount = await checkPasswordBreach(targetString).catch(() => -1);
            const strength = getPasswordStrength(targetString);
            if (breachCount < 0) {
                statusBox.innerHTML = `
                    <h6 class="text-secondary"><i class="fa-solid fa-circle-info me-2"></i>Audit Complete</h6>
                    <p class="small mb-2">Breach check unavailable in this environment. Use the strength meter for guidance.</p>
                    <p class="small mb-0"><strong>Strength:</strong> ${strength.label} (${strength.score}/6)</p>
                `;
                return;
            }

            if (breachCount > 0) {
                statusBox.innerHTML = `
                    <h6 class="text-danger fw-bold"><i class="fa-solid fa-triangle-exclamation me-2"></i>WARNING: Breach Detected</h6>
                    <p class="small mb-2">This password has been exposed in ${breachCount.toLocaleString()} data breaches.</p>
                    <p class="small mb-0"><strong>Strength:</strong> ${strength.label} (${strength.score}/6)</p>
                `;
            } else {
                statusBox.innerHTML = `
                    <h6 class="text-success fw-bold"><i class="fa-solid fa-circle-check me-2"></i>SECURE</h6>
                    <p class="small mb-2">No breaches detected. This password hasn't appeared in known data breaches.</p>
                    <p class="small mb-0"><strong>Strength:</strong> ${strength.label} (${strength.score}/6)</p>
                `;
            }
        });
    }

    if (user) {
        renderVaultEntries(user.email);
    }
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) target.scrollIntoView({ behavior: 'smooth' });
        });
    });
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

function showFormMessage(element, text, type) {
    if (!element) return;
    element.className = `alert alert-${type}`;
    element.textContent = text;
}

function showQueryMessage(element) {
    if (!element) return;
    const params = new URLSearchParams(window.location.search);
    if (params.get('registered') === '1') {
        element.className = 'alert alert-success';
        element.textContent = 'Account created successfully. Please log in.';
        element.classList.remove('d-none');
    }
}

function validateEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function getStoredUsers() {
    try {
        const raw = localStorage.getItem('shieldosUsers');
        return raw ? JSON.parse(raw) : [];
    } catch (err) {
        return [];
    }
}

function saveStoredUsers(users) {
    localStorage.setItem('shieldosUsers', JSON.stringify(users));
}

function findUserByEmail(email) {
    const users = getStoredUsers();
    return users.find(user => user.email === email);
}

function getCurrentUser() {
    try {
        return JSON.parse(localStorage.getItem('shieldosCurrentUser')) || null;
    } catch (err) {
        return null;
    }
}

function setCurrentUser(user) {
    localStorage.setItem('shieldosCurrentUser', JSON.stringify(user));
}

function logoutUser() {
    localStorage.removeItem('shieldosCurrentUser');
    location.href = 'login.php';
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
        vaultContent.innerHTML = `<div class="alert alert-info" id="vaultEmptyMessage">No saved passwords found yet. Generate a password and save it using the button above.</div>`;
        return;
    }

    const table = document.createElement('div');
    table.className = 'table-responsive';
    table.innerHTML = `
        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>Label</th>
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

    if (score <= 1) {
        return { score, percentage: 15, label: 'Very Weak', color: 'bg-danger' };
    }
    if (score <= 2) {
        return { score, percentage: 30, label: 'Weak', color: 'bg-danger' };
    }
    if (score <= 3) {
        return { score, percentage: 50, label: 'Fair', color: 'bg-warning' };
    }
    if (score <= 4) {
        return { score, percentage: 75, label: 'Good', color: 'bg-info' };
    }
    if (score <= 5) {
        return { score, percentage: 90, label: 'Strong', color: 'bg-success' };
    }
    return { score, percentage: 100, label: 'Very Strong', color: 'bg-success' };
}

function generateMemorablePassword(name, length, useUppercase, useLowercase, useNumbers, useSymbols) {
    if (!name || name.trim() === '') {
        alert('Please enter your full name to generate a memorable password');
        return null;
    }

    let charset = '';
    if (useUppercase) charset += 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    if (useLowercase) charset += 'abcdefghijklmnopqrstuvwxyz';
    if (useNumbers) charset += '0123456789';
    if (useSymbols) charset += '!@#$%^&*-_=+';
    if (!charset) {
        alert('Please select at least one character type');
        return null;
    }

    const nameParts = name.trim().split(/\s+/);
    let base = '';
    nameParts.forEach((part, index) => {
        if (part.length > 0) {
            let letter = (index === 0 || index === nameParts.length - 1) ? part.substring(0, 2) : part.charAt(0);
            if (useUppercase && !useLowercase) {
                letter = letter.toUpperCase();
            } else if (useLowercase && !useUppercase) {
                letter = letter.toLowerCase();
            }
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
        const msgBuffer = new TextEncoder().encode(password);
        const digest = await crypto.subtle.digest('SHA-1', msgBuffer);
        const sha1Hash = Array.from(new Uint8Array(digest)).map(b => b.toString(16).padStart(2, '0')).join('').toUpperCase();
        const prefix = sha1Hash.slice(0, 5);
        const suffix = sha1Hash.slice(5);
        const url = `https://api.pwnedpasswords.com/range/${prefix}`;
        const response = await fetch(url, {
            method: 'GET',
            headers: { 'Add-Padding': 'true' }
        });
        if (!response.ok) {
            throw new Error('Breach API unavailable');
        }
        const body = await response.text();
        return body.split('\n').reduce((count, line) => {
            const [hashSuffix, occurrences] = line.trim().split(':');
            return hashSuffix === suffix ? parseInt(occurrences, 10) : count;
        }, 0);
    } catch (err) {
        return -1;
    }
}
EOF