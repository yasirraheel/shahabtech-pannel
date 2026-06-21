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

        // --- 2. Hide logout elements and profile menus via CSS ---
        const style = document.createElement('style');
        style.innerHTML = `
            a[href*="logout" i], a[href*="signout" i], a[href*="sign-out" i],
            [class*="logout" i], [class*="signout" i], [id*="logout" i],
            button:has(img[alt*="profile" i]), 
            button:has(img[alt*="Profile" i]),
            button:has(img[src*="googleusercontent" i]),
            [aria-label*="Profile" i],
            [aria-label*="account" i]:not(.mavatar-footer-left) {
                display: none !important;
                pointer-events: none !important;
                opacity: 0 !important;
                visibility: hidden !important;
            }

            /* Make Gemini bottom profile area unclickable with warning cursor */
            .mavatar-footer-row {
                cursor: not-allowed !important;
            }
            .mavatar-footer-row * {
                cursor: not-allowed !important;
            }
            .mavatar-footer-row a, 
            .mavatar-footer-row button, 
            .mavatar-footer-row [role="button"] {
                pointer-events: none !important;
            }
        `;
        document.documentElement.appendChild(style);

        // --- 3. Hide logout elements via JS based on text content ---
        const hideLogoutByText = () => {
            const walkers = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT, null, false);
            let node;
            while (node = walkers.nextNode()) {
                const text = (node.nodeValue || '').toLowerCase();
                if (text.includes('sign out') || text.includes('log out') || text.includes('logout') || text.includes('signout')) {
                    // Hide the closest clickable parent (button, a, or the parent element)
                    const parent = node.parentElement;
                    if (parent) {
                        const clickable = parent.closest('button, a, [role="button"], [role="menuitem"], li, .btn, div');
                        if (clickable) {
                            clickable.style.setProperty('display', 'none', 'important');
                        } else {
                            parent.style.setProperty('display', 'none', 'important');
                        }
                    }
                }
            }

            // Also forcefully disable Gemini footer
            const footerRows = document.querySelectorAll('.mavatar-footer-row, .mavatar-footer-left');
            footerRows.forEach(row => {
                row.style.setProperty('cursor', 'not-allowed', 'important');
                row.querySelectorAll('a').forEach(link => {
                    link.removeAttribute('href');
                    link.style.setProperty('pointer-events', 'none', 'important');
                    link.style.setProperty('cursor', 'not-allowed', 'important');
                });
                row.querySelectorAll('button, [role="button"]').forEach(btn => {
                    btn.disabled = true;
                    btn.style.setProperty('pointer-events', 'none', 'important');
                    btn.style.setProperty('cursor', 'not-allowed', 'important');
                });
            });
        };

        // Run initially, on mutations, and periodically just in case (for SPAs)
        if (document.body) hideLogoutByText();
        else document.addEventListener('DOMContentLoaded', hideLogoutByText);
        
        const observer = new MutationObserver(hideLogoutByText);
        if (document.body) {
            observer.observe(document.body, { childList: true, subtree: true, characterData: true });
        } else {
            document.addEventListener('DOMContentLoaded', () => {
                observer.observe(document.body, { childList: true, subtree: true, characterData: true });
            });
        }
        setInterval(hideLogoutByText, 1000);

        // --- 4. Prevent clicks on things that say "logout" ---
        document.addEventListener('click', (e) => {
            if (e.target.closest('.mavatar-footer-row') || e.target.closest('.mavatar-footer-left')) {
                e.preventDefault();
                e.stopPropagation();
                return;
            }

            const target = e.target.closest('a, button, li, div, span, [role="button"], [role="menuitem"]');
            if (target) {
                const text = (target.innerText || '').toLowerCase().trim();
                const href = (target.getAttribute('href') || '').toLowerCase();
                if (text.includes('sign out') || text.includes('log out') || text.includes('logout') || text.includes('signout') ||
                    href.includes('logout') || href.includes('signout')) {
                    e.preventDefault();
                    e.stopPropagation();
                    alert("Logging out is disabled to protect the shared account.");
                }
            }
        }, true); // use capture phase
    }
});
