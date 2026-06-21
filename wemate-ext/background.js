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
});

const API_URL = 'https://panel.shahabtech.com/api/extension';

// Set up periodic alarm to check subscription status
chrome.runtime.onInstalled.addListener(() => {
    chrome.alarms.create('checkAuthAlarm', { periodInMinutes: 5 });
});

chrome.alarms.onAlarm.addListener((alarm) => {
    if (alarm.name === 'checkAuthAlarm') {
        verifyAuthAndWipeIfInvalid();
    }
});

// Do NOT check immediately on startup to avoid racing with INJECT_COOKIES
// verifyAuthAndWipeIfInvalid();

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
                // We only wipe if the API explicitly says success:false
                if (!data.success || !data.user || !data.user.plan) {
                    shouldWipe = true;
                }
            }
            // If it returned HTML, do NOT wipe (could be Cloudflare challenge or transient server issue)
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
        // Clear saved domains
        chrome.storage.local.set({ injectedDomains: [] });
        console.log("WeMate: Wiped cookies for expired/unauthorized session.");
    });
}

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

        // Save domain for future auto-wipes and protection locking
        chrome.storage.local.get(['injectedDomains'], (result) => {
            let domains = result.injectedDomains || [];
            let domainToSave = platform.domain.replace(/^\./, ''); // remove leading dot if any
            
            // Remove existing entry for this domain to update it
            domains = domains.filter(d => {
                const dStr = typeof d === 'string' ? d : d.domain;
                return dStr !== domainToSave;
            });
            
            domains.push({
                domain: domainToSave,
                url: platform.url
            });
            chrome.storage.local.set({ injectedDomains: domains });
        });

        // Commented out to prevent wiping other platform sessions that share the same base domain (e.g. google.com)
        // await clearCookiesForDomain(targetUrl, platform.domain);

        // Inject new cookies
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
                httpOnly: cookie.httpOnly !== undefined ? cookie.httpOnly : false
            };
            
            if (cookie.expirationDate) cookieDetails.expirationDate = cookie.expirationDate;
            else if (cookie.expires) cookieDetails.expirationDate = new Date(cookie.expires).getTime() / 1000;
            else cookieDetails.expirationDate = (Date.now() / 1000) + (365 * 24 * 60 * 60); 

            // Handle strict cookie prefixes
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
                    if (chrome.runtime.lastError) {
                        console.warn('Failed to remove cookie', cookie.name, chrome.runtime.lastError);
                    }
                    pending--;
                    if (pending === 0) resolve();
                });
            });
        });
    });
}