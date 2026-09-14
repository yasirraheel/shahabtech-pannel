# WeMate (ShahabTech Panel & Extension) — Architecture & Change Log

## 1. Project Overview & Environments
* **Project Name**: WeMate / ShahabTech Panel
* **Local Path**: `D:\Git Work\Web\wemate`
* **Production URL**: `https://panel.shahabtech.com`
* **Remote Server**:
  * **Host / IP**: `82.197.80.201` (Port: `65002`)
  * **User**: `u559276167`
  * **Web Root**: `/home/u559276167/domains/shahabtech.com/public_html/panel`
  * **SSH Command**: `ssh -p 65002 u559276167@82.197.80.201`
* **Git Repository**: `https://github.com/yasirraheel/shahabtech-pannel.git` (Branch: `main`)
* **Extension Source Folder**: `D:\Git Work\Web\wemate\wemate-ext`
* **Extension Download Location on Server**: `core/storage/app/public/extension/wemate-ext-v{version}.zip` (symlinked or served via `getExtensionDownloadUrl()`).

---

## 2. Full Summary of Recent Issues & Fixes

### A. Extension Cookie Injection Engine Upgrade (Saqib-Grade Parity)
* **Problem**: Cookie injection into Google Flow and other platforms was failing due to rigid SameSite attributes, strict domain locks, and premature session clearance.
* **Solution**:
  1. Implemented the **Saqib-grade 4-tier retry engine** in `wemate-ext/background.js`:
     * Attempt 0: Direct injection with original attributes.
     * Attempt 1: Injects with domain stripped (tied strictly to `targetUrl`).
     * Attempt 2: Falls back to `sameSite: 'lax'`.
     * Attempt 3: Falls back to `sameSite: 'unspecified'`.
  2. Cross-domain cookie mirroring: Automatically replicates cookies across `flow.google.com`, `.google.com`, and `labs.google`.
  3. Pre-cleans conflicting Google auth cookies (`clearGoogleAuthCookies`) before injecting new accounts to prevent Google Account mismatch errors.
  4. Removed broken hardcoded path lock redirects in `protector.js` that caused infinite loops on `flow.google.com`.

---

### B. Auto-Flattening Extension Zip Uploads
* **Problem**: When uploading extension zip files through the Admin Panel (`Extension Distribution & Versioning`), user extractions were creating a folder-inside-folder structure (e.g. `wemate-ext/wemate-ext/manifest.json`), preventing Chrome from loading it without manual reorganization.
* **Solution in `core/app/Http/Controllers/Admin/ExtensionUploadController.php`**:
  * Added `flattenExtensionZip($zipPath)`: Automatically inspects any uploaded `.zip` file. If `manifest.json` is located in a nested directory, it extracts all files and repacks them into a single root directory where `manifest.json` is at the archive root.
  * Preserved original versioned filenames (e.g., `wemate-ext-v2.2.0.zip`).
  * Cleans up stale previous `.zip` files from `core/storage/app/public/extension/`.

---

### C. Cookie Validator & "Check Cookie" False Expiry Fix
* **Problem**:
  1. In `core/app/Http/Controllers/CronController.php` (`verifyAccountCookieHealth`), checking Google Flow cookies against `https://labs.google/fx/api/auth/session` was checking `strtotime($json['expires']) < time()`. NextAuth's `expires` field is a 1-hour rolling access token window (refreshed dynamically by client scripts) and does **not** indicate cookie death. Valid sessions were being falsely flagged as `Google Session Expired`.
  2. Accounts exported directly from `flow.google.com` (using standard Google Account cookies `SID`, `SSID`, `HSID`, `__Secure-1PSID`, etc.) do not have NextAuth cookies, causing the NextAuth API endpoint to return `{}` and fail verification.
* **Solution**:
  * Implemented **Dual-Tier Google Flow Validation** in `CronController.php`:
    * **Tier 1 (Labs NextAuth Session API)**: Checks `https://labs.google/fx/api/auth/session`. If `$json['user']` exists, the account is validated and user name/email is extracted. The rolling `expires` check was removed.
    * **Tier 2 (Flow Portal Validation)**: Directly tests `https://flow.google.com/`. Verifies that Google does not redirect to `accounts.google.com/ServiceLogin`, extracts the authenticated user email from the embedded page config (`"oPEP7c"`), and validates Google auth session cookies (`SID`, `SSID`, `HSID`, `__Secure-1PSID`, `__Secure-3PSID`).

---

### D. Google Flow Projects & Thumbnails Privacy Filter (FlowByDcx Parity)
* **Problem**: When opening `https://flow.google.com/`, previous user projects and generated thumbnails were visible on the home page feed, compromising privacy on shared accounts.
* **Solution in `wemate-ext/protector.js`**:
  * Target both `flow.google.com` (root `/` and `/home`) and `labs.google/fx/tools/flow`.
  * Injected zero-flash CSS (`[data-wm-hide]` and `[data-wm-ban]` with `display: none !important; visibility: hidden !important; opacity: 0 !important;`).
  * Implemented `findCardParent(el)` to walk up the DOM and target the full card element (`<article>`, `<li>`, `role="gridcell"`, or grid item).
  * Filtered out:
    1. All `a[href*="/project/"]` links and cards (except "+ New project" or the user's own projects).
    2. Any card or listitem matching date patterns (`DATE_RE` like `Sep 14 - 08:49`, time stamps, etc.).
    3. Any card container holding an image/video thumbnail combined with a date stamp.
    4. Entire empty grid containers whose children have all been hidden to prevent blank spaces.
  * **User Project Isolation**: When a user creates/navigates to their own project (`/project/{id}`), it is stored in `__wemate_my_projects` in Chrome storage, ensuring **their own** projects remain visible to them.

---

### F. Cookie Injection Fix & Tab Creation Race Condition Elimination (v2.3.0)
* **Problem**: 
  1. When clicking "Visit Platform" from the user dashboard, users were not getting logged into Google Flow.
  2. Root cause: `background.js` had an unconditional `chrome.tabs.onCreated` listener. When `chrome.tabs.create({ url: 'https://flow.google.com/' })` opened the tab, the `onCreated` listener immediately fired, triggering `autoInjectCookies` -> `clearGoogleAuthCookies()`. This wiped the Google authentication cookies (`SID`, `SSID`, `HSID`, `__Secure-1PSID`, `OSID`, etc.) out of the browser at the exact millisecond the newly opened tab was initiating its HTTP handshake with Google Flow, causing Google to reject the request and redirect to `ServiceLogin`.
  3. Google cookies with domain `.google.com` were not being mirrored across `https://flow.google.com/`, `https://labs.google/`, and `https://accounts.google.com/`.
  4. Cookies with domain `flow.google.com` (such as `OSID` and `__Secure-OSID`) were being stripped of their domain and converted into host-only cookies.
* **Solution**:
  1. **Removed `chrome.tabs.onCreated` listener**: Tab creation never wipes or re-injects cookies.
  2. **Implemented 30-Second Tab Debounce**: `chrome.tabs.onUpdated` only triggers when `status === 'loading'`, enforces a 30-second debounce per tab, and never clears existing auth cookies during background updates (`shouldClearAuth = false`).
  3. **Comprehensive Google Cross-Domain Mirroring**: Replicated exact FlowByDcx mirroring logic where all `.google.com` auth cookies are mirrored directly to `https://flow.google.com/`, `https://labs.google/`, and `https://accounts.google.com/`.
  4. **Domain Normalization**: Cookies for `flow.google.com` are normalized to `.flow.google.com` so they are fully valid across all sub-paths.
  5. **Immediate Meta Tag Injection**: Updated `content.js` to run at `document_start` across all portal domains so `shahabtech-extension-installed` is always present before user clicks.
  6. **Packaged & Deployed**: Packaged flat `wemate-ext-v2.3.0.zip` and updated `min_extension_version` in DB to `2.3.0`.

### G. Admin Panel Cookie Auto-Sanitizer (Commit `2329818`)
* **Problem**: When admins export cookies from browser sessions, the JSON export frequently contains 100+ cookies from unrelated Google properties (`docs.google.com`, `drive.google.com`, `mail.google.com`, `adsense`, `youtube`, `play`, etc.) and tracking tokens (`_ga`, `_gid`, `NID`, `OGP`). Injecting this massive payload overwhelmed Chrome's cookie jar, degraded performance, and led to session conflicts.
* **Solution in `core/app/Http/Controllers/Admin/AccountListingController.php`**:
  * Implemented `sanitizeAccountCookies()`: Automatically runs on any cookie payload pasted in the Admin Panel when adding or editing accounts.
  * For Google Flow:
    1. Extracts only cookies belonging to `.google.com` or `flow.google.com`.
    2. Completely strips cookies belonging to 60+ unrelated subdomains (`docs`, `sheets`, `drive`, `mail`, `play`, `youtube`, `ads`, `analytics`, etc.).
    3. Strips advertising and telemetry cookies (`_ga`, `_gid`, `_gat`, `__utm`, `NID`, `ANID`, `IDE`, `DSID`).
    4. Deduplicates cookies by name/domain/path and formats the output into clean, optimized JSON.

---

### H. Safe Project Hiding & Elimination of Black Screen on Pro Accounts (v2.3.1)
* **Problem**:
  1. On Google Flow Pro accounts (e.g. `aasikhan`), users reported that the home page would render for a split second (showing the banner and "+ New project" card), and then immediately turn into a completely pitch-black screen with all controls disappearing.
  2. Meanwhile, Ultra accounts (with many existing projects) did not show the black screen.
  3. **Root Cause**:
     * In `wemate-ext/protector.js`, the previous `hideOtherProjects()` implementation used an over-aggressive DOM crawler `findCardParent(el, depth=8)` which looked for `parentElement.children.length > 6`.
     * On high-traffic Ultra accounts with >6 project cards, `children.length > 6` stopped the crawler at the card level.
     * On fresh Pro accounts with 0 or few projects, `children.length > 6` was **never met**.
     * `findCardParent` crawled up 8 levels of ancestors until reaching `document.body`'s direct child, marking the entire application root `<main>` / `div#__next` with `data-wm-hide="1"`.
     * Combined with `[data-wm-hide] { display: none !important; }`, the entire viewport was hidden into a black screen.
     * Additionally, arbitrary date matching (`DATE_RE`) on all `div` and `section` elements and container cascade rules (`kids.every(...)`) caused recursive hiding of the grid.
* **Solution in `wemate-ext/protector.js`**:
  1. **Removed all broad rules**: Eliminated `findCardParent(el, 8)`, `DATE_RE` regex, `BNNER_RE`, container hiding cascades, and global `[data-wm-hide]` CSS.
  2. **Targeted Card Selection**: Only inspects `a[href*="/project/"]` and `a[href^="/fx/tools/flow/"]` with valid project slugs (`m[1].length >= 4`). Creation links (`/project/create`, `/project/new`) are strictly excluded.
  3. **Comprehensive New Project Shield**: `NEWP_RE` checks `innerText`, `textContent`, `aria-label`, and `title` for "New project", "Create", "Start Creating", "+", etc. If an element or any ancestor contains the "+ New project" button, it is **strictly immune** and can never be hidden.
  4. **Strict Boundary Limiter**: The card parent finder walks a maximum of 4 levels and stops immediately if it encounters `MAIN`, `HEADER`, `NAV`, `SECTION`, `BODY`, or any element containing `NEWP_RE`.
  5. **Card-Level Inline Hiding**: Uses `card.style.setProperty('display', 'none', 'important')` strictly on the identified project card.
  6. **Packaged & Deployed**: Packaged flat `wemate-ext-v2.3.1.zip`, uploaded to server, and updated `min_extension_version` to `2.3.1`.

---

## 3. Key Files & Responsibilities

| Component | Path | Description |
|---|---|---|
| **Cron & Cookie Verification** | `core/app/Http/Controllers/CronController.php` | Live HTTP cookie validation (`verifyAccountCookieHealth`), WhatsApp expiry alerts, user load balancing. |
| **Admin Account Management** | `core/app/Http/Controllers/Admin/AccountListingController.php` | Account CRUD, manual "Check Cookie", cookie auto-sanitizer (`sanitizeAccountCookies`), expiry extend/decrease (+30 / -30 days). |
| **Extension Upload & Zip Flattening** | `core/app/Http/Controllers/Admin/ExtensionUploadController.php` | Handles zip upload, auto-flattening nested directories, updating `min_extension_version`. |
| **Extension Manifest** | `wemate-ext/manifest.json` | Manifest V3 configuration (currently v2.3.1). |
| **Extension Background Service Worker** | `wemate-ext/background.js` | Cookie injection engine, multi-tier fallback, subscription status watchdog. |
| **Extension Content Protector** | `wemate-ext/protector.js` | Prevents logout, blocks cookie-editor extensions, isolates ChatGPT chat history, safe Flow project hiding. |
| **Extension Main World Hijack** | `wemate-ext/hijack.js` | Runs in `MAIN` world to protect storage and environment. |

---

## 4. Useful Management Commands

### Deploy Local Code to Remote Server:
```powershell
# In D:\Git Work\Web\wemate
git add .
git commit -m "Your commit message"
git push origin main

# Deploy on Server via SSH
ssh -p 65002 u559276167@82.197.80.201 "cd ~/domains/shahabtech.com/public_html/panel && git fetch origin && git reset --hard origin/main && cd core && php artisan optimize:clear"
```

### Pack and Upload New Extension Version:
1. Bump version in `wemate-ext/manifest.json`.
2. Run zip pack script:
   ```powershell
   python "C:\Users\Team Hifsa\.gemini\antigravity\brain\6f636936-04bb-4827-95b4-5b4577ef5851\scratch\pack_wemate_ext.py"
   ```
3. Upload to server via `scp`:
   ```powershell
   scp -P 65002 "D:\Git Work\Web\wemate\wemate-ext-v{version}.zip" u559276167@82.197.80.201:~/domains/shahabtech.com/public_html/panel/core/storage/app/public/extension/
   ```
4. Update `min_extension_version` in DB:
   ```bash
   ssh -p 65002 u559276167@82.197.80.201 "cd ~/domains/shahabtech.com/public_html/panel/core && php -r \"\$g = gs(); \$g->min_extension_version = '{version}'; \$g->save();\""
   ```
