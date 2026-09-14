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

## 3. Key Files & Structure

| Component | Path | Description |
|---|---|---|
| **Cron & Cookie Verification** | `core/app/Http/Controllers/CronController.php` | Live HTTP cookie validation (`verifyAccountCookieHealth`), WhatsApp expiry alerts, user load balancing. |
| **Admin Account Management** | `core/app/Http/Controllers/Admin/AccountListingController.php` | Account CRUD, manual "Check Cookie", expiry extend/decrease (+30 / -30 days), duplicate name prevention. |
| **Extension Upload & Zip Flattening** | `core/app/Http/Controllers/Admin/ExtensionUploadController.php` | Handles zip upload, auto-flattening nested directories, updating `min_extension_version`. |
| **Extension Manifest** | `wemate-ext/manifest.json` | Manifest V3 configuration (currently v2.2.0). |
| **Extension Background Service Worker** | `wemate-ext/background.js` | Cookie injection engine, multi-tier fallback, subscription status watchdog. |
| **Extension Content Protector** | `wemate-ext/protector.js` | Prevents logout, blocks cookie-editor extensions, isolates ChatGPT chat history, hides Flow projects & home thumbnails. |
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
