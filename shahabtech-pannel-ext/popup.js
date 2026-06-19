/* BunnyFlow popup — daily-plan basis only, credits system removed */
const DEFAULT_API = 'https://flowbybunny.com';
const RENEW_THRESHOLD_DAYS = 5; // show renew banner when <= 5 days left

function $(id) { return document.getElementById(id); }

async function getApiBase() {
  const r = await chrome.storage.local.get('apiBase');
  return r.apiBase || DEFAULT_API;
}

function initials(name) {
  if (!name) return '?';
  return name.split(' ').map(p => p[0]).slice(0, 2).join('').toUpperCase();
}

function computeDaysLeft(data) {
  // Prefer extension2_days if present, otherwise compute from planExpires
  let days = parseInt(data.extension2_days, 10);
  if (!isNaN(days)) return days;
  const exp = data.planExpires || data.expirationDate || data.expiry;
  if (exp) {
    const d = new Date(exp);
    if (!isNaN(d.getTime())) {
      return Math.max(0, Math.ceil((d.getTime() - Date.now()) / 86400000));
    }
  }
  return null;
}

function updateRenewBanner(days) {
  const banner = $('renew-banner');
  if (!banner) return;
  if (days !== null && days <= RENEW_THRESHOLD_DAYS && days >= 0) {
    $('renew-days').textContent = days;
    if (days === 0) {
      $('renew-msg').innerHTML = 'Your plan has <b>expired today</b>. Renew now to restore access.';
    } else if (days === 1) {
      $('renew-msg').innerHTML = 'Your plan expires in <b>1 day</b>. Renew to keep uninterrupted access.';
    } else {
      $('renew-msg').innerHTML = 'Your plan expires in <b>' + days + '</b> days. Renew to keep uninterrupted access.';
    }
    banner.style.display = 'block';
  } else {
    banner.style.display = 'none';
  }
}

// ── Heart-lock: tryAutoConnect (ported from v3.9.8 popup_extra.js) ────────────
// Jab popup khule aur NOT connected ho, check karo koi portal tab open hai.
// Agar hai to: lock-emoji ❤️ → ⏳, header text "Auto-connecting…" ho jata hai,
// aur background ko BF_PORTAL_SYNC_REQ bhejta hai taake fresh auth mile.
function tryAutoConnect(storedToken) {
  if (storedToken) return; // already connected
  if (typeof chrome === 'undefined' || !chrome.tabs) return;
  chrome.tabs.query({ url: 'https://flowbybunny.com/*' }, function(tabs) {
    if (!tabs || tabs.length === 0) return;
    var lockEmoji = document.querySelector('.lock-emoji');
    var notConnH3 = document.querySelector('.not-connected-header h3');
    var notConnP  = document.querySelector('.not-connected-header p');
    if (lockEmoji)  lockEmoji.textContent = '⏳';
    if (notConnH3)  notConnH3.textContent = 'Auto-connecting\u2026';
    if (notConnP)   notConnP.textContent  = 'Syncing with your BunnyFlow session\u2026';
    try {
      chrome.runtime.sendMessage({ type: 'BF_PORTAL_SYNC_REQ' }, function() {
        if (chrome.runtime.lastError) {}
      });
    } catch(_) {}
  });
}

// ── Auto-reload popup when SITE_AUTH saves auth to storage ───────────────────
// Problem: user visits portal → site_bridge sends SITE_AUTH → background saves
// data → but popup is already open showing "Not Connected".
// Fix: watch storage for userId/token arriving, then reload ONLY if not
// already connected (status-screen not visible).
(function() {
  if (typeof chrome === 'undefined' || !chrome.storage || !chrome.storage.onChanged) return;
  var _bfPopupReloaded = false;
  chrome.storage.onChanged.addListener(function(changes, area) {
    if (area !== 'local' || _bfPopupReloaded) return;
    var authKeys = ['userId', 'token', 'sessionToken', 'authToken', 'jwt'];
    var gotAuth = authKeys.some(function(k) { return changes[k] && changes[k].newValue; });
    if (!gotAuth) return;
    var statusScreen = document.getElementById('status-screen');
    var isAlreadyConnected = statusScreen &&
      (statusScreen.style.display === 'block' || statusScreen.style.display === '');
    if (isAlreadyConnected) return;
    _bfPopupReloaded = true;
    setTimeout(function() { window.location.reload(); }, 350);
  });
})();

async function init() {
  const api = await getApiBase();
  if ($('api-base')) $('api-base').value = api;

  const data = await chrome.storage.local.get([
    'userId', 'userName', 'userPlan',
    'cookieData', 'authSource', 'cookieSystemDisabled',
    'sessionCookieCount', 'extension2_days', 'planExpires', 'expirationDate',
    'token', 'sessionToken', 'authToken', 'jwt'
  ]);

  if (data.userId) {
    showStatusScreen(data);
  } else {
    showLoginScreen();
    // Heart-lock: attempt auto-connect from open portal tab (v3.9.8 behaviour)
    var storedToken = data.token || data.sessionToken || data.authToken || data.jwt || '';
    setTimeout(function() { tryAutoConnect(storedToken); }, 200);
  }
}

function showLoadingScreen() {
  $('loading-screen').style.display = 'flex';
  $('login-screen').style.display = 'none';
  $('status-screen').style.display = 'none';
}

function showLoginScreen() {
  $('loading-screen').style.display = 'none';
  $('login-screen').style.display = 'block';
  $('status-screen').style.display = 'none';
}

function showStatusScreen(data) {
  $('loading-screen').style.display = 'none';
  $('login-screen').style.display = 'none';
  $('status-screen').style.display = 'block';

  const name = data.userName || 'Connected';
  $('user-name').textContent = name;
  $('user-avatar').textContent = initials(name);
  const plan = (data.userPlan || 'basic');
  var _PL={'basic':'Basic','pro':'Bunny Plus','ultra':'Bunny Max','starter':'Starter','unlimited':'Unlimited'};
  $('user-plan').textContent = (_PL[plan] || plan.charAt(0).toUpperCase() + plan.slice(1)) + ' Plan';

  const days = computeDaysLeft(data);
  if (days !== null) {
    $('user-days-text').textContent = days + ' day' + (days === 1 ? '' : 's') + ' left';
    $('ext2-days').textContent = days;
    $('ext2-days').style.color = days > 5 ? '#22c55e' : days > 0 ? '#f59e0b' : '#ef4444';
  } else {
    $('user-days-text').textContent = 'Active';
    $('ext2-days').textContent = '—';
  }
  updateRenewBanner(days);

  const sessions = data.sessionCookieCount || (data.cookieData ? data.cookieData.length : 0);
  $('cookies-count').textContent = sessions;

  const autoBadge = $('auto-badge');
  autoBadge.style.display = data.authSource === 'site' ? 'inline-flex' : 'none';

  const disBanner = $('disabled-banner');
  if (data.cookieSystemDisabled) {
    if (disBanner) disBanner.style.display = 'flex';
    $('cookies-count').textContent = 'OFF';
    $('cookies-count').style.color = '#ef4444';
    $('inject-btn').style.display = 'none';
  } else {
    if (disBanner) disBanner.style.display = 'none';
    $('cookies-count').style.color = '';
  }

  chrome.tabs.query({ active: true, currentWindow: true }, tabs => {
    const tab = tabs[0];
    const onFlow = tab && tab.url && tab.url.startsWith('https://labs.google/fx/tools/flow');
    const ind = $('page-indicator');
    if (onFlow) {
      if (data.cookieSystemDisabled) {
        ind.className = 'flow-badge inactive';
        $('page-text').textContent = 'Session system disabled by admin';
        $('inject-btn').style.display = 'none';
      } else {
        ind.className = 'flow-badge active';
        $('page-text').textContent = 'On Google Flow — Flow Active';
        $('inject-btn').style.display = 'block';
      }
    } else {
      ind.className = 'flow-badge inactive';
      $('page-text').textContent = 'Not on Google Flow';
      $('inject-btn').style.display = 'none';
    }
  });
}

chrome.storage.onChanged.addListener((changes, area) => {
  if (area !== 'local') return;
  if (changes.extension2_days !== undefined || changes.planExpires !== undefined) {
    chrome.storage.local.get(['extension2_days', 'planExpires', 'expirationDate'], d => {
      const days = computeDaysLeft(d);
      if (days !== null && $('status-screen').style.display !== 'none') {
        $('user-days-text').textContent = days + ' day' + (days === 1 ? '' : 's') + ' left';
        $('ext2-days').textContent = days;
        $('ext2-days').style.color = days > 5 ? '#22c55e' : days > 0 ? '#f59e0b' : '#ef4444';
        updateRenewBanner(days);
      }
    });
  }
});

$('save-api')?.addEventListener('click', async () => {
  const v = $('api-base').value.trim().replace(/\/$/, '');
  if (!v) return;
  await chrome.storage.local.set({ apiBase: v });
  $('save-api').textContent = '✓';
  setTimeout(() => { $('save-api').textContent = 'Save'; }, 1500);
});

$('login-btn')?.addEventListener('click', async () => {
  const email = $('login-email').value.trim();
  const password = $('login-password').value.trim();
  const errEl = $('login-error');
  if (!email || !password) {
    errEl.textContent = 'Enter your email and password.';
    errEl.style.display = 'block';
    return;
  }
  const btn = $('login-btn');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner"></span>Signing in...';
  errEl.style.display = 'none';
  try {
    const api = await getApiBase();
    const res = await fetch(api + '/api/auth/login', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email, password, version: '1.2' }),
      credentials: 'include'
    });
    const json = await res.json();
    if (!res.ok) throw new Error(json.message || 'Login failed');

    const cRes = await fetch(api + '/api/user/cookies', { credentials: 'include', headers: { 'X-Ext-Version': '1.2' } });
    const cJson = await cRes.json();
    const disabled = !!cJson.disabled;

    await chrome.storage.local.set({
      userId: json.user.id,
      userName: json.user.name,
      userPlan: (json.user.plan || '').toLowerCase(),
      planExpires: json.user.planExpires || json.user.expiresAt || null,
      // Store the real JWT so /api/extension/inject-cookies can verify us.
      token: json.token || '',
      sessionToken: json.token ? (json.user.id + ':' + json.token) : (json.user.id + ':' + email),
      cookieData: disabled ? [] : (cJson.cookies || []),
      apiBase: api,
      authSource: 'manual',
      cookieSystemDisabled: disabled
    });
    // Trigger an immediate cookie injection so the user lands logged-in.
    try { chrome.runtime.sendMessage({ type: 'BUNNYFLOW_INJECT_COOKIES', force: true }, function(){}); } catch (_) {}
    showStatusScreen({
      userName: json.user.name,
      userPlan: json.user.plan,
      planExpires: json.user.planExpires || json.user.expiresAt || null,
      cookieData: disabled ? [] : (cJson.cookies || []),
      authSource: 'manual',
      cookieSystemDisabled: disabled
    });
  } catch (e) {
    if (e.message === 'UPDATE_REQUIRED') {
      errEl.innerHTML = '<b>Update Required:</b> Please download the latest version of the BunnyFlow extension to continue.';
    } else {
      errEl.textContent = e.message || 'Connection failed. Check the server URL.';
    }
    errEl.style.display = 'block';
  } finally {
    btn.disabled = false;
    btn.textContent = 'Sign In';
  }
});

$('inject-btn')?.addEventListener('click', () => {
  const btn = $('inject-btn');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner"></span>Injecting...';
  chrome.runtime.sendMessage({ type: 'INJECT_NOW' }, resp => {
    if (resp && resp.success) {
      btn.textContent = 'Injected! Reloading...';
      setTimeout(() => { btn.disabled = false; btn.textContent = '↻ ReOpen Flow!'; }, 2500);
    } else {
      btn.disabled = false;
      btn.textContent = '↻ ReOpen Flow!';
    }
  });
});

$('logout-btn')?.addEventListener('click', async () => {
  const btn = $('logout-btn');
  btn.disabled = true;
  btn.textContent = 'Disconnecting...';
  try {
    const stored = await chrome.storage.local.get(['cookieData', 'originalCookies']);
    const cookies = stored.cookieData || [];
    const original = stored.originalCookies || [];
    await Promise.allSettled(cookies.map(c => {
      try {
        const url = 'https://' + (c.domain || '').replace(/^\./, '') + (c.path || '/');
        return chrome.cookies.remove({ url, name: c.name }).catch(() => null);
      } catch { return Promise.resolve(null); }
    }));
    if (original.length) {
      await Promise.allSettled(original.map(c => {
        try {
          const dom = (c.domain || '').replace(/^\./, '');
          const cd = {
            url: 'https://' + dom + (c.path || '/'),
            name: c.name, value: c.value, path: c.path || '/',
            secure: c.secure !== false, httpOnly: c.httpOnly !== false,
            sameSite: c.sameSite || 'lax'
          };
          if (!c.hostOnly && c.domain) cd.domain = c.domain;
          if (c.expirationDate && !c.session) cd.expirationDate = c.expirationDate;
          return chrome.cookies.set(cd).catch(() => null);
        } catch { return Promise.resolve(null); }
      }));
    }
    await chrome.storage.local.clear();
    chrome.tabs.query({ active: true, currentWindow: true }, tabs => {
      const t = tabs[0];
      if (t && t.url && t.url.startsWith('https://labs.google/fx/tools/flow')) {
        chrome.tabs.reload(t.id);
      }
    });
    showLoginScreen();
  } catch (e) {
    await chrome.storage.local.clear();
    showLoginScreen();
  }
});

$('login-password')?.addEventListener('keydown', e => {
  if (e.key === 'Enter') $('login-btn').click();
});

init();
