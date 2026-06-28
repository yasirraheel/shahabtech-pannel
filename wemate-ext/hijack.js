// hijack.js
// Runs in the MAIN world to intercept document.cookie and cookieStore
// This prevents users from opening the DevTools Console and typing document.cookie to steal the session.

(function() {
    var originalCookieDesc = Object.getOwnPropertyDescriptor(Document.prototype, 'cookie') ||
                             Object.getOwnPropertyDescriptor(HTMLDocument.prototype, 'cookie');

    if (!originalCookieDesc || !originalCookieDesc.get) return;

    function isUnauthorized() {
        try {
            var stack = new Error().stack || '';
            
            // If called from DevTools console, it often has "<anonymous>" and lacks a .js file reference
            if (stack.includes('<anonymous>') && !stack.includes('.js')) {
                return true;
            }
            
            // Block any chrome-extension scripts that try to read via MAIN world
            if (stack.includes('chrome-extension://')) {
                // We can't easily check our own ID here dynamically, but our own extension
                // uses chrome.cookies API from the background, not document.cookie from MAIN.
                return true;
            }
        } catch(e) {}
        
        return false;
    }

    try {
        Object.defineProperty(document, 'cookie', {
            get: function() {
                if (isUnauthorized()) return '';
                return originalCookieDesc.get.call(document);
            },
            set: function(val) {
                if (isUnauthorized()) return;
                originalCookieDesc.set.call(document, val);
            },
            configurable: true
        });
    } catch(e) {}

    // Hijack the modern async cookieStore API
    try {
        if (window.cookieStore) {
            var originalGetAll = window.cookieStore.getAll;
            var originalGet = window.cookieStore.get;
            var originalSet = window.cookieStore.set;
            var originalDelete = window.cookieStore.delete;
            
            window.cookieStore.getAll = function() {
                if (isUnauthorized()) return Promise.resolve([]);
                return originalGetAll.apply(this, arguments);
            };
            window.cookieStore.get = function() {
                if (isUnauthorized()) return Promise.resolve(null);
                return originalGet.apply(this, arguments);
            };
            window.cookieStore.set = function() {
                if (isUnauthorized()) return Promise.resolve();
                return originalSet.apply(this, arguments);
            };
            window.cookieStore.delete = function() {
                if (isUnauthorized()) return Promise.resolve();
                return originalDelete.apply(this, arguments);
            };
        }
    } catch(e) {}
})();
