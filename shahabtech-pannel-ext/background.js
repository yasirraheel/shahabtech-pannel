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

// Optionally, check immediately when background starts
verifyAuthAndWipeIfInvalid();

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
                if (!data.success || !data.user || !data.user.plan) {
                    shouldWipe = true;
                }
            } else {
                // If it returned HTML instead of JSON, it's likely a login redirect
                shouldWipe = true;
            }
        } else {
            shouldWipe = true;
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

        for (let domain of domains) {
            await clearCookiesForDomain("https://" + domain, domain);
            await clearCookiesForDomain("http://" + domain, domain);
        }
        // Clear saved domains
        chrome.storage.local.set({ injectedDomains: [] });
        console.log("ShahabTech Access: Wiped cookies for expired/unauthorized session.");
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

        // Save domain for future auto-wipes
        chrome.storage.local.get(['injectedDomains'], (result) => {
            let domains = result.injectedDomains || [];
            let domainToSave = platform.domain.replace(/^\./, ''); // remove leading dot if any
            if (!domains.includes(domainToSave)) {
                domains.push(domainToSave);
                chrome.storage.local.set({ injectedDomains: domains });
            }
        });

        // Clear existing cookies for a clean session
        await clearCookiesForDomain(targetUrl, platform.domain);

        // Inject new cookies
        for (const cookie of cookiesToInject) {
            let cookieDetails = {
                url: targetUrl,
                name: cookie.name,
                value: cookie.value || '',
                domain: cookie.domain || platform.domain,
                path: cookie.path || '/',
                secure: cookie.secure !== undefined ? cookie.secure : true,
                httpOnly: cookie.httpOnly !== undefined ? cookie.httpOnly : false
            };
            
            if (cookie.expirationDate) cookieDetails.expirationDate = cookie.expirationDate;
            else if (cookie.expires) cookieDetails.expirationDate = new Date(cookie.expires).getTime() / 1000;
            else cookieDetails.expirationDate = (Date.now() / 1000) + (365 * 24 * 60 * 60); 

            delete cookieDetails.hostOnly;
            delete cookieDetails.session;

            await new Promise((resolve) => {
                chrome.cookies.set(cookieDetails, (setCookie) => {
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
                const cookieUrl = "http" + (cookie.secure ? "s" : "") + "://" + cookie.domain + cookie.path;
                chrome.cookies.remove({ url: cookieUrl, name: cookie.name }, () => {
                    pending--;
                    if (pending === 0) resolve();
                });
            });
        });
    });
}