document.addEventListener('DOMContentLoaded', async () => {
    const manifestVersion = chrome.runtime.getManifest().version;
    const SNOOZE_MS = 6 * 60 * 60 * 1000; // 6 Hours in Milliseconds

    const ui = {
        loading: document.getElementById('loading-screen'),
        login: document.getElementById('login-screen'),
        update: document.getElementById('update-screen'),
        dashboard: document.getElementById('dashboard-screen'),
        actionError: document.getElementById('action-error'),
        displayName: document.getElementById('display-name'),
        displayPlan: document.getElementById('display-plan'),
        platformsContainer: document.getElementById('platforms-container'),
        versionChip: document.getElementById('version-chip'),
        updateMsg: document.getElementById('update-msg'),
        updateDownloadBtn: document.getElementById('update-download-btn'),
        softUpdateDialog: document.getElementById('soft-update-dialog'),
        softUpdateText: document.getElementById('soft-update-text'),
        softUpdateBtn: document.getElementById('soft-update-btn'),
        softSnoozeBtn: document.getElementById('soft-snooze-btn'),
    };

    if (ui.versionChip) {
        ui.versionChip.textContent = `v${manifestVersion}`;
    }

    const API_URL = 'https://panel.shahabtech.com/api/extension';

    function isOutdated(installed, required) {
        if (!required) return false;
        const p1 = installed.split('.').map(Number);
        const p2 = required.split('.').map(Number);
        for (let i = 0; i < Math.max(p1.length, p2.length); i++) {
            const n1 = p1[i] || 0;
            const n2 = p2[i] || 0;
            if (n1 < n2) return true;
            if (n1 > n2) return false;
        }
        return false;
    }

    function showScreen(screen) {
        ui.loading.style.display = 'none';
        ui.login.style.display = 'none';
        ui.update.style.display = 'none';
        ui.dashboard.style.display = 'none';
        if (ui[screen]) ui[screen].style.display = 'block';
    }

    function showError(msg) {
        ui.actionError.textContent = msg;
        ui.actionError.style.display = 'block';
        setTimeout(() => ui.actionError.style.display = 'none', 4000);
    }

    async function evaluateVersionUpdate(data) {
        if (!data || !data.required_version) return false;

        const isVerOutdated = isOutdated(manifestVersion, data.required_version);
        if (!isVerOutdated) return false;

        const isStrictForce = !!data.force_update;
        const downloadUrl = data.download_url || 'https://panel.shahabtech.com/user/dashboard';

        if (isStrictForce) {
            // STRICT MODE: Block usage completely
            triggerStrictUpdateScreen(data.required_version, downloadUrl);
            return true;
        } else {
            // SOFT MODE: Check 6-hour snooze window
            const res = await new Promise(resolve => chrome.storage.local.get(['wemate_last_snooze_time'], resolve));
            const lastSnooze = res ? (res.wemate_last_snooze_time || 0) : 0;
            const now = Date.now();

            if (now - lastSnooze > SNOOZE_MS) {
                showSoftUpdateDialog(data.required_version, downloadUrl);
            }
            return false; // Allow dashboard to load
        }
    }

    function triggerStrictUpdateScreen(requiredVer, downloadUrl) {
        ui.updateMsg.textContent = `Your extension (v${manifestVersion}) is outdated. Admin has enabled Strict Force Update. Version ${requiredVer} or higher is required to continue accessing platforms.`;
        if (downloadUrl && ui.updateDownloadBtn) {
            ui.updateDownloadBtn.href = downloadUrl;
        }
        showScreen('update');
    }

    function showSoftUpdateDialog(requiredVer, downloadUrl) {
        if (!ui.softUpdateDialog) return;
        ui.softUpdateText.textContent = `Extension v${requiredVer} is available. You are currently on v${manifestVersion}. Would you like to update now?`;
        if (downloadUrl && ui.softUpdateBtn) {
            ui.softUpdateBtn.href = downloadUrl;
        }
        ui.softUpdateDialog.style.display = 'block';

        if (ui.softSnoozeBtn) {
            ui.softSnoozeBtn.onclick = () => {
                chrome.storage.local.set({ wemate_last_snooze_time: Date.now() });
                ui.softUpdateDialog.style.display = 'none';
            };
        }
    }

    // Check minimum required version
    async function checkVersion() {
        try {
            const res = await fetch(`${API_URL}/version`, {
                method: 'GET',
                headers: { 'Accept': 'application/json' }
            });
            if (res.ok) {
                const data = await res.json();
                const isBlocked = await evaluateVersionUpdate(data);
                if (isBlocked) return false;
            }
        } catch (e) {}
        return true;
    }

    // Auto-check authentication via browser session cookie
    async function checkAuth() {
        // First check version compatibility
        const isVerValid = await checkVersion();
        if (!isVerValid) return;

        try {
            const res = await fetch(`${API_URL}/me`, {
                method: 'GET',
                credentials: 'include',
                headers: { 
                    'Accept': 'application/json',
                    'X-Ext-Version': manifestVersion 
                }
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
            
            // Check version from response
            const isBlocked = await evaluateVersionUpdate(data);
            if (isBlocked) return;

            if (data.success && data.user) {
                ui.displayName.textContent = data.user.name;
                ui.displayPlan.textContent = data.user.plan ? `Plan: ${data.user.plan.name}` : 'Plan: Direct Access';
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
                headers: { 
                    'Accept': 'application/json',
                    'X-Ext-Version': manifestVersion 
                }
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
                headers: { 
                    'Accept': 'application/json',
                    'X-Ext-Version': manifestVersion 
                }
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
                    btnElement.style.background = '#00e676'; // Emerald Green
                    btnElement.style.borderColor = '#00e676';
                    btnElement.style.color = '#000000';
                } else {
                    throw new Error(response ? response.error : 'Injection failed');
                }
                
                setTimeout(() => {
                    btnElement.textContent = originalText;
                    btnElement.disabled = false;
                    btnElement.style.background = '';
                    btnElement.style.borderColor = '';
                    btnElement.style.color = '';
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
