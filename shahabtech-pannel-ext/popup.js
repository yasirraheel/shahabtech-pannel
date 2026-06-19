document.addEventListener('DOMContentLoaded', async () => {
    const ui = {
        loading: document.getElementById('loading-screen'),
        login: document.getElementById('login-screen'),
        dashboard: document.getElementById('dashboard-screen'),
        loginForm: document.getElementById('login-form'),
        loginBtn: document.getElementById('login-btn'),
        loginError: document.getElementById('login-error'),
        actionError: document.getElementById('action-error'),
        apiUrl: document.getElementById('api-url'),
        username: document.getElementById('username'),
        password: document.getElementById('password'),
        displayName: document.getElementById('display-name'),
        displayPlan: document.getElementById('display-plan'),
        platformsContainer: document.getElementById('platforms-container'),
        logoutBtn: document.getElementById('logout-btn')
    };

    function showScreen(screen) {
        ui.loading.style.display = 'none';
        ui.login.style.display = 'none';
        ui.dashboard.style.display = 'none';
        if (screen === 'loading') ui.loading.style.display = 'flex';
        if (screen === 'login') ui.login.style.display = 'block';
        if (screen === 'dashboard') ui.dashboard.style.display = 'block';
    }

    function showError(element, message) {
        element.textContent = message;
        element.style.display = 'block';
        setTimeout(() => { element.style.display = 'none'; }, 5000);
    }

    // Check auth status
    const data = await chrome.storage.local.get(['token', 'user', 'apiUrl']);
    if (data.apiUrl) {
        ui.apiUrl.value = data.apiUrl;
    }

    if (data.token && data.user) {
        await loadDashboard(data.token, data.apiUrl || ui.apiUrl.value, data.user);
    } else {
        showScreen('login');
    }

    // Login Form Submit
    ui.loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        ui.loginBtn.disabled = true;
        ui.loginBtn.innerHTML = '<div class="spinner"></div> Logging in...';
        ui.loginError.style.display = 'none';

        const apiUrl = ui.apiUrl.value.replace(/\/$/, '');
        const username = ui.username.value;
        const password = ui.password.value;

        try {
            const res = await fetch(`${apiUrl}/api/extension/login`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ username, password })
            });

            const result = await res.json();
            if (result.success && result.token) {
                await chrome.storage.local.set({
                    token: result.token,
                    user: result.user,
                    apiUrl: apiUrl
                });
                await loadDashboard(result.token, apiUrl, result.user);
            } else {
                showError(ui.loginError, result.message || 'Login failed.');
            }
        } catch (error) {
            showError(ui.loginError, 'Network error. Please check Server URL.');
        } finally {
            ui.loginBtn.disabled = false;
            ui.loginBtn.textContent = 'Log In';
        }
    });

    // Logout
    ui.logoutBtn.addEventListener('click', async () => {
        const { token, apiUrl } = await chrome.storage.local.get(['token', 'apiUrl']);
        if (token && apiUrl) {
            fetch(`${apiUrl}/api/extension/logout`, {
                method: 'POST',
                headers: { 'Authorization': `Bearer ${token}` }
            }).catch(() => {});
        }
        await chrome.storage.local.remove(['token', 'user']);
        showScreen('login');
    });

    // Load Dashboard
    async function loadDashboard(token, apiUrl, user) {
        showScreen('loading');
        ui.displayName.textContent = user.name;
        ui.displayPlan.textContent = `Plan: ${user.plan ? user.plan.name : 'None'}`;

        try {
            const res = await fetch(`${apiUrl}/api/extension/platforms`, {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });

            if (res.status === 401) {
                // Token expired
                await chrome.storage.local.remove(['token', 'user']);
                showScreen('login');
                return;
            }

            const result = await res.json();
            if (result.success) {
                renderPlatforms(result.platforms, token, apiUrl);
                showScreen('dashboard');
            } else {
                showError(ui.actionError, result.message);
                showScreen('dashboard');
            }
        } catch (error) {
            await chrome.storage.local.remove(['token', 'user']);
            showScreen('login');
        }
    }

    // Render Platforms
    function renderPlatforms(platforms, token, apiUrl) {
        ui.platformsContainer.innerHTML = '';
        if (!platforms || platforms.length === 0) {
            ui.platformsContainer.innerHTML = '<div style="color:#8a8a99; font-size:12px; text-align:center; padding: 20px 0;">No platforms available on your plan.</div>';
            return;
        }

        platforms.forEach(platform => {
            const card = document.createElement('div');
            card.className = 'platform-card';
            card.innerHTML = `
                <div class="platform-info">
                    <span class="platform-name">${platform.name}</span>
                    <span class="platform-domain">${platform.domain}</span>
                </div>
                <div class="platform-action" style="font-size: 16px; color: #a78bfa;">➔</div>
            `;

            card.addEventListener('click', async () => {
                card.style.opacity = '0.5';
                try {
                    // Send message to background to fetch and inject cookies
                    chrome.runtime.sendMessage({
                        type: 'INJECT_COOKIES',
                        platformId: platform.id,
                        apiUrl: apiUrl,
                        token: token
                    }, (response) => {
                        card.style.opacity = '1';
                        if (response && response.success) {
                            window.close(); // Close popup
                        } else {
                            showError(ui.actionError, (response && response.message) || 'Failed to inject cookies.');
                        }
                    });
                } catch (e) {
                    card.style.opacity = '1';
                    showError(ui.actionError, 'Extension communication error.');
                }
            });

            ui.platformsContainer.appendChild(card);
        });
    }
});
