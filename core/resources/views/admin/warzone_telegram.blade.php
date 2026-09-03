@extends('admin.layouts.app')

@section('panel')
<div class="row justify-content-center">
    <div class="col-xl-4 col-lg-5 col-md-7 col-sm-9 col-12">
        <div class="tg-app-container shadow-lg">
            <!-- Telegram App Header -->
            <div class="tg-app-header d-flex align-items-center justify-content-between px-3 py-1">
                <div class="d-flex align-items-center gap-2">
                    <div class="tg-bot-avatar">
                        <img src="https://ui-avatars.com/api/?name=Warzone+Shop&background=2b5278&color=fff&size=36&font-size=0.4" alt="Warzone Shop" class="rounded-circle">
                    </div>
                    <div>
                        <h6 class="tg-bot-name mb-0 text-white fw-bold" style="font-size: 13px;">Warzone Shop</h6>
                        <span class="tg-bot-subtext text-white-50" style="font-size: 10px;">13,071 monthly users</span>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-1">
                    <span class="badge bg-success px-2 py-1" id="topBalanceBadge" style="font-size: 10.5px;">💰 ${{ number_format($account['wallet_balance'] ?? 17.7, 2) }}</span>
                    <button class="btn btn-sm btn-outline-light border-0 py-0 px-1 tg-action-btn" data-action="start" title="Refresh Feed">
                        <i class="las la-sync fs-6"></i>
                    </button>
                </div>
            </div>

            <!-- Chat Scrollable Feed -->
            <div class="tg-chat-feed p-2" id="chatFeed">
                <!-- Initial Welcome Bot Message -->
                <div class="tg-msg-row tg-msg-bot-row mb-2">
                    <div class="tg-msg-bot">
                        @include('admin.partials.warzone_menu_response', ['account' => $account])
                        <span class="tg-msg-time">8:56 AM</span>
                    </div>
                </div>
            </div>

            <!-- Telegram Keyboard Grid Panel -->
            <div class="tg-keyboard-container p-1" id="keyboardContainer">
                <div class="row g-1">
                    <div class="col-6">
                        <button type="button" class="btn tg-btn-blue w-100 tg-keyboard-btn" data-action="shop">
                            🛒 Shop
                        </button>
                    </div>
                    <div class="col-6">
                        <button type="button" class="btn tg-btn-blue w-100 tg-keyboard-btn" data-action="wallet">
                            💰 Wallet
                        </button>
                    </div>
                    <div class="col-6">
                        <button type="button" class="btn tg-btn-blue w-100 tg-keyboard-btn" data-action="orders">
                            📃 Order History
                        </button>
                    </div>
                    <div class="col-6">
                        <button type="button" class="btn tg-btn-blue w-100 tg-keyboard-btn" data-action="orders">
                            💸 Recover Order
                        </button>
                    </div>
                    <div class="col-6">
                        <button type="button" class="btn tg-btn-green w-100 tg-keyboard-btn" data-action="wallet">
                            💰 Refer & Earn
                        </button>
                    </div>
                    <div class="col-6">
                        <button type="button" class="btn tg-btn-blue w-100 tg-keyboard-btn" data-action="profile">
                            🛡️ Profile
                        </button>
                    </div>
                    <div class="col-6">
                        <button type="button" class="btn tg-btn-green w-100 tg-keyboard-btn" data-action="support">
                            🤫 Support
                        </button>
                    </div>
                    <div class="col-6">
                        <button type="button" class="btn tg-btn-blue w-100 tg-keyboard-btn" data-action="api_key">
                            📱 API Key
                        </button>
                    </div>
                    <div class="col-12">
                        <button type="button" class="btn tg-btn-blue w-100 tg-keyboard-btn" data-action="stats">
                            📊 Account Stats
                        </button>
                    </div>
                </div>
            </div>

            <!-- Bottom Message Input Bar -->
            <div class="tg-input-bar d-flex align-items-center gap-1 p-1">
                <button type="button" class="btn text-white p-1" id="toggleKeyboardBtn" title="Toggle Keyboard" style="font-size: 11px;">
                    <i class="las la-bars"></i> Menu
                </button>
                <input type="text" class="form-control tg-msg-input" id="tgMessageInput" placeholder="Message..." autocomplete="off">
                <button type="button" class="btn btn-primary text-white p-1 rounded-circle d-flex align-items-center justify-content-center" id="tgSendBtn" style="width: 32px; height: 32px; min-width: 32px;">
                    <i class="las la-paper-plane" style="font-size: 14px;"></i>
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('style')
<style>
    .tg-app-container {
        background-color: #0e1621;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #17212b;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        max-width: 390px;
        margin: 0 auto;
        height: calc(100vh - 165px);
        max-height: 570px;
        min-height: 430px;
        display: flex;
        flex-direction: column;
    }
    .tg-app-header {
        background-color: #17212b;
        border-bottom: 1px solid #0e1621;
        flex-shrink: 0;
    }
    .tg-bot-avatar img {
        width: 32px;
        height: 32px;
    }
    .tg-chat-feed {
        flex: 1 1 auto;
        overflow-y: auto;
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
        border-radius: 10px 10px 10px 2px;
        max-width: 90%;
        padding: 8px 10px;
        position: relative;
        box-shadow: 0 1px 2px rgba(0,0,0,0.3);
    }
    .tg-msg-user {
        background-color: #24527a !important;
        color: #ffffff !important;
        border-radius: 10px 10px 2px 10px;
        max-width: 80%;
        padding: 6px 10px;
        position: relative;
        font-size: 12px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.3);
    }
    .tg-msg-user span {
        color: #ffffff !important;
        font-weight: 500;
    }
    .tg-msg-user .tg-msg-time {
        color: #93c5fd !important;
    }
    .tg-msg-time {
        font-size: 9.5px;
        color: #94a3b8 !important;
        float: right;
        margin-top: 4px;
        margin-left: 6px;
    }
    .tg-bot-card {
        font-size: 12px;
        line-height: 1.4;
        color: #ffffff !important;
    }
    .tg-bot-card * {
        color: #ffffff;
    }
    .tg-card-header {
        font-size: 13px;
        border-bottom: 1px solid rgba(255,255,255,0.12);
        padding-bottom: 4px;
        color: #ffffff !important;
    }
    .tg-info-box {
        background-color: rgba(0,0,0,0.35) !important;
        padding: 6px 10px;
        border-radius: 6px;
        border-left: 3px solid #38bdf8;
    }
    .tg-info-box * {
        color: #f8fafc !important;
    }
    .tg-info-box code, .tg-bot-card code {
        color: #38bdf8 !important;
        background: rgba(0,0,0,0.5) !important;
        padding: 2px 5px;
        border-radius: 4px;
        font-weight: 600;
        font-size: 11px;
    }
    .tg-info-box .text-success, .tg-bot-card .text-success {
        color: #4ade80 !important;
    }
    .tg-keyboard-container {
        background-color: #17212b;
        border-top: 1px solid #0e1621;
        flex-shrink: 0;
    }
    .tg-btn-blue {
        background-color: #24466b !important;
        color: #ffffff !important;
        border: none !important;
        font-weight: 600 !important;
        font-size: 11.5px !important;
        padding: 5px 8px !important;
        border-radius: 6px;
        transition: all 0.15s;
    }
    .tg-btn-blue:hover {
        background-color: #2e5988 !important;
        color: #ffffff !important;
    }
    .tg-btn-green {
        background-color: #1b6339 !important;
        color: #ffffff !important;
        border: none !important;
        font-weight: 600 !important;
        font-size: 11.5px !important;
        padding: 5px 8px !important;
        border-radius: 6px;
        transition: all 0.15s;
    }
    .tg-btn-green:hover {
        background-color: #24824b !important;
        color: #ffffff !important;
    }
    .tg-btn-prod-stock {
        background-color: #166534 !important;
        border: 1px solid #22c55e !important;
        color: #ffffff !important;
        border-radius: 6px;
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
        border-radius: 6px;
    }
    .tg-btn-prod-nostock:hover {
        background-color: #b91c1c !important;
    }
    .tg-btn-prod-nostock span {
        color: #ffffff !important;
    }
    .tg-input-bar {
        background-color: #17212b;
        border-top: 1px solid #0e1621;
        flex-shrink: 0;
    }
    .tg-msg-input {
        background-color: #0e1621 !important;
        border: 1px solid #2b5278 !important;
        color: #ffffff !important;
        border-radius: 18px !important;
        padding: 4px 10px !important;
        font-size: 11.5px !important;
        height: 30px !important;
    }
    .tg-msg-input::placeholder {
        color: rgba(255,255,255,0.4) !important;
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
            const feed = document.getElementById('chatFeed');
            feed.scrollTop = feed.scrollHeight;
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
                            $('#topBalanceBadge').text('💰 Balance: $' + parseFloat(res.account.wallet_balance).toFixed(2));
                        }
                    } else {
                        const errMsg = `<div class="alert alert-danger py-2 mb-0 small">❌ ${res.message || 'Error executing request'}</div>`;
                        appendBotResponse(loadingId, errMsg);
                    }
                },
                error: function(xhr) {
                    const errMsg = `<div class="alert alert-danger py-2 mb-0 small">❌ Connection error (${xhr.status}). Try again.</div>`;
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
            if (action === 'start' || action === 'shop' || action === 'wallet' || action === 'orders' || action === 'profile' || action === 'api_key' || action === 'stats') {
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
