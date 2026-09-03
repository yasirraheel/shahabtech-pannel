<div class="tg-bot-card">
    <div class="tg-card-header mb-2">
        🛡️ <strong>Warzone Account Profile</strong>
    </div>
    <div class="tg-card-body">
        <div class="tg-info-box mb-3">
            <div class="text-white">👤 <strong>Name:</strong> {{ $account['first_name'] ?? 'Shahab' }}</div>
            <div class="text-white">💳 <strong>Telegram ID:</strong> <code>{{ $account['chat_id'] ?? '6976455363' }}</code></div>
            <div class="text-white">💰 <strong>Wallet Balance:</strong> <span class="text-success fw-bold">${{ number_format($account['wallet_balance'] ?? 17.7, 2) }}</span></div>
            <div class="text-white">📦 <strong>Total Orders Placed:</strong> {{ $stats['total_orders'] ?? 0 }}</div>
            <div class="text-white">⚡ <strong>Total API Calls Made:</strong> {{ $stats['api_calls'] ?? 0 }}</div>
        </div>
        <button type="button" class="btn btn-primary btn-sm w-100 tg-action-btn" data-action="start">
            ⬅️ Back to Menu
        </button>
    </div>
</div>
