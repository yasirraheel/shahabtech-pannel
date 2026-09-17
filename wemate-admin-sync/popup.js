document.addEventListener('DOMContentLoaded', async () => {
    const inputPanelUrl = document.getElementById('inputPanelUrl');
    const inputAdminKey = document.getElementById('inputAdminKey');
    const selectAccount = document.getElementById('selectAccount');
    const chkAutoSync = document.getElementById('chkAutoSync');
    const btnSaveConfig = document.getElementById('btnSaveConfig');
    const btnRefreshAccounts = document.getElementById('btnRefreshAccounts');
    const btnSyncNow = document.getElementById('btnSyncNow');
    const badgeStatus = document.getElementById('badgeStatus');
    const statusSummary = document.getElementById('statusSummary');
    const alertBox = document.getElementById('alertBox');
    const historyList = document.getElementById('historyList');

    function showAlert(msg, isError = false) {
        alertBox.textContent = msg;
        alertBox.className = 'alert ' + (isError ? 'alert-danger' : 'alert-success');
        alertBox.style.display = 'block';
        setTimeout(() => { alertBox.style.display = 'none'; }, 4000);
    }

    function timeAgo(date) {
        if (!date) return 'Never';
        const seconds = Math.floor((new Date() - new Date(date)) / 1000);
        if (seconds < 10) return 'Just now';
        if (seconds < 60) return `${seconds}s ago`;
        const minutes = Math.floor(seconds / 60);
        if (minutes < 60) return `${minutes}m ago`;
        const hours = Math.floor(minutes / 60);
        if (hours < 24) return `${hours}h ago`;
        return `${Math.floor(hours / 24)}d ago`;
    }

    // ─── LOAD STORAGE & INITIALIZE ───────────────────────────────────────────
    const stored = await chrome.storage.local.get([
        'panelUrl', 'adminKey', 'accountId', 'autoSyncEnabled', 'lastSyncStatus', 'syncHistory'
    ]);

    if (stored.panelUrl) inputPanelUrl.value = stored.panelUrl;
    if (stored.adminKey) inputAdminKey.value = stored.adminKey;
    if (stored.autoSyncEnabled != null) chkAutoSync.checked = stored.autoSyncEnabled;

    updateStatusUI(stored.lastSyncStatus);
    renderHistory(stored.syncHistory || []);

    if (stored.adminKey) {
        await loadAccounts(stored.accountId);
    }

    // ─── REFRESH ACCOUNTS ────────────────────────────────────────────────────
    async function loadAccounts(selectedId = null) {
        selectAccount.innerHTML = '<option value="">Loading accounts from panel...</option>';
        selectAccount.disabled = true;

        try {
            chrome.runtime.sendMessage({ type: 'FETCH_ACCOUNTS' }, (response) => {
                selectAccount.disabled = false;
                selectAccount.innerHTML = '';

                if (!response || !response.success || !Array.isArray(response.accounts)) {
                    const msg = response ? response.message : 'Could not reach panel';
                    selectAccount.innerHTML = `<option value="">-- Error: ${msg} --</option>`;
                    return;
                }

                if (response.accounts.length === 0) {
                    selectAccount.innerHTML = '<option value="">-- No Active Accounts Found --</option>';
                    return;
                }

                selectAccount.innerHTML = '<option value="">-- Select Account to Sync --</option>';
                response.accounts.forEach(acc => {
                    const opt = document.createElement('option');
                    opt.value = acc.id;
                    const syncInfo = acc.last_synced_text ? `(Synced: ${acc.last_synced_text})` : '(Never synced)';
                    opt.textContent = `#${acc.id}: ${acc.title} [${acc.platform}] ${syncInfo}`;
                    if (selectedId && String(acc.id) === String(selectedId)) {
                        opt.selected = true;
                    }
                    selectAccount.appendChild(opt);
                });
            });
        } catch (err) {
            selectAccount.disabled = false;
            selectAccount.innerHTML = `<option value="">-- Error: ${err.message} --</option>`;
        }
    }

    // ─── SAVE CONFIGURATION ──────────────────────────────────────────────────
    btnSaveConfig.addEventListener('click', async () => {
        const panelUrl = inputPanelUrl.value.trim().replace(/\/+$/, '');
        const adminKey = inputAdminKey.value.trim();
        const accountId = selectAccount.value;
        const autoSyncEnabled = chkAutoSync.checked;

        if (!adminKey) {
            showAlert('Please enter your Admin Sync Key.', true);
            return;
        }

        btnSaveConfig.disabled = true;
        btnSaveConfig.textContent = 'Saving...';

        await chrome.storage.local.set({
            panelUrl,
            adminKey,
            accountId,
            autoSyncEnabled
        });

        showAlert('Settings saved successfully!');
        btnSaveConfig.disabled = false;
        btnSaveConfig.textContent = 'Save Settings';

        await loadAccounts(accountId);
    });

    btnRefreshAccounts.addEventListener('click', (e) => {
        e.preventDefault();
        loadAccounts(selectAccount.value);
    });

    // ─── MANUAL SYNC BUTTON ──────────────────────────────────────────────────
    btnSyncNow.addEventListener('click', () => {
        const originalText = btnSyncNow.innerHTML;
        btnSyncNow.disabled = true;
        btnSyncNow.innerHTML = '<span>⏳</span> Syncing to Panel...';

        chrome.runtime.sendMessage({ type: 'MANUAL_SYNC' }, (response) => {
            btnSyncNow.disabled = false;
            btnSyncNow.innerHTML = originalText;

            if (response && response.success) {
                const count = response.data ? response.data.cookie_count : 'all';
                showAlert(`Successfully synced ${count} fresh cookies to panel!`);
                updateStatusUI({
                    success: true,
                    timestamp: Date.now(),
                    cookieCount: count,
                    accountId: response.data ? response.data.account_id : selectAccount.value,
                    accountTitle: response.data ? response.data.title : 'Selected Account'
                });
                chrome.storage.local.get(['syncHistory'], (res) => {
                    renderHistory(res.syncHistory || []);
                });
            } else {
                const err = response ? response.message : 'Sync failed. Check connection.';
                showAlert(err, true);
                updateStatusUI({
                    success: false,
                    timestamp: Date.now(),
                    error: err
                });
            }
        });
    });

    // ─── UI UPDATERS ─────────────────────────────────────────────────────────
    function updateStatusUI(status) {
        if (!status) {
            badgeStatus.textContent = 'UNCONFIGURED';
            badgeStatus.className = 'badge badge-wait';
            statusSummary.innerHTML = 'Enter Admin Key & select account to start syncing.';
            return;
        }

        if (status.success) {
            badgeStatus.textContent = 'LIVE & SYNCED';
            badgeStatus.className = 'badge badge-live';
            const timeStr = timeAgo(status.timestamp);
            statusSummary.innerHTML = `
                <div><span class="status-highlight">${status.cookieCount || 0} cookies</span> synced <span class="status-highlight">${timeStr}</span></div>
                <div style="font-size: 10px; color: #64748b; margin-top: 2px;">Target: ${status.accountTitle || 'Google Flow'} (Account #${status.accountId || ''})</div>
            `;
        } else {
            badgeStatus.textContent = 'SYNC ERROR';
            badgeStatus.className = 'badge badge-err';
            statusSummary.innerHTML = `<span style="color:#f87171;">Error: ${status.error || 'Failed'}</span>`;
        }
    }

    function renderHistory(history) {
        if (!history || !history.length) {
            historyList.innerHTML = '<div style="text-align: center; color: #64748b; padding: 6px 0;">No sync activity yet.</div>';
            return;
        }

        historyList.innerHTML = '';
        history.slice(0, 10).forEach(h => {
            const row = document.createElement('div');
            row.className = 'history-item';
            const timeStr = timeAgo(h.timestamp);
            const statusStr = h.success 
                ? `<span style="color: #4ade80;">✔ ${h.cookieCount || 0} cookies</span>` 
                : `<span style="color: #f87171; cursor: help;" title="${(h.error || 'Failed').replace(/"/g, '&quot;')}">✖ Failed</span>`;
            row.innerHTML = `
                <span>${timeStr} (${h.trigger || 'auto'})</span>
                <span>${statusStr}</span>
            `;
            historyList.appendChild(row);
        });
    }
});
