// protector.js
// Runs on all URLs at document_start

chrome.storage.local.get(['injectedDomains'], (result) => {
    const domains = result.injectedDomains || [];
    if (domains.length === 0) return;

    const currentHost = window.location.hostname.toLowerCase();
    
    // Find if the current host falls under any injected domain
    let matchedPlatform = null;
    for (let item of domains) {
        let domainStr = typeof item === 'string' ? item : item.domain;
        if (currentHost === domainStr || currentHost.endsWith('.' + domainStr)) {
            matchedPlatform = typeof item === 'object' ? item : { domain: domainStr, url: `https://${domainStr}` };
            break;
        }
    }

    if (matchedPlatform) {
        // --- 1. Prevent top-level navigation to unauthorized paths or logout URLs ---
        if (window.top === window) {
            const currentUrl = window.location.href.toLowerCase();
            let allowedObj;
            try {
                allowedObj = new URL(matchedPlatform.url);
            } catch (e) {
                // Ignore if URL is invalid
            }

            if (allowedObj) {
                // Block logout URLs explicitly
                if (currentUrl.includes('logout') || currentUrl.includes('signout') || currentUrl.includes('sign-out')) {
                    window.location.replace(matchedPlatform.url);
                    return;
                }

                // Path lock logic
                // Only lock if the user has provided a specific path (length > 1)
                if (allowedObj.pathname.length > 1) {
                    // If they are on the exact same host but a different path
                    if (currentHost === allowedObj.hostname.toLowerCase()) {
                        if (!window.location.pathname.toLowerCase().startsWith(allowedObj.pathname.toLowerCase())) {
                            window.location.replace(matchedPlatform.url);
                            return;
                        }
                    } else {
                        // They navigated to a different subdomain entirely (e.g., accounts.google.com instead of labs.google)
                        window.location.replace(matchedPlatform.url);
                        return;
                    }
                }
            }
        }

        // --- 2. Hide logout elements via CSS ---
        const style = document.createElement('style');
        style.innerHTML = `
            a[href*="logout" i], a[href*="signout" i], a[href*="sign-out" i],
            [class*="logout" i], [class*="signout" i], [id*="logout" i] {
                display: none !important;
                pointer-events: none !important;
                opacity: 0 !important;
                visibility: hidden !important;
            }
        `;
        document.documentElement.appendChild(style);

        // --- 3. Prevent clicks on things that say "logout" ---
        document.addEventListener('click', (e) => {
            const target = e.target.closest('a, button, li, div, span');
            if (target) {
                const text = (target.innerText || '').toLowerCase();
                const href = (target.getAttribute('href') || '').toLowerCase();
                if (text.includes('log out') || text.includes('logout') || text.includes('sign out') || text.includes('signout') ||
                    href.includes('logout') || href.includes('signout')) {
                    e.preventDefault();
                    e.stopPropagation();
                    alert("Logging out is disabled to protect the shared account.");
                }
            }
        }, true); // use capture phase
    }
});
