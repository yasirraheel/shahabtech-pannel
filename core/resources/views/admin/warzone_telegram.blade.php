@extends('admin.layouts.app')

@section('panel')
<div class="row g-0 justify-content-center">
    <div class="col-12">
        <div class="tg-desktop-app shadow-lg">
            <div class="row g-0 h-100">
                <!-- Left Control / Navigation Pane -->
                <div class="col-lg-4 col-md-5 tg-sidebar-pane d-flex flex-column border-end border-dark">
                    <!-- Bot Info & Balance Card -->
                    <div class="tg-sidebar-header p-3 border-bottom border-dark">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="tg-bot-avatar">
                                <img src="https://ui-avatars.com/api/?name=Warzone+Shop&background=2b5278&color=fff&size=52&font-size=0.4" alt="Warzone Shop" class="rounded-circle shadow">
                            </div>
                            <div>
                                <h5 class="mb-0 text-white fw-bold">Warzone Shop</h5>
                                <div class="d-flex align-items-center gap-2 mt-1">
                                    <span class="tg-status-dot"></span>
                                    <span class="text-white-50 small">Bot • 13,071 users</span>
                                </div>
                            </div>
                        </div>

                        <!-- Live Account Pill Box -->
                        <div class="tg-account-card p-3 rounded">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-white-50 small">Wallet Balance</span>
                                <span class="badge bg-success px-2 py-1 fs-6 fw-bold" id="leftBalanceBadge">
                                    ${{ number_format($account['wallet_balance'] ?? 17.7, 2) }}
                                </span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center text-white-50 small mt-2 pt-2 border-top border-secondary">
                                <span>Telegram ID: <code class="tg-code">{{ $account['chat_id'] ?? '6976455363' }}</code></span>
                                <span>👤 {{ $account['first_name'] ?? 'Shahab' }}</span>
                            </div>

                            <!-- Live Auto-Buy Cron Status Widget -->
                            <div class="p-2 rounded mt-2 border border-info d-flex align-items-center justify-content-between" style="background: rgba(14, 165, 233, 0.1);">
                                <div>
                                    <span class="text-white small fw-bold d-block">🤖 Auto-Buy: Gemini Pro 18M</span>
                                    <span class="text-white-50" style="font-size: 10px;">🟢 30s Cron Running &bull; Stock: {{ $autobuyState['last_stock'] ?? 0 }}</span>
                                </div>
                                <button type="button" class="btn btn-xs btn-outline-info tg-action-btn" data-action="autobuy" title="View Auto-Buy Log">
                                    Logs 👁️
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Keyboard Grid Buttons Menu -->
                    <div class="tg-sidebar-menu p-3 flex-grow-1 overflow-y-auto">
                        <div class="small text-uppercase text-white-50 fw-bold mb-2 tracking-wide" style="font-size: 11px;">
                            Quick Commands
                        </div>
                        <div class="d-flex flex-column gap-2">
                            <button type="button" class="btn tg-btn-blue w-100 text-start d-flex align-items-center justify-content-between py-2 px-3 tg-keyboard-btn" data-action="shop">
                                <span class="text-white fw-bold"><i class="las la-shopping-cart fs-5 me-2 text-info"></i> 🛒 Shop Products</span>
                                <i class="las la-angle-right text-info"></i>
                            </button>
                            <button type="button" class="btn tg-btn-blue w-100 text-start d-flex align-items-center justify-content-between py-2 px-3 tg-keyboard-btn border-info" data-action="autobuy">
                                <span class="text-white fw-bold"><i class="las la-robot fs-5 me-2 text-info"></i> 🤖 Auto-Buy Monitor</span>
                                <span class="badge bg-success" style="font-size: 10px;">30s Live</span>
                            </button>
                            <button type="button" class="btn tg-btn-blue w-100 text-start d-flex align-items-center justify-content-between py-2 px-3 tg-keyboard-btn" data-action="wallet">
                                <span class="text-white fw-bold"><i class="las la-wallet fs-5 me-2 text-warning"></i> 💰 Wallet & Balance</span>
                                <i class="las la-angle-right text-info"></i>
                            </button>
                            <button type="button" class="btn tg-btn-blue w-100 text-start d-flex align-items-center justify-content-between py-2 px-3 tg-keyboard-btn" data-action="orders">
                                <span class="text-white fw-bold"><i class="las la-receipt fs-5 me-2 text-success"></i> 📃 Order History</span>
                                <i class="las la-angle-right text-info"></i>
                            </button>
                            <button type="button" class="btn tg-btn-blue w-100 text-start d-flex align-items-center justify-content-between py-2 px-3 tg-keyboard-btn" data-action="orders">
                                <span class="text-white fw-bold"><i class="las la-undo-alt fs-5 me-2 text-primary"></i> 💸 Recover Order</span>
                                <i class="las la-angle-right text-info"></i>
                            </button>
                            <button type="button" class="btn tg-btn-blue w-100 text-start d-flex align-items-center justify-content-between py-2 px-3 tg-keyboard-btn" data-action="profile">
                                <span class="text-white fw-bold"><i class="las la-user-shield fs-5 me-2 text-info"></i> 🛡️ Account Profile</span>
                                <i class="las la-angle-right text-info"></i>
                            </button>
                            <button type="button" class="btn tg-btn-blue w-100 text-start d-flex align-items-center justify-content-between py-2 px-3 tg-keyboard-btn" data-action="api_key">
                                <span class="text-white fw-bold"><i class="las la-key fs-5 me-2 text-warning"></i> 📱 API Key</span>
                                <i class="las la-angle-right text-info"></i>
                            </button>
                            <button type="button" class="btn tg-btn-blue w-100 text-start d-flex align-items-center justify-content-between py-2 px-3 tg-keyboard-btn" data-action="stats">
                                <span class="text-white fw-bold"><i class="las la-chart-bar fs-5 me-2 text-success"></i> 📊 Account Stats</span>
                                <i class="las la-angle-right text-info"></i>
                            </button>
                            <button type="button" class="btn tg-btn-green w-100 text-start d-flex align-items-center justify-content-between py-2 px-3 tg-keyboard-btn" data-action="support">
                                <span class="text-white fw-bold"><i class="las la-headset fs-5 me-2 text-white"></i> 🤫 Warzone Support</span>
                                <i class="las la-angle-right text-white"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Right Chat & Interaction Pane -->
                <div class="col-lg-8 col-md-7 tg-chat-pane d-flex flex-column">
                    <!-- Telegram Chat Header -->
                    <div class="tg-chat-header d-flex align-items-center justify-content-between px-3 py-2 border-bottom border-dark">
                        <div class="d-flex align-items-center gap-2">
                            <div class="d-lg-none d-md-none">
                                <img src="https://ui-avatars.com/api/?name=Warzone+Shop&background=2b5278&color=fff&size=32&font-size=0.4" alt="Warzone Shop" class="rounded-circle">
                            </div>
                            <div>
                                <span class="text-white fw-bold d-block" style="font-size: 14px;">Warzone Shop Conversation</span>
                                <span class="text-white-50 small" style="font-size: 11px;">Real-time API Bot Response Feed</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button class="btn btn-sm btn-outline-warning py-1 px-2 tg-action-btn" data-action="autobuy" title="Open Auto-Buy Monitor">
                                <i class="las la-robot me-1"></i> Auto-Buy
                            </button>
                            <button class="btn btn-sm btn-outline-info py-1 px-3 tg-action-btn" data-action="start">
                                <i class="las la-redo-alt me-1"></i> /start
                            </button>
                        </div>
                    </div>


                    <!-- Chat Scrollable Feed -->
                    <div class="tg-chat-feed p-3 flex-grow-1 overflow-y-auto" id="chatFeed">
                        <!-- Initial Welcome Bot Message -->
                        <div class="tg-msg-row tg-msg-bot-row mb-3">
                            <div class="tg-msg-bot shadow-sm">
                                @include('admin.partials.warzone_menu_response', ['account' => $account])
                                <span class="tg-msg-time">8:56 AM</span>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Message Input Bar -->
                    <div class="tg-input-bar d-flex align-items-center gap-2 p-3 border-top border-dark">
                        <button type="button" class="btn btn-outline-secondary text-white py-2 px-3 tg-action-btn" data-action="start" title="Main Menu" style="flex-shrink: 0;">
                            <i class="las la-bars fs-5"></i>
                        </button>
                        <input type="text" class="form-control tg-msg-input py-2 px-3" id="tgMessageInput" placeholder="Send a command (e.g. /shop, /orders, /wallet, /start)..." autocomplete="off">
                        <button type="button" class="btn btn-primary text-white fw-bold py-2 px-3 rounded-pill d-inline-flex align-items-center justify-content-center gap-1 tg-send-btn" id="tgSendBtn" style="flex-shrink: 0; min-width: 90px; white-space: nowrap;">
                            <span>Send</span>
                            <i class="las la-paper-plane fs-6"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection

@push('style')
<style>
    .tg-desktop-app {
        background-color: #0e1621;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #1f2c3d;
        height: calc(100vh - 170px);
        min-height: 520px;
        max-height: 720px;
        display: flex;
        flex-direction: column;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    }
    .tg-desktop-app > .row {
        flex: 1 1 0;
        min-height: 0;
        height: 100%;
        margin: 0;
    }
    .tg-sidebar-pane {
        background-color: #151e27;
        height: 100%;
        min-height: 0;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    .tg-sidebar-header {
        background-color: #17212b;
        flex-shrink: 0;
    }
    .tg-sidebar-menu {
        flex: 1 1 0;
        min-height: 0;
        overflow-y: auto !important;
        scrollbar-width: thin;
        scrollbar-color: #2b5278 #111822;
    }
    .tg-sidebar-menu::-webkit-scrollbar,
    .tg-chat-feed::-webkit-scrollbar {
        width: 6px;
    }
    .tg-sidebar-menu::-webkit-scrollbar-track,
    .tg-chat-feed::-webkit-scrollbar-track {
        background: #111822;
    }
    .tg-sidebar-menu::-webkit-scrollbar-thumb,
    .tg-chat-feed::-webkit-scrollbar-thumb {
        background: #2b5278;
        border-radius: 4px;
    }
    .tg-status-dot {
        width: 8px;
        height: 8px;
        background-color: #4ade80;
        border-radius: 50%;
        display: inline-block;
        box-shadow: 0 0 6px #4ade80;
    }
    .tg-account-card {
        background: #1c2733;
        border: 1px solid #233242;
    }
    .tg-code {
        color: #38bdf8 !important;
        background: rgba(0,0,0,0.4) !important;
        padding: 2px 6px;
        border-radius: 4px;
        font-weight: bold;
    }
    .tg-chat-pane {
        background-color: #0e1621;
        height: 100%;
        min-height: 0;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    .tg-chat-header {
        background-color: #17212b;
        flex-shrink: 0;
    }
    .tg-chat-feed {
        flex: 1 1 0 !important;
        min-height: 0 !important;
        overflow-y: auto !important;
        scroll-behavior: smooth;
        scrollbar-width: thin;
        scrollbar-color: #2b5278 #111822;
        background: #0e1621 url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%2F1b2733' fill-opacity='0.15' fill-rule='evenodd'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/svg%3E");
    }
    .tg-msg-row {
        display: flex;
        width: 100%;
    }
    .tg-msg-bot-row {
        justify-content: flex-start;
    }
    .tg-msg-user-row {
        justify-content: flex-end;
    }
    .tg-msg-bot {
        background-color: #182533 !important;
        color: #ffffff !important;
        border-radius: 12px 12px 12px 2px;
        max-width: 85%;
        padding: 12px 16px;
        position: relative;
        box-shadow: 0 2px 6px rgba(0,0,0,0.3);
    }
    .tg-msg-user {
        background-color: #2b5278 !important;
        color: #ffffff !important;
        border-radius: 12px 12px 2px 12px;
        max-width: 75%;
        padding: 10px 16px;
        position: relative;
        font-size: 14px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.3);
    }
    .tg-msg-user span {
        color: #ffffff !important;
        font-weight: 500;
    }
    .tg-msg-user .tg-msg-time {
        color: #93c5fd !important;
    }
    .tg-msg-time {
        font-size: 11px;
        color: #94a3b8 !important;
        float: right;
        margin-top: 6px;
        margin-left: 10px;
    }
    .tg-bot-card {
        font-size: 13.5px;
        line-height: 1.5;
        color: #ffffff !important;
    }
    .tg-bot-card * {
        color: #ffffff;
    }
    .tg-card-header {
        font-size: 15px;
        border-bottom: 1px solid rgba(255,255,255,0.12);
        padding-bottom: 6px;
        color: #ffffff !important;
    }
    .tg-info-box {
        background-color: rgba(0,0,0,0.35) !important;
        padding: 10px 14px;
        border-radius: 8px;
        border-left: 3px solid #38bdf8;
    }
    .tg-info-box * {
        color: #f8fafc !important;
    }
    .tg-info-box code, .tg-bot-card code {
        color: #38bdf8 !important;
        background: rgba(0,0,0,0.5) !important;
        padding: 2px 6px;
        border-radius: 4px;
        font-weight: 600;
        font-size: 12px;
    }
    .tg-info-box .text-success, .tg-bot-card .text-success {
        color: #4ade80 !important;
    }
    .tg-btn-blue {
        background-color: #204169 !important;
        color: #ffffff !important;
        border: 1px solid #325f94 !important;
        font-weight: 700 !important;
        font-size: 13.5px !important;
        border-radius: 8px;
        transition: all 0.2s;
    }
    .tg-btn-blue *, .tg-btn-blue span, .tg-btn-blue i {
        color: #ffffff !important;
        font-weight: 700 !important;
    }
    .tg-btn-blue:hover {
        background-color: #2b5688 !important;
        border-color: #38bdf8 !important;
    }
    .tg-btn-blue:hover *, .tg-btn-blue:hover span, .tg-btn-blue:hover i {
        color: #ffffff !important;
    }
    .tg-btn-green {
        background-color: #174d2b !important;
        color: #ffffff !important;
        border: 1px solid #247543 !important;
        font-weight: 700 !important;
        font-size: 13.5px !important;
        border-radius: 8px;
        transition: all 0.2s;
    }
    .tg-btn-green *, .tg-btn-green span, .tg-btn-green i {
        color: #ffffff !important;
        font-weight: 700 !important;
    }
    .tg-btn-green:hover {
        background-color: #1e6237 !important;
        border-color: #4ade80 !important;
    }
    .tg-btn-green:hover *, .tg-btn-green:hover span, .tg-btn-green:hover i {
        color: #ffffff !important;
    }
    .tg-send-btn {
        flex-shrink: 0 !important;
        white-space: nowrap !important;
        min-width: 90px !important;
        height: 38px !important;
        font-weight: bold !important;
        color: #ffffff !important;
    }

    .tg-btn-prod-stock {
        background-color: #166534 !important;
        border: 1px solid #22c55e !important;
        color: #ffffff !important;
        border-radius: 8px;
    }
    .tg-btn-prod-stock:hover {
        background-color: #15803d !important;
    }
    .tg-btn-prod-stock span {
        color: #ffffff !important;
    }
    .tg-btn-prod-nostock {
        background-color: #991b1b !important;
        border: 1px solid #ef4444 !important;
        color: #ffffff !important;
        border-radius: 8px;
    }
    .tg-btn-prod-nostock:hover {
        background-color: #b91c1c !important;
    }
    .tg-btn-prod-nostock span {
        color: #ffffff !important;
    }
    .tg-input-bar {
        background-color: #17212b;
        flex-shrink: 0;
    }
    .tg-msg-input {
        background-color: #0e1621 !important;
        border: 1px solid #2b5278 !important;
        color: #ffffff !important;
        border-radius: 24px !important;
        font-size: 13.5px !important;
    }
    .tg-msg-input::placeholder {
        color: rgba(255,255,255,0.4) !important;
    }
    .tg-custom-table {

        width: 100%;
        border-collapse: collapse;
        background: #111b27;
        border: 1px solid #23374d;
        border-radius: 8px;
        overflow: hidden;
    }
    .tg-custom-table th {
        background: #1e334a !important;
        color: #38bdf8 !important;
        font-size: 13px !important;
        font-weight: 700 !important;
        padding: 8px 12px !important;
        border: 1px solid #23374d !important;
        text-transform: uppercase;
    }
    .tg-custom-table td {
        background: #142130 !important;
        color: #ffffff !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        padding: 8px 12px !important;
        border: 1px solid #23374d !important;
    }
    .tg-alert-danger {
        background-color: #450a0a !important;
        border: 1px solid #ef4444 !important;
        color: #fecaca !important;
        font-weight: 600 !important;
        font-size: 13px !important;
        padding: 10px 14px !important;
        border-radius: 8px !important;
    }
    .tg-alert-warning {
        background-color: #422006 !important;
        border: 1px solid #eab308 !important;
        color: #fef08a !important;
        font-weight: 600 !important;
        font-size: 13px !important;
        padding: 10px 14px !important;
        border-radius: 8px !important;
    }
    .tg-alert-success {
        background-color: #052e16 !important;
        border: 1px solid #22c55e !important;
        color: #bbf7d0 !important;
        font-weight: 600 !important;
        font-size: 13px !important;
        padding: 10px 14px !important;
        border-radius: 8px !important;
    }
    .tg-alert-info {
        background-color: #082f49 !important;
        border: 1px solid #0284c7 !important;
        color: #bae6fd !important;
        font-weight: 600 !important;
        font-size: 13px !important;
        padding: 10px 14px !important;
        border-radius: 8px !important;
    }

    .btn-xs {
        padding: 2px 6px;
        font-size: 11px;
    }
</style>
@endpush

@push('script')
<script>
    (function($) {
        "use strict";

        const actionUrl = "{{ route('admin.warzone.telegram.action') }}";
        const csrfToken = "{{ csrf_token() }}";

        function getCurrentTime() {
            const now = new Date();
            let hours = now.getHours();
            let minutes = now.getMinutes();
            const ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12;
            hours = hours ? hours : 12;
            minutes = minutes < 10 ? '0' + minutes : minutes;
            return hours + ':' + minutes + ' ' + ampm;
        }

        function scrollToBottom() {
            setTimeout(function() {
                const feed = document.getElementById('chatFeed');
                if (feed) {
                    feed.scrollTop = feed.scrollHeight;
                }
            }, 50);
        }


        function appendUserMessage(text) {
            const timeStr = getCurrentTime();
            const html = `
                <div class="tg-msg-row tg-msg-user-row mb-3">
                    <div class="tg-msg-user">
                        <span>${escapeHtml(text)}</span>
                        <span class="tg-msg-time">${timeStr} ✓✓</span>
                    </div>
                </div>
            `;
            $('#chatFeed').append(html);
            scrollToBottom();
        }

        function appendBotLoading() {
            const loadingId = 'tgLoading_' + Date.now();
            const html = `
                <div class="tg-msg-row tg-msg-bot-row mb-3" id="${loadingId}">
                    <div class="tg-msg-bot text-white-50 small">
                        <i class="las la-spinner la-spin"></i> Typing...
                    </div>
                </div>
            `;
            $('#chatFeed').append(html);
            scrollToBottom();
            return loadingId;
        }

        function appendBotResponse(loadingId, htmlContent) {
            const timeStr = getCurrentTime();
            const responseHtml = `
                <div class="tg-msg-row tg-msg-bot-row mb-3">
                    <div class="tg-msg-bot">
                        ${htmlContent}
                        <span class="tg-msg-time">${timeStr}</span>
                    </div>
                </div>
            `;
            if (loadingId) {
                $('#' + loadingId).replaceWith(responseHtml);
            } else {
                $('#chatFeed').append(responseHtml);
            }
            scrollToBottom();
        }

        function escapeHtml(string) {
            return String(string).replace(/[&<>"']/g, function (s) {
                return {
                    "&": "&amp;",
                    "<": "&lt;",
                    ">": "&gt;",
                    '"': '&quot;',
                    "'": '&#39;'
                }[s];
            });
        }

        function sendActionRequest(actionName, extraData = {}) {
            const userMsg = extraData.user_text || ('/' + actionName);
            appendUserMessage(userMsg);
            const loadingId = appendBotLoading();

            const postData = Object.assign({
                _token: csrfToken,
                action: actionName
            }, extraData);

            $.ajax({
                url: actionUrl,
                type: 'POST',
                data: postData,
                dataType: 'json',
                success: function(res) {
                    if (res.success && res.html) {
                        appendBotResponse(loadingId, res.html);
                        if (res.account && res.account.wallet_balance !== undefined) {
                            $('#topBalanceBadge').text('💰 $' + parseFloat(res.account.wallet_balance).toFixed(2));
                            $('#leftBalanceBadge').text('$' + parseFloat(res.account.wallet_balance).toFixed(2));
                        }
                    } else {
                        const errMsg = `<div class="tg-alert-danger mb-0">❌ ${res.message || 'Error executing request'}</div>`;
                        appendBotResponse(loadingId, errMsg);
                    }
                },
                error: function(xhr) {
                    const errMsg = `<div class="tg-alert-danger mb-0">❌ Connection error (${xhr.status}). Try again.</div>`;
                    appendBotResponse(loadingId, errMsg);
                }
            });
        }


        // Toggle Keyboard Panel
        $('#toggleKeyboardBtn').on('click', function() {
            $('#keyboardContainer').slideToggle(200);
        });

        // Click Keyboard buttons
        $(document).on('click', '.tg-keyboard-btn, .tg-action-btn', function(e) {
            e.preventDefault();
            const action = $(this).data('action');
            const btnText = $(this).text().trim();
            if (action) {
                sendActionRequest(action, { user_text: btnText });
            }
        });

        // Click Product button in Shop view
        $(document).on('click', '.tg-product-btn', function(e) {
            e.preventDefault();
            const serviceId = $(this).data('service-id');
            const btnText = $(this).text().trim();
            sendActionRequest('product_detail', { service_id: serviceId, user_text: btnText });
        });

        // Submit Order Form inside Product Detail
        $(document).on('submit', '.tg-order-form', function(e) {
            e.preventDefault();
            const serviceId = $(this).data('service-id');
            const qty = $(this).find('.tg-order-qty').val();
            sendActionRequest('place_order', {
                service_id: serviceId,
                quantity: qty,
                user_text: `💳 Buy Product ${serviceId} (Qty: ${qty})`
            });
        });

        // Click Order Detail button
        $(document).on('click', '.tg-order-detail-btn', function(e) {
            e.preventDefault();
            const orderId = $(this).data('order-id');
            sendActionRequest('order_detail', { order_id: orderId, user_text: `/order_${orderId}` });
        });

        // Message Input Enter key
        $('#tgMessageInput').on('keypress', function(e) {
            if (e.which === 13) {
                $('#tgSendBtn').click();
            }
        });

        // Click Send button
        $('#tgSendBtn').on('click', function() {
            const inputVal = $('#tgMessageInput').val().trim();
            if (!inputVal) return;
            $('#tgMessageInput').val('');

            let action = inputVal.toLowerCase().replace('/', '');
            if (action === 'start' || action === 'shop' || action === 'wallet' || action === 'orders' || action === 'profile' || action === 'api_key' || action === 'stats' || action === 'autobuy' || action === 'sniper' || action === 'check_now') {
                sendActionRequest(action, { user_text: inputVal });
            } else {
                // Default to menu if unrecognized command
                sendActionRequest('start', { user_text: inputVal });
            }
        });


        window.copyApiKey = function() {
            const copyText = document.getElementById("apiKeyInput");
            if (copyText) {
                copyText.select();
                copyText.setSelectionRange(0, 99999);
                navigator.clipboard.writeText(copyText.value);
                alert("API Key copied to clipboard!");
            }
        };

        scrollToBottom();
    })(jQuery);
</script>
@endpush
