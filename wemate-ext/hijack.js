// hijack.js
// Runs in the MAIN world to protect session cookies safely without V8 stack overhead

(function() {
    var originalCookieDesc = Object.getOwnPropertyDescriptor(Document.prototype, 'cookie') ||
                             Object.getOwnPropertyDescriptor(HTMLDocument.prototype, 'cookie');

    if (!originalCookieDesc || !originalCookieDesc.get) return;

    try {
        Object.defineProperty(document, 'cookie', {
            get: function() {
                return originalCookieDesc.get.call(document);
            },
            set: function(val) {
                originalCookieDesc.set.call(document, val);
            },
            configurable: true
        });
    } catch(e) {}
})();
