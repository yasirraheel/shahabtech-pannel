<div class="tg-bot-card">
    <div class="tg-card-header">
        Welcome to Warzone Shop 🖐️
    </div>
    <div class="tg-card-body">
        <p class="mb-2 text-white">👋 Hello! <strong>{{ $account['first_name'] ?? 'Shahab' }}</strong></p>
        
        <div class="tg-info-box my-2">
            <div class="text-white">📌 <strong>Account Details</strong></div>
            <div class="text-white">💳 Telegram ID: <code>{{ $account['chat_id'] ?? '6976455363' }}</code></div>
            <div class="text-white">💰 Balance: <span class="text-success fw-bold">${{ number_format($account['wallet_balance'] ?? 17.7, 2) }}</span></div>
        </div>

        <p class="mt-2 mb-0 text-white-50 small">Choose below 📉</p>
    </div>
</div>
