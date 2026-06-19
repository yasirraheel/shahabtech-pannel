chrome.runtime.onMessage.addListener((request, sender, sendResponse) => {
    if (request.type === 'INJECT_COOKIES') {
        handleCookieInjection(request.platformId, request.apiUrl, request.token)
            .then(() => sendResponse({ success: true }))
            .catch((err) => sendResponse({ success: false, message: err.message }));
        return true; // Keep message channel open for async
    }
});

async function handleCookieInjection(platformId, apiUrl, token) {
    try {
        // Fetch cookies from the backend
        const res = await fetch(`${apiUrl}/api/extension/cookies/${platformId}`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });

        if (res.status === 401) {
            throw new Error('Session expired. Please log in again.');
        }

        const result = await res.json();
        
        if (!result.success || !result.cookies || !result.platform) {
            throw new Error(result.message || 'Failed to fetch account credentials.');
        }

        const platform = result.platform;
        let cookiesToInject = result.cookies;

        if (typeof cookiesToInject === 'string') {
            try { cookiesToInject = JSON.parse(cookiesToInject); } catch(e) {}
        }

        if (!Array.isArray(cookiesToInject) || cookiesToInject.length === 0) {
            throw new Error('No valid cookies found for this account.');
        }

        // Get the base URL to inject cookies
        const targetUrl = new URL(platform.url).origin;

        // Optionally, clear existing cookies for the domain first to ensure a clean session
        // (This prevents mixing user's personal session with the shared session)
        await clearCookiesForDomain(targetUrl, platform.domain);

        // Inject the new cookies
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
            
            // Set expiration if provided, otherwise it's a session cookie
            if (cookie.expirationDate) {
                cookieDetails.expirationDate = cookie.expirationDate;
            } else if (cookie.expires) {
                cookieDetails.expirationDate = new Date(cookie.expires).getTime() / 1000;
            } else {
                // Ensure it stays valid for at least a year if no expiry is set to prevent random logouts
                cookieDetails.expirationDate = (Date.now() / 1000) + (365 * 24 * 60 * 60); 
            }

            // Remove hostOnly/session flags which are read-only and cause errors if passed
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

        // Open the platform in a new tab
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