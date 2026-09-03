@extends('admin.layouts.app')

@section('panel')
<div class="row justify-content-center">
    <div class="col-xl-6 col-lg-8 col-md-10 col-12">
        <div class="tg-app-container shadow-lg">
            <!-- Telegram App Header -->
            <div class="tg-app-header d-flex align-items-center justify-content-between px-3 py-2">
                <div class="d-flex align-items-center gap-2">
                    <div class="tg-bot-avatar">
                        <img src="https://ui-avatars.com/api/?name=Warzone+Shop&background=2b5278&color=fff&size=48&font-size=0.4" alt="Warzone Shop" class="rounded-circle">
                    </div>
                    <div>
                        <h6 class="tg-bot-name mb-0 text-white fw-bold">Warzone Shop</h6>
                        <span class="tg-bot-subtext text-white-50 small" style="font-size: 12px;">13,071 monthly users</span>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-success px-2 py-1" id="topBalanceBadge">💰 Balance: ${{ number_format($account['wallet_balance'] ?? 17.7, 2) }}</span>
                    <button class="btn btn-sm btn-outline-light border-0 py-1 px-2 tg-action-btn" data-action="start" title="Refresh Feed">
                        <i class="las la-sync"></i>
                    </button>
                </div>
            </div>

            <!-- Chat Scrollable Feed -->
            <div class="tg-chat-feed p-3" id="chatFeed">
                <!-- Initial Welcome Bot Message -->
                <div class="tg-msg-row tg-msg-bot-row mb-3">
                    <div class="tg-msg-bot">
                        @include('admin.partials.warzone_menu_response', ['account' => $account])
                        <span class="tg-msg-time">8:56 AM</span>
                    </div>
                </div>
            </div>

            <!-- Telegram Keyboard Grid Panel -->
            <div class="tg-keyboard-container p-2" id="keyboardContainer">
                <div class="row g-2">
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
            <div class="tg-input-bar d-flex align-items-center gap-2 p-2">
                <button type="button" class="btn text-white p-1" id="toggleKeyboardBtn" title="Toggle Keyboard">
                    <i class="las la-bars fs-4"></i> Menu
                </button>
                <input type="text" class="form-control tg-msg-input" id="tgMessageInput" placeholder="Message..." autocomplete="off">
                <button type="button" class="btn btn-primary text-white py-1 px-3 rounded-circle" id="tgSendBtn">
                    <i class="las la-paper-plane fs-5"></i>
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
    }
    .tg-app-header {
        background-color: #17212b;
        border-bottom: 1px solid #0e1621;
    }
    .tg-bot-avatar img {
        width: 38px;
        height: 38px;
    }
    .tg-chat-feed {
        height: 440px;
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
        background-color: #182533;
        color: #ffffff;
        border-radius: 12px 12px 12px 2px;
        max-width: 88%;
        padding: 10px 12px;
        position: relative;
        box-shadow: 0 1px 2px rgba(0,0,0,0.3);
    }
    .tg-msg-user {
        background-color: #2b5278;
        color: #ffffff;
        border-radius: 12px 12px 2px 12px;
        max-width: 75%;
        padding: 8px 12px;
        position: relative;
        font-size: 14px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.3);
    }
    .tg-msg-time {
        font-size: 10px;
        color: rgba(255,255,255,0.5);
        float: right;
        margin-top: 4px;
        margin-left: 8px;
    }
    .tg-bot-card {
        font-size: 13px;
        line-height: 1.5;
    }
    .tg-card-header {
        font-size: 15px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        padding-bottom: 6px;
    }
    .tg-info-box {
        background-color: rgba(0,0,0,0.25);
        padding: 8px 12px;
        border-radius: 8px;
        border-left: 3px solid #2b5278;
    }
    .tg-keyboard-container {
        background-color: #17212b;
        border-top: 1px solid #0e1621;
    }
    .tg-btn-blue {
        background-color: #2b5278 !important;
        color: #ffffff !important;
        border: none !important;
        font-weight: 500;
        font-size: 13px;
        padding: 9px;
        border-radius: 8px;
        transition: all 0.2s;
    }
    .tg-btn-blue:hover {
        background-color: #366593 !important;
    }
    .tg-btn-green {
        background-color: #26804a !important;
        color: #ffffff !important;
        border: none !important;
        font-weight: 500;
        font-size: 13px;
        padding: 9px;
        border-radius: 8px;
        transition: all 0.2s;
    }
    .tg-btn-green:hover {
        background-color: #2fa15d !important;
    }
    .tg-input-bar {
        background-color: #17212b;
        border-top: 1px solid #0e1621;
    }
    .tg-msg-input {
        background-color: #0e1621 !important;
        border: 1px solid #2b5278 !important;
        color: #ffffff !important;
        border-radius: 20px !important;
        padding: 6px 14px !important;
        font-size: 13px !important;
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
