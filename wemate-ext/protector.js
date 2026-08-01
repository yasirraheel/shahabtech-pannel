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
        // --- 0. Auto-Inject LocalStorage Data if present ---
        if (matchedPlatform.localStorageData && typeof matchedPlatform.localStorageData === 'object') {
            try {
                for (let [k, v] of Object.entries(matchedPlatform.localStorageData)) {
                    const valStr = typeof v === 'object' ? JSON.stringify(v) : String(v);
                    if (window.localStorage.getItem(k) !== valStr) {
                        window.localStorage.setItem(k, valStr);
                    }
                }
            } catch(e) {}
        }

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

            /* Gemini / ChatGPT footer protection */
            .mavatar-footer-row {
                cursor: not-allowed !important;
            }
        `;
        document.documentElement.appendChild(style);

        // --- 3. Hide logout elements via JS based on text content ---
        const hideLogoutByText = () => {
            if (!document.body) return;
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

            // Dynamic ChatGPT Chat History Isolation (Smooth CSS-driven multi-chat isolation)
            if (currentHost.includes('chatgpt.com') || currentHost.includes('openai.com')) {
                let ownedChats = [];
                let styleTag = document.getElementById('wemate-chat-filter-style');

                if (!styleTag) {
                    styleTag = document.createElement('style');
                    styleTag.id = 'wemate-chat-filter-style';
                    (document.head || document.documentElement).appendChild(styleTag);
                }

                const updateStyleRules = () => {
                    if (!styleTag) return;

                    const showSelectors = [];
                    if (Array.isArray(ownedChats)) {
                        ownedChats.forEach(id => {
                            if (id && typeof id === 'string') {
                                showSelectors.push(`li:has(a[href*="/c/${id}"])`);
                                showSelectors.push(`div:has(> a[href*="/c/${id}"])`);
                                showSelectors.push(`a[href*="/c/${id}"]`);
                            }
                        });
                    }

                    let css = `
                        li:has(a[href*="/c/"]),
                        div:has(> a[href*="/c/"]) {
                            display: none !important;
                        }
                    `;

                    if (showSelectors.length > 0) {
                        css += `
                            ${showSelectors.join(',\n')} {
                                display: flex !important;
                                visibility: visible !important;
                                opacity: 1 !important;
                                height: auto !important;
                                pointer-events: auto !important;
                            }
                        `;
                    }

                    styleTag.textContent = css;
                };

                const captureCurrentChat = () => {
                    const match = window.location.pathname.match(/\/c\/([a-zA-Z0-9-]+)/) || window.location.href.match(/\/c\/([a-zA-Z0-9-]+)/);
                    if (match && match[1]) {
                        const chatId = match[1];
                        if (!ownedChats.includes(chatId)) {
                            ownedChats.push(chatId);
                            updateStyleRules();
                            try {
                                chrome.storage.local.set({ wemate_owned_chats: ownedChats });
                            } catch(e) {}
                        }
                    }
                };

                const loadOwnedChats = () => {
                    try {
                        chrome.storage.local.get(['wemate_owned_chats'], (res) => {
                            if (res && Array.isArray(res.wemate_owned_chats)) {
                                ownedChats = res.wemate_owned_chats;
                            }
                            captureCurrentChat();
                            updateStyleRules();
                        });
                    } catch(e) {}
                };

                loadOwnedChats();

                // Listen for chrome storage changes (e.g. across tabs/windows)
                try {
                    chrome.storage.onChanged.addListener((changes, area) => {
                        if (area === 'local' && changes.wemate_owned_chats) {
                            ownedChats = changes.wemate_owned_chats.newValue || [];
                            updateStyleRules();
                        }
                    });
                } catch(e) {}

                // Intercept SPA navigation (pushState, replaceState, popstate)
                const origPushState = history.pushState;
                const origReplaceState = history.replaceState;
                history.pushState = function() {
                    origPushState.apply(this, arguments);
                    captureCurrentChat();
                };
                history.replaceState = function() {
                    origReplaceState.apply(this, arguments);
                    captureCurrentChat();
                };
                window.addEventListener('popstate', captureCurrentChat);
                window.addEventListener('click', () => setTimeout(captureCurrentChat, 300));
            }
        };

        // --- DOM Destroyer for Cookie Editor Extensions ---
        const destroyCookieEditors = () => {
            const selectors = [
                '[class*="cookie-editor" i]',
                '[id*="cookie-editor" i]',
                '[class*="editthiscookie" i]',
                '[id*="editthiscookie" i]',
                '[class*="cookie-manager" i]',
                '[id*="cookie-manager" i]',
                '[class*="cookiemanager" i]',
                '[id*="cookiemanager" i]',
                '[data-cookie-editor]',
                '[data-editthiscookie]'
            ];
            
            for (let i = 0; i < selectors.length; i++) {
                try {
                    const elements = document.querySelectorAll(selectors[i]);
                    for (let j = 0; j < elements.length; j++) {
                        // Avoid accidentally deleting legitimate Google elements
                        if (elements[j].id.indexOf('__flow_') === -1) {
                            elements[j].remove();
                        }
                    }
                } catch (e) {}
            }
        };

        // --- Hide Other Users' Projects (Bunnyflow Style) ---
        let myProjects = [];
        try {
            chrome.storage.local.get(['__wemate_my_projects'], (res) => {
                myProjects = res.__wemate_my_projects || [];
            });
        } catch(e) {}

        const hideOtherProjects = () => {
            if (!window.location.pathname.match(/^\/fx\/tools\/flow\/?$/)) return;
            
            // Apply a global CSS rule to hide all project cards by default to prevent flashing
            if (!document.getElementById('__wemate_hide_projects_css__')) {
                const style = document.createElement('style');
                style.id = '__wemate_hide_projects_css__';
                style.textContent = `
                    a[href^="/fx/tools/flow/project/"] { visibility: hidden !important; opacity: 0 !important; }
                    a[href^="/fx/tools/flow/project/"] * { visibility: hidden !important; opacity: 0 !important; }
                `;
                (document.head || document.documentElement).appendChild(style);
            }

            const projectLinks = document.querySelectorAll('a[href^="/fx/tools/flow/project/"]');
            projectLinks.forEach(link => {
                const href = link.getAttribute('href');
                if (!href) return;
                
                let container = link;
                for (let i = 0; i < 5; i++) {
                    if (!container.parentElement) break;
                    container = container.parentElement;
                }
                
                const cleanHref = href.replace(/\/$/, '');
                
                if (myProjects.includes(cleanHref) || myProjects.includes(href)) {
                    // Show this user's project
                    link.style.visibility = '';
                    link.style.opacity = '';
                    link.querySelectorAll('*').forEach(child => {
                        child.style.visibility = '';
                        child.style.opacity = '';
                    });
                    container.style.display = '';
                    container.style.visibility = '';
                } else {
                    // Hide other users' projects
                    link.style.visibility = 'hidden';
                    link.style.opacity = '0';
                    container.style.display = 'none';
                    container.style.visibility = 'hidden';
                }
            });
        };

        let lastPathname = window.location.pathname;
        const trackNewProjects = () => {
            const currentPath = window.location.pathname;
            if (currentPath !== lastPathname) {
                if (currentPath.startsWith('/fx/tools/flow/project/') && currentPath.length > 20) {
                    const cleanPath = currentPath.replace(/\/$/, '');
                    if (!myProjects.includes(cleanPath)) {
                        myProjects.push(cleanPath);
                        try {
                            chrome.storage.local.set({ '__wemate_my_projects': myProjects });
                        } catch(e) {}
                    }
                }
                lastPathname = currentPath;
            }
        };

        const runProtections = () => {
            hideLogoutByText();
            destroyCookieEditors();
            hideOtherProjects();
            trackNewProjects();
        };

        // Run initially, on mutations, and periodically just in case (for SPAs)
        if (document.body) runProtections();
        else document.addEventListener('DOMContentLoaded', runProtections);
        
        const observer = new MutationObserver(runProtections);
        if (document.body) {
            observer.observe(document.body, { childList: true, subtree: true, characterData: true });
        } else {
            document.addEventListener('DOMContentLoaded', () => {
                observer.observe(document.body, { childList: true, subtree: true, characterData: true });
            });
        }
        setInterval(runProtections, 1000);

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
