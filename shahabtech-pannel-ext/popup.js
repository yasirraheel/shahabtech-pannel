document.addEventListener('DOMContentLoaded', async () => {
    const ui = {
        loading: document.getElementById('loading-screen'),
        login: document.getElementById('login-screen'),
        dashboard: document.getElementById('dashboard-screen'),
        actionError: document.getElementById('action-error'),
        displayName: document.getElementById('display-name'),
        displayPlan: document.getElementById('display-plan'),
        platformsContainer: document.getElementById('platforms-container'),
    };

    const API_URL = 'https://panel.shahabtech.com/api/extension';

    function showScreen(screen) {
        ui.loading.style.display = 'none';
        ui.login.style.display = 'none';
        ui.dashboard.style.display = 'none';
        if (ui[screen]) ui[screen].style.display = 'block';
    }

    function showError(msg) {
        ui.actionError.textContent = msg;
        ui.actionError.style.display = 'block';
        setTimeout(() => ui.actionError.style.display = 'none', 4000);
    }

    // Auto-check authentication via browser session cookie
    async function checkAuth() {
        try {
            const res = await fetch(`${API_URL}/me`, {
                method: 'GET',
                credentials: 'include',
                headers: { 'Accept': 'application/json' }
            });
            
            if (res.status === 401 || res.status === 403 || !res.ok) {
                chrome.runtime.sendMessage({ type: 'WIPE_COOKIES' });
                showScreen('login');
                return;
            }

            const contentType = res.headers.get("content-type");
            if (!contentType || contentType.indexOf("application/json") === -1) {
                chrome.runtime.sendMessage({ type: 'WIPE_COOKIES' });
                showScreen('login');
                return;
            }

            const data = await res.json();
            if (data.success && data.user) {
                ui.displayName.textContent = data.user.name;
                ui.displayPlan.textContent = data.user.plan ? `Plan: ${data.user.plan.name}` : 'Plan: None';
                loadPlatforms();
                showScreen('dashboard');
            } else {
                chrome.runtime.sendMessage({ type: 'WIPE_COOKIES' });
                showScreen('login');
            }
        } catch (err) {
            console.error(err);
            chrome.runtime.sendMessage({ type: 'WIPE_COOKIES' });
            showScreen('login');
        }
    }

    async function loadPlatforms() {
        try {
            const res = await fetch(`${API_URL}/platforms`, {
                method: 'GET',
                credentials: 'include',
                headers: { 'Accept': 'application/json' }
            });

            if (!res.ok) throw new Error('Failed to load platforms');
            
            const data = await res.json();
            if (data.success && data.platforms) {
                renderPlatforms(data.platforms);
            }
        } catch (err) {
            showError('Could not load platforms.');
        }
    }

    function renderPlatforms(platforms) {
        ui.platformsContainer.innerHTML = '';
        if (platforms.length === 0) {
            ui.platformsContainer.innerHTML = '<div style="text-align:center; padding: 20px; color:#8a8a99; font-size: 12px;">No platforms available on your plan.</div>';
            return;
        }

        platforms.forEach(p => {
            const card = document.createElement('div');
            card.className = 'platform-card';
            card.innerHTML = `
                <div class="platform-info">
                    <span class="platform-name">${p.name}</span>
                    <span class="platform-domain">${p.domain}</span>
                </div>
                <button class="btn btn-primary" style="width:auto; padding: 6px 12px; font-size:11px;">Access</button>
            `;
            
            const btn = card.querySelector('button');
            btn.addEventListener('click', () => injectCookies(p.id, btn));
            
            ui.platformsContainer.appendChild(card);
        });
    }

    async function injectCookies(platformId, btnElement) {
        const originalText = btnElement.textContent;
        btnElement.innerHTML = '<div class="spinner" style="width:10px;height:10px;border-width:2px;"></div>';
        btnElement.disabled = true;

        try {
            const res = await fetch(`${API_URL}/cookies/${platformId}`, {
                method: 'GET',
                credentials: 'include',
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();

            if (!data.success) {
                throw new Error(data.message || 'Failed to fetch access data');
            }

            // Send to background script
            chrome.runtime.sendMessage({
                type: 'INJECT_COOKIES',
                platform: data.platform,
                cookies: data.cookies
            }, (response) => {
                if (response && response.success) {
                    btnElement.textContent = 'Opened!';
                    btnElement.style.background = '#10b981'; // Green
                    btnElement.style.borderColor = '#10b981';
                } else {
                    throw new Error(response ? response.error : 'Injection failed');
                }
                
                setTimeout(() => {
                    btnElement.textContent = originalText;
                    btnElement.disabled = false;
                    btnElement.style.background = '';
                    btnElement.style.borderColor = '';
                }, 3000);
            });
        } catch (err) {
            btnElement.textContent = originalText;
            btnElement.disabled = false;
            showError(err.message);
        }
    }

    // Start
    checkAuth();
});
