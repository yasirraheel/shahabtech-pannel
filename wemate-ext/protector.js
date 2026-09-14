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
        } catch(e) {}

        const DATE_RE = /\b(jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec)\b.{0,5}\d{1,2}|\d{1,2}:\d{2}\s*(am|pm)|tháng|\d{4}-\d{2}-\d{2}/i;
        const NEWP_RE = /new\s*project|dự án mới|\+\s*d|create new|\+\s*new/i;
        const BNNER_RE = /nano banana|is here!|new model|veo\s+\d|imagen/i;

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

        function findCardParent(el, depth) {
            depth = depth || 8;
            let cur = el;
            for (let i = 0; i < depth; i++) {
                const p = cur.parentElement;
                if (!p || p === document.body || p === document.documentElement) break;
                if (p.children.length > 6) return cur;
                if (['ARTICLE', 'LI'].includes(p.tagName) || p.getAttribute('role') === 'gridcell' || p.getAttribute('role') === 'listitem') return p;
                cur = p;
            }
            return cur;
        }

        // Apply instant global CSS rules
        if (!document.getElementById('__wemate_hide_projects_css__')) {
            const style = document.createElement('style');
            style.id = '__wemate_hide_projects_css__';
            style.textContent = `
                [data-wm-hide] { display: none !important; visibility: hidden !important; opacity: 0 !important; }
                [data-wm-ban]  { display: none !important; visibility: hidden !important; opacity: 0 !important; }
            `;
            (document.head || document.documentElement).appendChild(style);
        }

        const hideOtherProjects = () => {
            if (!isFlowHomePage()) return;

            // 1. Hide all project card links that aren't the user's own project or "New Project"
            document.querySelectorAll('a[href*="/project/"]').forEach(a => {
                const href = a.getAttribute('href') || '';
                const txt = (a.textContent || '').trim();
                if (NEWP_RE.test(txt)) return;

                // Check if this project is owned by this user
                const cleanHref = href.replace(/\/$/, '');
                const m = cleanHref.match(/\/project\/([a-zA-Z0-9_-]{4,})/i);
                const pId = m ? m[1] : '';

                const isMine = myProjects.some(p => p.includes(cleanHref) || (pId && p.includes(pId)));
                if (isMine) return;

                const card = findCardParent(a);
                card.dataset.wmHide = '1';
                a.dataset.wmHide = '1';
            });

            // 2. Hide list/article/gridcell items containing date stamps (Flow project cards)
            document.querySelectorAll('li, article, [role="gridcell"], [role="listitem"]').forEach(el => {
                const txt = (el.textContent || '').trim();
                if (txt.length > 350 || !DATE_RE.test(txt) || NEWP_RE.test(txt)) return;
                
                // Do not hide if it contains a link to our own project
                const links = el.querySelectorAll('a[href*="/project/"]');
                let hasMine = false;
                links.forEach(l => {
                    const h = l.getAttribute('href') || '';
                    if (myProjects.some(p => p && h && (p.includes(h) || h.includes(p)))) hasMine = true;
                });
                if (!hasMine) {
                    el.dataset.wmHide = '1';
                }
            });

            // 3. Hide any div/section with an image/video AND a date (Flow project thumbnail cards)
            document.querySelectorAll('div, section').forEach(el => {
                const txt = (el.textContent || '').trim();
                if (txt.length > 280 || txt.length < 3 || !DATE_RE.test(txt) || NEWP_RE.test(txt)) return;
                if (!el.querySelector('img, video, [role="img"]')) return;
                if (el.querySelectorAll('[data-wm-hide]').length > 0) return;

                const links = el.querySelectorAll('a[href*="/project/"]');
                let hasMine = false;
                links.forEach(l => {
                    const h = l.getAttribute('href') || '';
                    if (myProjects.some(p => p && h && (p.includes(h) || h.includes(p)))) hasMine = true;
                });
                if (!hasMine) {
                    findCardParent(el).dataset.wmHide = '1';
                }
            });

            // 4. Hide banners or promo sections
            document.querySelectorAll('div, section').forEach(el => {
                const txt = (el.textContent || '').trim();
                if (txt.length > 500 || txt.length < 5 || !BNNER_RE.test(txt) || NEWP_RE.test(txt)) return;
                el.dataset.wmBan = '1';
            });

            // 5. Hide containers whose visible children are all hidden project cards (prevent blank spaces)
            document.querySelectorAll('ul, ol, div[class*="grid"], div[class*="list"]').forEach(el => {
                const kids = Array.from(el.children);
                if (kids.length < 2) return;
                const allHidden = kids.every(k => k.dataset.wmHide === '1' || k.dataset.wmBan === '1');
                if (allHidden) {
                    el.dataset.wmHide = '1';
                }
            });
        };

        let lastPathname = window.location.pathname;
        const trackNewProjects = () => {
            const currentPath = window.location.pathname;
            const m = currentPath.match(/\/project\/([a-zA-Z0-9_-]{6,})/i);
            if (m && m[1]) {
                const pId = m[1];
                const cleanPath = currentPath.replace(/\/$/, '');
                if (!myProjects.includes(cleanPath) && !myProjects.includes(pId)) {
                    myProjects.push(cleanPath);
                    myProjects.push(pId);
                    try {
                        chrome.storage.local.set({ '__wemate_my_projects': myProjects });
                    } catch(e) {}
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
