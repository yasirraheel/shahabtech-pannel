<div class="tg-bot-card">
    <div class="tg-card-header mb-2">
        💰 <strong>Wallet & Balance Info</strong>
    </div>
    <div class="tg-card-body">
        <div class="tg-info-box mb-3">
            <div class="text-white">💳 <strong>Telegram Chat ID:</strong> <code>{{ $account['chat_id'] ?? '6976455363' }}</code></div>
            <div class="text-white">💵 <strong>Available Balance:</strong> <span class="text-success fw-bold fs-5">${{ number_format($account['wallet_balance'] ?? 17.7, 2) }}</span></div>
            <div class="text-white">💸 <strong>Total Lifetime Spent:</strong> ${{ number_format($stats['total_spent'] ?? 0, 2) }}</div>
            <div class="text-white">📊 <strong>Total Completed Orders:</strong> {{ $stats['total_orders'] ?? 0 }}</div>
        </div>
        <p class="small text-white-50 mb-3">💡 To top up your wallet balance, contact Warzone Support or admin in Telegram.</p>
        <button type="button" class="btn btn-primary btn-sm w-100 tg-action-btn" data-action="start">
            ⬅️ Back to Menu
        </button>
    </div>
</div>
