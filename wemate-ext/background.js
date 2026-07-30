chrome.runtime.onMessage.addListener((request, sender, sendResponse) => {
    if (request.type === 'WIPE_COOKIES') {
        wipeAllInjectedCookies();
        sendResponse({ success: true });
        return false;
    }

    if (request.type === 'INJECT_COOKIES') {
        handleCookieInjection(request.platform, request.cookies)
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

// Set up periodic alarm to check subscription status & maintain persistent cookies
chrome.runtime.onInstalled.addListener(() => {
    chrome.alarms.create('checkAuthAlarm', { periodInMinutes: 5 });
    chrome.alarms.create('cookieTTLAlarm', { periodInMinutes: 2 });
});

chrome.alarms.onAlarm.addListener((alarm) => {
    if (alarm.name === 'checkAuthAlarm') {
        verifyAuthAndWipeIfInvalid();
    } else if (alarm.name === 'cookieTTLAlarm') {
        refreshCookieTTL();
    }
});

// Watch cookie changes to prevent unauthorized cookie deletion or expiration
chrome.cookies.onChanged.addListener((changeInfo) => {
    if (changeInfo.removed && changeInfo.cause !== 'overwrite') {
        const domain = (changeInfo.cookie.domain || '').replace(/^\./, '');
        chrome.storage.local.get(['injectedDomains'], (result) => {
            let domains = result.injectedDomains || [];
            let matched = domains.find(d => {
                let dStr = typeof d === 'string' ? d : d.domain;
                return dStr && (domain === dStr || domain.endsWith('.' + dStr) || dStr.endsWith('.' + domain));
            });
            if (matched && matched.savedCookies) {
                // Auto-reinject missing cookie persistently
                autoInjectCookies(null, matched);
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

async function handleCookieInjection(platform, cookiesToInject) {
    try {
        if (!platform || !cookiesToInject) throw new Error('Invalid platform or cookies data.');
        
        if (typeof cookiesToInject === 'string') {
            try { cookiesToInject = JSON.parse(cookiesToInject); } catch(e) {}
        }
        if (!Array.isArray(cookiesToInject) || cookiesToInject.length === 0) {
            throw new Error('No valid cookies found for this account.');
        }

        const targetUrl = new URL(platform.url).origin;

        // Save domain & cookies for continuous auto-reinjection and locking
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
                savedCookies: cookiesToInject
            });
            chrome.storage.local.set({ injectedDomains: domains });
        });

        // Inject cookies with persistent 1-Year expiration
        for (const cookie of cookiesToInject) {
            let activeDomain = cookie.domain || platform.domain;
            let cleanDomainForUrl = activeDomain.replace(/^\./, '');
            let dynamicUrl = "http" + (cookie.secure !== false ? "s" : "") + "://" + cleanDomainForUrl + (cookie.path || '/');

            let cookieDetails = {
                url: dynamicUrl,
                name: cookie.name,
                value: cookie.value || '',
                domain: activeDomain,
                path: cookie.path || '/',
                secure: cookie.secure !== undefined ? cookie.secure : true,
                httpOnly: cookie.httpOnly !== undefined ? cookie.httpOnly : false,
                expirationDate: (Date.now() / 1000) + ONE_YEAR_SEC // 1 Year Persistent
            };

            if (cookie.name.startsWith('__Host-')) {
                delete cookieDetails.domain;
                cookieDetails.path = '/';
                cookieDetails.secure = true;
            } else if (cookie.name.startsWith('__Secure-')) {
                cookieDetails.secure = true;
            }

            delete cookieDetails.hostOnly;
            delete cookieDetails.session;

            await new Promise((resolve) => {
                chrome.cookies.set(cookieDetails, (setCookie) => {
                    if (chrome.runtime.lastError) {
                        console.warn('Failed to set cookie', cookieDetails.name, chrome.runtime.lastError.message);
                    }
                    resolve();
                });
            });
        }

        chrome.tabs.create({ url: platform.url });
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

        let persistentExpirationDate = (Date.now() / 1000) + ONE_YEAR_SEC;

        domains.forEach(item => {
            let domainStr = typeof item === 'string' ? item : item.domain;
            chrome.cookies.getAll({ domain: domainStr }, (cookies) => {
                if (!cookies) return;
                cookies.forEach(cookie => {
                    let cleanDomainForUrl = cookie.domain.replace(/^\./, '');
                    let dynamicUrl = "http" + (cookie.secure !== false ? "s" : "") + "://" + cleanDomainForUrl + (cookie.path || '/');

                    let cookieDetails = {
                        url: dynamicUrl,
                        name: cookie.name,
                        value: cookie.value || '',
                        domain: cookie.domain,
                        path: cookie.path || '/',
                        secure: cookie.secure !== undefined ? cookie.secure : true,
                        httpOnly: cookie.httpOnly !== undefined ? cookie.httpOnly : false,
                        expirationDate: persistentExpirationDate
                    };

                    if (cookie.name.startsWith('__Host-')) {
                        delete cookieDetails.domain;
                        cookieDetails.path = '/';
                        cookieDetails.secure = true;
                    } else if (cookie.name.startsWith('__Secure-')) {
                        cookieDetails.secure = true;
                    }

                    chrome.cookies.set(cookieDetails, () => {});
                });
            });
        });
    });
}

// Auto-Reinjection mechanism for ChatGPT, Google Flow, and platforms
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

function autoInjectCookies(tabId, matchedDomainObj) {
    if (!matchedDomainObj || !matchedDomainObj.savedCookies) return;
    
    if (tabId) {
        const last = autoInjectedTabs.get(tabId);
        const now = Date.now();
        if (last && (now - last) < 2000) return;
        autoInjectedTabs.set(tabId, now);
    }

    let cookiesToInject = matchedDomainObj.savedCookies;
    const persistentExpirationDate = (Date.now() / 1000) + ONE_YEAR_SEC;

    for (const cookie of cookiesToInject) {
        let activeDomain = cookie.domain || matchedDomainObj.domain;
        let cleanDomainForUrl = activeDomain.replace(/^\./, '');
        let dynamicUrl = "http" + (cookie.secure !== false ? "s" : "") + "://" + cleanDomainForUrl + (cookie.path || '/');

        let cookieDetails = {
            url: dynamicUrl,
            name: cookie.name,
            value: cookie.value || '',
            domain: activeDomain,
            path: cookie.path || '/',
            secure: cookie.secure !== undefined ? cookie.secure : true,
            httpOnly: cookie.httpOnly !== undefined ? cookie.httpOnly : false,
            expirationDate: persistentExpirationDate
        };

        if (cookie.name.startsWith('__Host-')) {
            delete cookieDetails.domain;
            cookieDetails.path = '/';
            cookieDetails.secure = true;
        } else if (cookie.name.startsWith('__Secure-')) {
            cookieDetails.secure = true;
        }

        chrome.cookies.set(cookieDetails, () => {});
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