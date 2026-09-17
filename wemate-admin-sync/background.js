// WeMate Admin Cookie Sync — Background Service Worker
// Automatically captures Google Flow rolling session tokens and syncs to ShahabTech Panel.

const DEFAULT_PANEL_URL = 'https://panel.shahabtech.com';
const SYNC_ALARM_NAME = 'periodicAdminSync';
const SYNC_INTERVAL_MINUTES = 30;
const DEBOUNCE_MS = 15000; // 15 seconds debounce for cookie change triggers

let debounceTimer = null;
let isSyncing = false;

// ─── INITIALIZATION ──────────────────────────────────────────────────────────
chrome.runtime.onInstalled.addListener(() => {
    console.log('[WeMate Admin Sync] Installed. Setting up 30-minute periodic heartbeat...');
    chrome.alarms.create(SYNC_ALARM_NAME, { periodInMinutes: SYNC_INTERVAL_MINUTES });
    updateBadge('INIT', '#6c757d');
});

chrome.alarms.onAlarm.addListener((alarm) => {
    if (alarm.name === SYNC_ALARM_NAME) {
        console.log('[WeMate Admin Sync] 30-minute interval heartbeat fired. Initiating sync...');
        performSync('30m_heartbeat');
    }
});

// ─── COOKIE WATCHER (GOOGLE FLOW ROLLING TOKENS) ─────────────────────────────
const WATCHED_COOKIE_NAMES = new Set([
    '__Secure-1PSIDTS',
    '__Secure-3PSIDTS',
    '__Secure-1PSID',
    '__Secure-3PSID',
    'OSID',
    '__Secure-OSID',
    'SID',
    'HSID',
    'SSID',
    'SIDCC',
    '__Secure-1PSIDCC',
    '__Secure-3PSIDCC'
]);

chrome.cookies.onChanged.addListener((changeInfo) => {
    const cookie = changeInfo.cookie;
    if (!cookie) return;

    const domain = (cookie.domain || '').toLowerCase();
    const isGoogle = domain.includes('google.com') || domain.includes('flow.google.com');
    if (!isGoogle) return;

    if (WATCHED_COOKIE_NAMES.has(cookie.name)) {
        console.log(`[WeMate Admin Sync] Detected token change: ${cookie.name} on ${domain} (removed: ${changeInfo.removed})`);
        if (!changeInfo.removed) {
            scheduleSync(DEBOUNCE_MS, `cookie_change:${cookie.name}`);
        }
    }
});

function scheduleSync(delayMs, reason) {
    if (debounceTimer) {
        clearTimeout(debounceTimer);
    }
    console.log(`[WeMate Admin Sync] Scheduling sync in ${delayMs / 1000}s (Reason: ${reason})`);
    debounceTimer = setTimeout(() => {
        debounceTimer = null;
        performSync(reason);
    }, delayMs);
}

// ─── COOKIE EXTRACTION ENGINE ────────────────────────────────────────────────
const TARGET_DOMAINS = [
    'flow.google.com',
    '.flow.google.com',
    '.google.com',
    'google.com',
    'accounts.google.com',
    '.accounts.google.com'
];

async function collectGoogleFlowCookies() {
    const cookieMap = new Map();

    for (const domain of TARGET_DOMAINS) {
        try {
            const cookies = await chrome.cookies.getAll({ domain: domain });
            for (const c of (cookies || [])) {
                // Keyed by name, domain, path to ensure uniqueness
                const key = `${c.name}|${c.domain}|${c.path}`;
                if (!cookieMap.has(key)) {
                    cookieMap.set(key, {
                        name: c.name,
                        value: c.value,
                        domain: c.domain,
                        path: c.path,
                        secure: c.secure,
                        httpOnly: c.httpOnly,
                        sameSite: c.sameSite,
                        expirationDate: c.expirationDate,
                        hostOnly: c.hostOnly,
                        session: c.session
                    });
                }
            }
        } catch (err) {
            console.warn(`[WeMate Admin Sync] Error reading cookies for ${domain}:`, err);
        }
    }

    return Array.from(cookieMap.values());
}

// ─── MASTER SYNC ACTION ──────────────────────────────────────────────────────
async function performSync(triggerSource = 'manual') {
    if (isSyncing) {
        console.log('[WeMate Admin Sync] Sync already in progress. Skipping duplicate request.');
        return { success: false, message: 'Sync already in progress' };
    }

    isSyncing = true;
    updateBadge('SYNC', '#0d6efd');

    try {
        const config = await chrome.storage.local.get(['panelUrl', 'adminKey', 'accountId', 'autoSyncEnabled']);
        const panelUrl = (config.panelUrl || DEFAULT_PANEL_URL).replace(/\/+$/, '');
        const adminKey = (config.adminKey || '').trim();
        const accountId = config.accountId;
        const autoSyncEnabled = config.autoSyncEnabled !== false; // default true

        if (triggerSource !== 'manual' && !autoSyncEnabled) {
            console.log('[WeMate Admin Sync] Auto-sync is currently paused by admin.');
            updateBadge('PAUSE', '#ffc107');
            isSyncing = false;
            return { success: false, message: 'Auto-sync is disabled' };
        }

        if (!adminKey) {
            const err = 'Admin Key is not set. Open extension popup to configure.';
            console.warn('[WeMate Admin Sync]', err);
            await logSyncResult({ success: false, error: err, trigger: triggerSource });
            updateBadge('KEY?', '#dc3545');
            isSyncing = false;
            return { success: false, message: err };
        }

        if (!accountId) {
            const err = 'No Account selected. Open extension popup to select Google Flow account.';
            console.warn('[WeMate Admin Sync]', err);
            await logSyncResult({ success: false, error: err, trigger: triggerSource });
            updateBadge('ACC?', '#dc3545');
            isSyncing = false;
            return { success: false, message: err };
        }

        // 1. Gather all current cookies
        const cookies = await collectGoogleFlowCookies();
        if (!cookies.length) {
            const err = 'No Google cookies found in browser. Make sure you are logged into Google Flow in this browser profile.';
            await logSyncResult({ success: false, error: err, trigger: triggerSource });
            updateBadge('EMPTY', '#dc3545');
            isSyncing = false;
            return { success: false, message: err };
        }

        console.log(`[WeMate Admin Sync] Extracted ${cookies.length} cookies. Pushing to ${panelUrl}...`);

        // 2. Transmit to Panel API
        const endpoint = `${panelUrl}/api/extension/admin-sync`;
        const res = await fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Admin-Key': adminKey
            },
            body: JSON.stringify({
                account_id: parseInt(accountId, 10),
                cookies: cookies,
                source: 'admin_edge_extension',
                trigger: triggerSource
            })
        });

        const data = await res.json();

        if (!res.ok || !data.success) {
            const errMsg = data.message || `Server returned HTTP ${res.status}`;
            await logSyncResult({ success: false, error: errMsg, trigger: triggerSource });
            updateBadge('ERR', '#dc3545');
            isSyncing = false;
            return { success: false, message: errMsg };
        }

        // 3. Success Record
        await logSyncResult({
            success: true,
            cookieCount: data.cookie_count || cookies.length,
            accountId: data.account_id,
            accountTitle: data.title,
            syncedAt: data.synced_at,
            trigger: triggerSource
        });

        updateBadge('LIVE', '#198754');
        console.log(`[WeMate Admin Sync] Sync SUCCESSFUL! Updated Account #${data.account_id} with ${data.cookie_count} cookies.`);
        isSyncing = false;
        return { success: true, data: data };

    } catch (err) {
        console.error('[WeMate Admin Sync] Unexpected network or execution error:', err);
        await logSyncResult({ success: false, error: err.message, trigger: triggerSource });
        updateBadge('ERR', '#dc3545');
        isSyncing = false;
        return { success: false, message: err.message };
    }
}

// ─── LOGGING & STATUS PERSISTENCE ────────────────────────────────────────────
async function logSyncResult(result) {
    const entry = {
        ...result,
        timestamp: Date.now(),
        isoTime: new Date().toISOString()
    };

    const existing = await chrome.storage.local.get(['syncHistory', 'lastSyncStatus']);
    let history = existing.syncHistory || [];
    history.unshift(entry);
    if (history.length > 20) history = history.slice(0, 20); // keep last 20 logs

    await chrome.storage.local.set({
        lastSyncStatus: entry,
        syncHistory: history
    });
}

function updateBadge(text, color) {
    try {
        if (chrome.action && chrome.action.setBadgeText) {
            chrome.action.setBadgeText({ text: text });
            chrome.action.setBadgeBackgroundColor({ color: color });
        }
    } catch (_) {}
}

// ─── POPUP MESSAGE HANDLER ───────────────────────────────────────────────────
chrome.runtime.onMessage.addListener((request, sender, sendResponse) => {
    if (request.type === 'MANUAL_SYNC') {
        performSync('manual_popup_click')
            .then(res => sendResponse(res))
            .catch(err => sendResponse({ success: false, message: err.message }));
        return true; // async keep-alive
    }

    if (request.type === 'FETCH_ACCOUNTS') {
        fetchPanelAccounts()
            .then(res => sendResponse(res))
            .catch(err => sendResponse({ success: false, message: err.message }));
        return true;
    }
});

async function fetchPanelAccounts() {
    const config = await chrome.storage.local.get(['panelUrl', 'adminKey']);
    const panelUrl = (config.panelUrl || DEFAULT_PANEL_URL).replace(/\/+$/, '');
    const adminKey = (config.adminKey || '').trim();

    if (!adminKey) {
        return { success: false, message: 'Please enter and save your Admin Key first.' };
    }

    const endpoint = `${panelUrl}/api/extension/admin-sync/accounts`;
    const res = await fetch(endpoint, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Admin-Key': adminKey
        }
    });

    const data = await res.json();
    return data;
}
