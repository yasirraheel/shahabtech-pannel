chrome.runtime.onMessage.addListener((request, sender, sendResponse) => {
    if (request.type === 'WIPE_COOKIES') {
        wipeAllInjectedCookies();
        sendResponse({ success: true });
        return false;
    }

    if (request.type === 'INJECT_COOKIES') {
        handleCookieInjection(request.platform, request.cookies, request.targetTabId)
            .then(() => sendResponse({ success: true }))
            .catch((err) => sendResponse({ success: false, error: err.message }));
        return true; // Keep message channel open for async
    }

    if (request.type === 'REINJECT_PLATFORM_COOKIES') {
        reinjectDomainCookies(request.domain, sender.tab ? sender.tab.id : null);
        sendResponse({ success: true });
        return true;
    }
});

const API_URL = 'https://panel.shahabtech.com/api/extension';

// Set up periodic alarm to refresh cookie TTL and prevent expiration
chrome.runtime.onInstalled.addListener(() => {
    chrome.alarms.create('cookieTTLAlarm', { periodInMinutes: 2 });
});

chrome.alarms.onAlarm.addListener((alarm) => {
    if (alarm.name === 'cookieTTLAlarm') {
        refreshCookieTTL();
    }
});

// Watch cookie changes to prevent unauthorized cookie deletion or expiration
let isReinjectingCookies = false;
chrome.cookies.onChanged.addListener((changeInfo) => {
    if (changeInfo.removed && changeInfo.cause === 'explicit' && !isReinjectingCookies) {
        const domain = (changeInfo.cookie.domain || '').replace(/^\./, '');
        chrome.storage.local.get(['injectedDomains'], (result) => {
            let domains = result.injectedDomains || [];
            let matched = domains.find(d => {
                let dStr = typeof d === 'string' ? d : d.domain;
                return dStr && (domain === dStr || domain.endsWith('.' + dStr) || dStr.endsWith('.' + domain));
            });
            if (matched && matched.savedCookies) {
                isReinjectingCookies = true;
                autoInjectCookies(null, matched).then(() => {
                    setTimeout(() => { isReinjectingCookies = false; }, 2000);
                }).catch(() => {
                    isReinjectingCookies = false;
                });
            }
        });
    }
});

async function verifyAuthAndWipeIfInvalid() {
    try {
        const res = await fetch(`${API_URL}/me`, {
            method: 'GET',
            credentials: 'include',
            headers: { 'Accept': 'application/json' }
        });
        
        let shouldWipe = false;

        if (res.status === 401 || res.status === 403) {
            shouldWipe = true;
        } else if (res.ok) {
            const contentType = res.headers.get("content-type");
            if (contentType && contentType.indexOf("application/json") !== -1) {
                const data = await res.json();
                if (!data.success || !data.user) {
                    shouldWipe = true;
                }
            }
        }

        if (shouldWipe) {
            wipeAllInjectedCookies();
        }
    } catch (err) {
        console.warn('Network error checking auth status', err);
    }
}

function wipeAllInjectedCookies() {
    chrome.storage.local.get(['injectedDomains'], async (result) => {
        let domains = result.injectedDomains || [];
        if (domains.length === 0) return;

        for (let item of domains) {
            let domainStr = typeof item === 'string' ? item : item.domain;
            await clearCookiesForDomain("https://" + domainStr, domainStr);
            await clearCookiesForDomain("http://" + domainStr, domainStr);
        }
        chrome.storage.local.set({ injectedDomains: [] });
        console.log("WeMate: Wiped cookies for expired/unauthorized session.");
    });
}

const ONE_YEAR_SEC = 365 * 24 * 60 * 60;

function setSingleCookie(cookie, platformUrl, fallbackDomain) {
    return new Promise((resolve) => {
        let activeDomain = cookie.domain || fallbackDomain;
        let cleanDomainForUrl = activeDomain ? activeDomain.replace(/^\./, '') : '';
        
        let baseUrl = platformUrl || ("https://" + (cleanDomainForUrl || fallbackDomain));
        let dynamicUrl = baseUrl;
        try {
            let u = new URL(baseUrl);
            dynamicUrl = u.origin + (cookie.path || '/');
        } catch(e) {}

        let rawVal = cookie.value || '';
        let cleanVal = rawVal;
        try {
            if (typeof rawVal === 'string' && rawVal.includes('%')) {
                cleanVal = decodeURIComponent(rawVal);
            }
        } catch(e) {
            cleanVal = rawVal;
        }

        let cookieDetails = {
            url: dynamicUrl,
            name: cookie.name,
            value: cleanVal,
            domain: activeDomain,
            path: cookie.path || '/',
            secure: cookie.secure !== undefined ? cookie.secure : true,
            httpOnly: cookie.httpOnly !== undefined ? cookie.httpOnly : false,
            expirationDate: (Date.now() / 1000) + ONE_YEAR_SEC // 1 Year Persistent
        };

        if (cookie.sameSite) {
            const s = String(cookie.sameSite).toLowerCase();
            if (s === 'no_restriction' || s === 'none') {
                cookieDetails.sameSite = 'no_restriction';
                cookieDetails.secure = true;
            } else if (s === 'lax') {
                cookieDetails.sameSite = 'lax';
            } else if (s === 'strict') {
                cookieDetails.sameSite = 'strict';
            }
        }

        if (cookie.name.startsWith('__Host-') || cookie.hostOnly === true) {
            delete cookieDetails.domain;
            if (cookie.name.startsWith('__Host-')) {
                cookieDetails.path = '/';
                cookieDetails.secure = true;
            }
        } else if (cookie.name.startsWith('__Secure-')) {
            cookieDetails.secure = true;
        }

        delete cookieDetails.hostOnly;
        delete cookieDetails.session;
        delete cookieDetails.storeId;

        chrome.cookies.set(cookieDetails, (setCookie) => {
            if (chrome.runtime.lastError) {
                delete cookieDetails.domain;
                chrome.cookies.set(cookieDetails, () => resolve());
            } else {
                resolve();
            }
        });
    });
}

async function handleCookieInjection(platform, cookiesDataInput, targetTabId) {
    try {
        if (!platform || !cookiesDataInput) throw new Error('Invalid platform or cookies data.');
        
        let parsedData = cookiesDataInput;
        if (typeof parsedData === 'string') {
            try { parsedData = JSON.parse(parsedData); } catch(e) {}
        }

        let cookiesToInject = [];
        let localStorageData = null;

        if (Array.isArray(parsedData)) {
            cookiesToInject = parsedData;
        } else if (parsedData && typeof parsedData === 'object') {
            cookiesToInject = parsedData.cookies || [];
            localStorageData = parsedData.localStorage || parsedData.local_storage || null;
        }

        if (!Array.isArray(cookiesToInject) || (cookiesToInject.length === 0 && !localStorageData)) {
            throw new Error('No valid cookies or localStorage found for this account.');
        }

        // Save domain, cookies & localStorage for continuous auto-reinjection and locking
        chrome.storage.local.get(['injectedDomains'], (result) => {
            let domains = result.injectedDomains || [];
            let domainToSave = platform.domain.replace(/^\./, '');
            
            domains = domains.filter(d => {
                const dStr = typeof d === 'string' ? d : d.domain;
                return dStr !== domainToSave;
            });
            
            domains.push({
                domain: domainToSave,
                url: platform.url,
                savedCookies: cookiesToInject,
                localStorageData: localStorageData
            });
            chrome.storage.local.set({ injectedDomains: domains });
        });

        // Inject cookies with persistent 1-Year expiration and URL alignment
        for (const cookie of cookiesToInject) {
            await setSingleCookie(cookie, platform.url, platform.domain);
        }

        if (targetTabId) {
            chrome.tabs.reload(targetTabId);
        } else {
            chrome.tabs.create({ url: platform.url });
        }
    } catch (error) {
        throw error;
    }
}

function clearCookiesForDomain(url, domainStr) {
    return new Promise((resolve) => {
        chrome.cookies.getAll({ domain: domainStr }, (cookies) => {
            if (!cookies || cookies.length === 0) {
                resolve();
                return;
            }
            let pending = cookies.length;
            cookies.forEach(cookie => {
                const cleanDomain = cookie.domain.replace(/^\./, '');
                const cookieUrl = "http" + (cookie.secure ? "s" : "") + "://" + cleanDomain + cookie.path;
                chrome.cookies.remove({ url: cookieUrl, name: cookie.name }, () => {
                    pending--;
                    if (pending === 0) resolve();
                });
            });
        });
    });
}

function refreshCookieTTL() {
    chrome.storage.local.get(['injectedDomains'], (result) => {
        let domains = result.injectedDomains || [];
        if (domains.length === 0) return;

        domains.forEach(item => {
            let domainStr = typeof item === 'string' ? item : item.domain;
            let platformUrl = item.url || ("https://" + domainStr);
            chrome.cookies.getAll({ domain: domainStr }, (cookies) => {
                if (!cookies) return;
                cookies.forEach(cookie => {
                    setSingleCookie(cookie, platformUrl, domainStr);
                });
            });
        });
    });
}

// Auto-Reinjection mechanism for ChatGPT, Google Flow, HeyGen, and all platforms
const autoInjectedTabs = new Map();

function shouldAutoInject(url, domains) {
    try {
        let p = new URL(url);
        for (let d of domains) {
            let domainStr = typeof d === 'string' ? d : d.domain;
            if (!domainStr) continue;
            let cleanDomain = domainStr.replace(/^\./, '');
            if (p.hostname === cleanDomain || p.hostname.endsWith('.' + cleanDomain)) {
                return d;
            }
        }
    } catch(e) {}
    return null;
}

function reinjectDomainCookies(targetDomainStr, tabId) {
    chrome.storage.local.get(['injectedDomains'], (result) => {
        let domains = result.injectedDomains || [];
        let matched = domains.find(d => {
            let dStr = typeof d === 'string' ? d : d.domain;
            return dStr && (dStr.includes(targetDomainStr) || targetDomainStr.includes(dStr));
        });
        if (matched) {
            autoInjectCookies(tabId, matched);
        }
    });
}

async function autoInjectCookies(tabId, matchedDomainObj) {
    if (!matchedDomainObj || !matchedDomainObj.savedCookies) return;
    
    if (tabId) {
        const last = autoInjectedTabs.get(tabId);
        const now = Date.now();
        if (last && (now - last) < 2000) return;
        autoInjectedTabs.set(tabId, now);
    }

    let cookiesToInject = matchedDomainObj.savedCookies;
    let platformUrl = matchedDomainObj.url || ("https://" + matchedDomainObj.domain);
    for (const cookie of cookiesToInject) {
        await setSingleCookie(cookie, platformUrl, matchedDomainObj.domain);
    }
}

function handleTabNavigation(tabId, url) {
    if (!url) return;
    chrome.storage.local.get(['injectedDomains'], (result) => {
        let domains = result.injectedDomains || [];
        if (domains.length === 0) return;
        
        let matched = shouldAutoInject(url, domains);
        if (matched) {
            autoInjectCookies(tabId, matched);
        }
    });
}

chrome.tabs.onUpdated.addListener((tabId, changeInfo, tab) => {
    handleTabNavigation(tabId, changeInfo.url || (tab && tab.url));
});
chrome.tabs.onCreated.addListener((tab) => {
    if (tab && tab.id != null) handleTabNavigation(tab.id, tab.url || tab.pendingUrl);
});
chrome.tabs.onRemoved.addListener((tabId) => {
    autoInjectedTabs.delete(tabId);
});