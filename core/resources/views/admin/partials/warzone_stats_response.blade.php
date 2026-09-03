<div class="tg-bot-card">
    <div class="tg-card-header mb-2">
        📊 <strong>Account Statistics</strong>
    </div>
    <div class="tg-card-body">
        <div class="tg-info-box mb-3">
            <div class="text-white">💵 <strong>Wallet Balance:</strong> ${{ number_format($stats['wallet_balance'] ?? 0, 2) }}</div>
            <div class="text-white">💸 <strong>Total Spent:</strong> ${{ number_format($stats['total_spent'] ?? 0, 2) }}</div>
            <div class="text-white">📦 <strong>Total Orders:</strong> {{ $stats['total_orders'] ?? 0 }}</div>
            <div class="text-white">⚡ <strong>API Requests:</strong> {{ $stats['api_calls'] ?? 0 }}</div>
        </div>

        @if(!empty($stats['products_breakdown']))
            <div class="mb-3">
                <h6 class="text-white mb-2">🛍️ Products Purchased Breakdown:</h6>
                @foreach($stats['products_breakdown'] as $p)
                    <div class="d-flex justify-content-between align-items-center p-2 bg-dark rounded border border-secondary mb-1 small text-white">
                        <span>{{ $p['name'] }}</span>
                        <span class="badge bg-primary">Sold: {{ $p['quantity_sold'] }} | Revenue: ${{ number_format($p['revenue'], 2) }}</span>
                    </div>
                @endforeach
            </div>
        @endif

        <button type="button" class="btn btn-primary btn-sm w-100 tg-action-btn" data-action="start">
            ⬅️ Back to Menu
        </button>
    </div>
</div>
