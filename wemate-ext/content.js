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

// Also let the web page know the extension is installed and its exact version
const extVersion = (typeof chrome !== 'undefined' && chrome.runtime && chrome.runtime.getManifest) ? chrome.runtime.getManifest().version : '2.3.0';

function injectExtensionMetaTags() {
    const target = document.head || document.documentElement;
    if (!target) return;

    if (!document.querySelector('meta[name="extension-version"]')) {
        const metaVersion = document.createElement('meta');
        metaVersion.name = 'extension-version';
        metaVersion.content = extVersion;
        target.appendChild(metaVersion);
    }

    if (!document.querySelector('meta[name="wemate-extension-version"]')) {
        const metaVersion2 = document.createElement('meta');
        metaVersion2.name = 'wemate-extension-version';
        metaVersion2.content = extVersion;
        target.appendChild(metaVersion2);
    }

    if (!document.querySelector('meta[name="shahabtech-extension-installed"]')) {
        const meta = document.createElement('meta');
        meta.name = 'shahabtech-extension-installed';
        meta.content = 'true';
        target.appendChild(meta);
    }

    if (!document.querySelector('meta[name="wemate-extension-installed"]')) {
        const meta2 = document.createElement('meta');
        meta2.name = 'wemate-extension-installed';
        meta2.content = 'true';
        target.appendChild(meta2);
    }
}

injectExtensionMetaTags();
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', injectExtensionMetaTags);
}
