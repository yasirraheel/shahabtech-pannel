// BunnyFlow Site Bridge v3.3
// Runs on BunnyFlow platform — auto-connects extension on any supported domain

const STORAGE_KEY = '__flow_auth__';
const HEARTBEAT_KEY = '__bf_ext_active';
const HEARTBEAT_INTERVAL = 20000; // 20 seconds

// Set extension presence heartbeat so platform knows extension is active
function setHeartbeat() {
  try {
    sessionStorage.setItem(HEARTBEAT_KEY, Date.now().toString());
  } catch(e) {}
}

setHeartbeat();
setInterval(setHeartbeat, HEARTBEAT_INTERVAL);

// Normalize origin: always use flowbybunny.com
function _bfNormalizeOrigin(origin) {
  if (!origin) return 'https://flowbybunny.com';
  // Strip www if present
  return origin.replace('https://www.', 'https://').replace('http://www.', 'https://');
}

// Debounce: only call syncAuth at most once per 3 seconds
var _bfSyncTimer = null;
function syncAuthDebounced() {
  if (_bfSyncTimer) return; // already pending, skip
  _bfSyncTimer = setTimeout(function() {
    _bfSyncTimer = null;
    syncAuth();
  }, 3000);
}

// Core sync: read __flow_auth__ from localStorage and send SITE_AUTH to background
function syncAuth() {
  try {
    const raw = localStorage.getItem(STORAGE_KEY);
    if (!raw) return;
    const data = JSON.parse(raw);
    if (!data || !data.userId) return;
    const apiBase = _bfNormalizeOrigin(window.location.origin);
    chrome.runtime.sendMessage(
      { type: 'SITE_AUTH', data: { ...data, apiBase } },
      () => { if (chrome.runtime.lastError) {} }
    );
  } catch(e) {}
}

// Run immediately (catches already-logged-in users)
syncAuth();

// ── Primary trigger: custom event dispatched by auth.tsx the moment auth is ready ──
// This fires as soon as React finishes the login or user-data fetch — no polling needed
window.addEventListener('__bf_auth_ready__', (e) => {
  try {
    const data = e.detail;
    if (!data || !data.userId) return;
    chrome.runtime.sendMessage(
      { type: 'SITE_AUTH', data: { ...data, apiBase: _bfNormalizeOrigin(window.location.origin) } },
      () => { if (chrome.runtime.lastError) {} }
    );
  } catch(e) {}
});

// ── Fallback: poll 6× every 5 seconds (30s total) if event was missed ──
// (Covers slow APIs and cases where __bf_auth_ready__ event is missed)
var _bfPollCount = 0;
var _bfPollTimer = setInterval(function() {
  _bfPollCount++;
  syncAuth();
  if (_bfPollCount >= 6) clearInterval(_bfPollTimer); // poll 6× every 5s = 30s
}, 5000);

// ── Fallback 2: storage event from other tabs (no debounce needed, rare) ──
window.addEventListener('storage', (e) => {
  if (e.key === STORAGE_KEY) syncAuth();
});

// ── Listen for BF_SYNC_NOW from background (triggered by popup) ──
// When the popup opens while on the portal, it asks background to trigger a sync.
// Background sends BF_SYNC_NOW here, we call syncAuth() immediately.
chrome.runtime.onMessage.addListener(function(msg, sender, sendResponse) {
  if (msg && msg.type === 'BF_SYNC_NOW') {
    syncAuth();
    sendResponse({ ok: true });
  }
  return false;
});

// Listen for video open events from platform
window.addEventListener('FLOW_OPEN_VIDEO', (e) => {
  if (e && e.detail && e.detail.url) {
    chrome.runtime.sendMessage(
      { type: 'OPEN_VIDEO', url: e.detail.url },
      () => { if (chrome.runtime.lastError) {} }
    );
  }
});
