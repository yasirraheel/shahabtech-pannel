<div class="tg-bot-card">
    <div class="tg-card-header text-white d-flex justify-content-between align-items-center mb-2">
        <span>🤖 <strong>Gemini Auto-Buy Sniper Bot</strong></span>
        <span class="badge bg-success px-2 py-1" style="font-size: 11px;">🟢 30s Cron Active</span>
    </div>
    <div class="tg-card-body">
        <!-- Live Status Bar -->
        <div class="p-2 rounded mb-3 d-flex align-items-center justify-content-between" style="background: rgba(34, 197, 94, 0.15); border: 1px solid #22c55e;">
            <div>
                <strong class="text-white small d-block">🟢 Auto-Buy Daemon: RUNNING</strong>
                <span class="text-white-50" style="font-size: 11px;">
                    Current System Time: <strong class="text-info">{{ now()->timezone('Asia/Karachi')->format('h:i:s A') }}</strong> (PKT)
                </span>
            </div>
            <span class="badge bg-success py-1 px-2" style="font-size: 11px;">Cycle: 30 Seconds</span>
        </div>

        <div class="tg-info-box mb-3">
            <div class="text-white mb-1">🎯 <strong class="text-white">Target Product:</strong> <span class="text-info fw-bold">Gemini AI Pro 18M</span> <code class="tg-code">S_01</code></div>
            <div class="text-white mb-1">📦 <strong class="text-white">Latest Stock Detected:</strong> 
                @if(($autobuyState['last_stock'] ?? 0) > 0)
                    <span class="badge bg-success">{{ $autobuyState['last_stock'] }} In Stock</span>
                @else
                    <span class="badge bg-danger">0 (Out of Stock)</span>
                @endif
            </div>
            <div class="text-white mb-1">💰 <strong class="text-white">Current Wallet Balance:</strong> <span class="text-success fw-bold">${{ number_format($account['wallet_balance'] ?? 17.70, 2) }}</span></div>
            <div class="text-white mb-1">⚡ <strong class="text-white">Purchase Strategy:</strong> <span class="text-warning fw-bold">Buy Max Possible Instantly</span></div>
            <div class="text-white mb-1">🕒 <strong class="text-white">Last Check Time:</strong> <span class="text-info fw-bold">{{ $autobuyState['last_check'] ?? 'Awaiting next cycle' }}</span></div>
            <div class="text-white">📊 <strong class="text-white">Checks Logged:</strong> <span class="badge bg-primary fs-6">{{ $autobuyState['total_checks'] ?? 0 }}</span> &bull; <strong class="text-white">Accounts Secured:</strong> <span class="badge bg-success fs-6">{{ $autobuyState['total_bought'] ?? 0 }}</span></div>
        </div>

        <!-- Live Activity Log Table -->
        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="text-white fw-bold mb-0" style="font-size: 13px;">📋 Recent Auto-Buy Activity Feed (System Time):</h6>
                <button type="button" class="btn btn-xs btn-outline-info tg-action-btn" data-action="autobuy" title="Refresh Log">
                    <i class="las la-sync"></i> Refresh Now
                </button>
            </div>

            @php
                $checks = $autobuyState['recent_checks'] ?? [];
            @endphp

            @if(!empty($checks))
                <div class="overflow-y-auto" style="max-height: 240px;">
                    <table class="tg-custom-table w-100">
                        <thead>
                            <tr>
                                <th style="font-size: 11px;">Time (PKT)</th>
                                <th class="text-center" style="font-size: 11px;">Stock</th>
                                <th class="text-center" style="font-size: 11px;">Balance</th>
                                <th style="font-size: 11px;">Status / Message</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($checks as $chk)
                                <tr>
                                    <td class="text-info fw-bold small" style="font-size: 11px;">{{ $chk['time'] ?? '--' }}</td>
                                    <td class="text-center">
                                        <span class="badge {{ ($chk['stock'] ?? 0) > 0 ? 'bg-success' : 'bg-danger' }}" style="font-size: 10px;">
                                            {{ $chk['stock'] ?? 0 }}
                                        </span>
                                    </td>
                                    <td class="text-center text-success small" style="font-size: 11px;">${{ number_format($chk['balance'] ?? 0, 2) }}</td>
                                    <td style="font-size: 11px;">
                                        @if(($chk['status'] ?? '') === 'ordered')
                                            <span class="text-success fw-bold">🎉 {{ $chk['message'] ?? 'Order Placed' }}</span>
                                        @elseif(($chk['status'] ?? '') === 'waiting')
                                            <span class="text-white-50">⏳ Standing by for stock...</span>
                                        @else
                                            <span class="text-warning">{{ $chk['message'] ?? 'Checked' }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="tg-alert-info py-2 mb-0 small">
                    ⏳ Cron job is registered. Live check logs will appear here every 30 seconds.
                </div>
            @endif
        </div>

        @if(!empty($autobuyState['orders']))
            <div class="mb-3">
                <h6 class="text-success fw-bold mb-2">🎁 Secured Gemini Orders:</h6>
                @foreach($autobuyState['orders'] as $ord)
                    <div class="p-2 bg-dark rounded border border-success mb-2 text-white small">
                        <div class="d-flex justify-content-between">
                            <strong>Order Date: {{ $ord['time'] ?? 'N/A' }}</strong>
                            <span class="badge bg-success">Qty: {{ $ord['quantity'] ?? 1 }}</span>
                        </div>
                        @if(!empty($ord['order_result']['delivered_products']))
                            <div class="mt-2">
                                <div class="text-white-50 small mb-1">Delivered Key(s):</div>
                                @foreach($ord['order_result']['delivered_products'] as $deliv)
                                    <code class="tg-code d-block text-break mb-1">{{ $deliv }}</code>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        <div class="d-flex gap-2 mt-3">
            <button type="button" class="btn btn-warning btn-sm w-50 tg-action-btn fw-bold text-dark" data-action="check_now">
                ⚡ Check & Buy Now
            </button>
            <button type="button" class="btn tg-btn-blue btn-sm w-50 tg-action-btn fw-bold text-white" data-action="start">
                ⬅️ Main Menu
            </button>
        </div>
    </div>
</div>
