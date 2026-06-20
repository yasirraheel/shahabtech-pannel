// content.js
// Injected into panel.shahabtech.com to listen for injection requests from the web page

window.addEventListener('ShahabTechInject', (event) => {
    const data = event.detail;
    
    if (data && data.platform && data.cookies) {
        // Send to background script for secure injection
        try {
            chrome.runtime.sendMessage({
                type: 'INJECT_COOKIES',
                platform: data.platform,
                cookies: data.cookies
            }, (response) => {
                if (chrome.runtime.lastError) {
                    console.warn('ShahabTech Access: Extension context invalidated. Please refresh the page.');
                    return;
                }
                if (response && response.success) {
                    console.log('ShahabTech Access: Cookies injected and tab opened.');
                    // We can notify the page back if we want
                    window.dispatchEvent(new CustomEvent('ShahabTechInjectSuccess'));
                } else {
                    console.error('ShahabTech Access: Failed to inject cookies', response?.error);
                    window.dispatchEvent(new CustomEvent('ShahabTechInjectError', { detail: response?.error }));
                }
            });
        } catch (e) {
            console.warn('ShahabTech Access: Extension connection failed. Please refresh the page.');
        }
    }
});

// Also let the web page know the extension is installed
const meta = document.createElement('meta');
meta.name = 'shahabtech-extension-installed';
meta.content = 'true';
document.head.appendChild(meta);
