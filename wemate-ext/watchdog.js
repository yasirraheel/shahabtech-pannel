// watchdog.js
// Runs on all injected domains to prevent malicious extension disabling

const CHECK_INTERVAL = 1000;
let failCount = 0;

function destroySessionAndLock() {
    // 1. Destroy all cookies for the current domain
    try {
        const domains = [window.location.hostname, '.' + window.location.hostname, window.location.hostname.replace(/^[^.]+\./, '.'), ''];
        const paths = ['/', window.location.pathname, '/fx', '/fx/tools/flow'];
        const cookies = document.cookie.split(';');
        
        for (let c of cookies) {
            let name = (c.split('=')[0] || '').trim();
            if (!name) continue;
            for (let d of domains) {
                for (let p of paths) {
                    let clearStr = name + '=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=' + p;
                    if (d) clearStr += ';domain=' + d;
                    document.cookie = clearStr;
                    document.cookie = clearStr + ';secure';
                }
            }
        }
    } catch(e) {}

    // 2. Clear local storage
    try { localStorage.clear(); } catch(e) {}
    try { sessionStorage.clear(); } catch(e) {}

    // 3. Throw the user to a blank page
    try {
        window.location.replace('about:blank');
    } catch(e) {
        window.location.href = 'about:blank';
    }
}

function checkExtensionStatus() {
    // If the extension is forcibly disabled or uninstalled by the user, chrome.runtime.id becomes undefined.
    // This allows us to catch users trying to disable the extension to steal cookies or bypass locks.
    const isGone = (typeof chrome === 'undefined' || !chrome.runtime || !chrome.runtime.id);
    
    if (isGone) {
        failCount++;
        if (failCount >= 3) { // 3 seconds grace period
            destroySessionAndLock();
        }
    } else {
        failCount = 0;
    }
}

setInterval(checkExtensionStatus, CHECK_INTERVAL);
