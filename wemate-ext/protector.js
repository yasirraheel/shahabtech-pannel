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
            }

            // Google Flow /about bounce recovery
            if (currentHost.includes('flow.google.com')) {
                const currentPath = window.location.pathname.toLowerCase();
                if (currentPath === '/about' || currentPath === '/about/' || currentPath.startsWith('/flow/about')) {
                    const REDIRECT_KEY = '__wm_flow_about_recovery';
                    const attempts = parseInt(sessionStorage.getItem(REDIRECT_KEY) || '0', 10);
                    if (attempts < 3) {
                        sessionStorage.setItem(REDIRECT_KEY, String(attempts + 1));
                        window.location.replace('https://flow.google.com/');
                        return;
                    }
                } else {
                    try { sessionStorage.removeItem('__wm_flow_about_recovery'); } catch(_) {}
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
                    // Hide the closest clickable parent (button, a, or menu item)
                    const parent = node.parentElement;
                    if (parent) {
                        const clickable = parent.closest('button, a, [role="button"], [role="menuitem"], li, .btn');
                        const targetToHide = clickable || parent;
                        const tTag = (targetToHide.tagName || '').toUpperCase();
                        if (targetToHide !== document.body && targetToHide !== document.documentElement && tTag !== 'MAIN' && tTag !== 'HEADER' && tTag !== 'NAV' && tTag !== 'SECTION') {
                            targetToHide.style.setProperty('display', 'none', 'important');
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

            // Dynamic ChatGPT Chat History Isolation (Show owned chats, hide unowned chats)
            if (currentHost.includes('chatgpt.com') || currentHost.includes('openai.com')) {
                let ownedChats = [];

                try {
                    chrome.storage.local.get(['wemate_owned_chats'], (res) => {
                        if (res && Array.isArray(res.wemate_owned_chats)) {
                            ownedChats = res.wemate_owned_chats;
                        }
                    });
                } catch(e) {}

                const captureCurrentChat = () => {
                    const match = window.location.pathname.match(/\/c\/([a-zA-Z0-9-]+)/);
                    if (match && match[1]) {
                        const chatId = match[1];
                        if (!ownedChats.includes(chatId)) {
                            ownedChats.push(chatId);
                            try {
                                chrome.storage.local.set({ wemate_owned_chats: ownedChats });
                            } catch(e) {}
                        }
                    }
                };

                const filterChatGPTChats = () => {
                    captureCurrentChat();

                    const chatLinks = document.querySelectorAll('a[href*="/c/"], [data-testid="history-item"]');
                    chatLinks.forEach(el => {
                        const href = el.getAttribute('href') || el.querySelector('a')?.getAttribute('href') || '';
                        const match = href.match(/\/c\/([a-zA-Z0-9-]+)/);
                        if (match && match[1]) {
                            const chatId = match[1];
                            const container = el.closest('li') || el;
                            if (ownedChats.includes(chatId)) {
                                el.style.setProperty('display', 'flex', 'important');
                                el.style.setProperty('visibility', 'visible', 'important');
                                el.style.setProperty('opacity', '1', 'important');
                                el.style.setProperty('height', 'auto', 'important');
                                el.style.setProperty('pointer-events', 'auto', 'important');
                                if (container && container !== el) {
                                    container.style.setProperty('display', 'block', 'important');
                                    container.style.setProperty('visibility', 'visible', 'important');
                                }
                            } else {
                                el.style.setProperty('display', 'none', 'important');
                                el.style.setProperty('visibility', 'hidden', 'important');
                                el.style.setProperty('opacity', '0', 'important');
                                el.style.setProperty('height', '0', 'important');
                                el.style.setProperty('pointer-events', 'none', 'important');
                                if (container && container !== el) {
                                    container.style.setProperty('display', 'none', 'important');
                                }
                            }
                        }
                    });
                };

                filterChatGPTChats();
                setInterval(filterChatGPTChats, 800);
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

        // --- Hide Other Users' Projects & Home Thumbnails (FlowByDcx Parity) ---
        let myProjects = [];
        try {
            chrome.storage.local.get(['__wemate_my_projects'], (res) => {
                myProjects = res.__wemate_my_projects || [];
            });
            chrome.storage.onChanged.addListener((changes, area) => {
                if (area === 'local' && changes.__wemate_my_projects) {
                    myProjects = changes.__wemate_my_projects.newValue || [];
                    if (isFlowHomePage()) hideOtherProjects();
                }
            });
        } catch(e) {}

        const NEWP_RE = /new\s*project|dự án mới|\+\s*d|create\s*new|create|start\s*creating|\+\s*new|^\s*\+\s*$/i;

        function isFlowHomePage() {
            const host = window.location.hostname.toLowerCase();
            const path = window.location.pathname.toLowerCase();
            if (host.includes('flow.google.com')) {
                return !path.includes('/project/');
            }
            if (host.includes('labs.google')) {
                return path.includes('/fx/tools/flow') && !path.includes('/project/');
            }
            return false;
        }

        const hideOtherProjects = () => {
            if (!isFlowHomePage()) return;

            // Target only actual project links (Google Flow and Labs Flow)
            const projectLinks = document.querySelectorAll('a[href*="/project/"], a[href^="/fx/tools/flow/"]');
            projectLinks.forEach(a => {
                const href = a.getAttribute('href') || '';
                if (!href || href === '/fx/tools/flow' || href === '/fx/tools/flow/' || href === '/project' || href === '/project/') return;

                // Never hide if the link/button is "New Project" or "Create"
                const linkTxt = (a.innerText || a.textContent || a.getAttribute('aria-label') || a.title || '').trim();
                if (NEWP_RE.test(linkTxt)) return;

                const cleanHref = href.replace(/\/$/, '');
                const m = cleanHref.match(/\/(?:fx\/tools\/flow\/)?project\/([a-zA-Z0-9_-]{4,})/i);
                if (!m) return;
                const pId = m[1].toLowerCase();
                if (pId === 'create' || pId === 'new') return;

                // Check if this project is owned by this user
                const isMine = myProjects.some(p => {
                    if (!p) return false;
                    const cp = p.replace(/\/$/, '').toLowerCase();
                    return cleanHref.toLowerCase() === cp ||
                           cleanHref.toLowerCase().endsWith('/' + cp) ||
                           pId === cp;
                });

                if (isMine) {
                    // Make sure user's own project is visible
                    a.style.display = '';
                    a.style.visibility = '';
                    a.style.opacity = '';
                    let up = a;
                    for (let i = 0; i < 4; i++) {
                        if (!up.parentElement || up.parentElement === document.body) break;
                        up = up.parentElement;
                        up.style.display = '';
                        up.style.visibility = '';
                        up.style.opacity = '';
                    }
                    return;
                }

                // Locate the specific card element (up to 4 levels max, never climbing into layout containers)
                let card = a;
                for (let i = 0; i < 4; i++) {
                    const parent = card.parentElement;
                    if (!parent || parent === document.body || parent === document.documentElement) break;
                    const parentTag = (parent.tagName || '').toUpperCase();
                    if (['MAIN', 'HEADER', 'NAV', 'SECTION'].includes(parentTag)) break;

                    // Safety: if parent contains the "New project" button or text, stop so we never hide the container!
                    const parentTxt = parent.innerText || parent.textContent || '';
                    if (NEWP_RE.test(parentTxt)) break;

                    // If parent has multiple items and is the grid/list container, card is the direct child
                    if (parent.children.length > 1) {
                        const pCls = (typeof parent.className === 'string' ? parent.className : '').toLowerCase();
                        const pRole = (parent.getAttribute('role') || '').toLowerCase();
                        if (pRole === 'grid' || pRole === 'list' || pCls.includes('grid') || pCls.includes('list')) {
                            break;
                        }
                    }

                    const cardTag = (card.tagName || '').toUpperCase();
                    if (['LI', 'ARTICLE'].includes(cardTag) || card.getAttribute('role') === 'gridcell' || card.getAttribute('role') === 'listitem') {
                        break;
                    }

                    card = parent;
                }

                // Final safety: never hide if card contains "New Project" button/text
                const cardTxt = (card.innerText || card.textContent || '').trim();
                if (!NEWP_RE.test(cardTxt)) {
                    card.style.setProperty('display', 'none', 'important');
                    card.style.setProperty('visibility', 'hidden', 'important');
                    card.style.setProperty('opacity', '0', 'important');
                }
            });
        };

        let lastPathname = window.location.pathname;
        const trackNewProjects = () => {
            const currentPath = window.location.pathname;
            const m = currentPath.match(/\/(?:fx\/tools\/flow\/)?project\/([a-zA-Z0-9_-]{4,})/i);
            if (m && m[1]) {
                const pId = m[1].toLowerCase();
                if (pId !== 'new' && pId !== 'create') {
                    const cleanPath = currentPath.replace(/\/$/, '');
                    if (!myProjects.includes(cleanPath) && !myProjects.includes(m[1])) {
                        myProjects.push(cleanPath);
                        myProjects.push(m[1]);
                        try {
                            chrome.storage.local.set({ '__wemate_my_projects': myProjects });
                        } catch(e) {}
                    }
                }
            }
            lastPathname = currentPath;
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
