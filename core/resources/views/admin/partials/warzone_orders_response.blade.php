<div class="tg-bot-card">
    <div class="tg-card-header mb-2">
        📃 <strong>Order History</strong>
    </div>
    <div class="tg-card-body">
        @if(!empty($counts))
            <div class="d-flex flex-wrap gap-2 mb-3 small">
                <span class="badge bg-secondary">Total: {{ $counts['all'] ?? 0 }}</span>
                <span class="badge bg-success">Success: {{ $counts['success'] ?? 0 }}</span>
                <span class="badge bg-danger">Cancelled: {{ $counts['cancelled'] ?? 0 }}</span>
            </div>
        @endif

        <div class="d-flex flex-column gap-2 overflow-y-auto pr-1" style="max-height: 320px;">
            @forelse($orders as $ord)
                @php
                    $badgeClass = $ord['status'] === 'success' ? 'bg-success' : ($ord['status'] === 'cancelled' ? 'bg-danger' : 'bg-warning');
                @endphp
                <div class="p-2 bg-dark rounded border border-secondary">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <strong class="small text-white">#{{ $ord['order_id'] }}</strong>
                        <span class="badge {{ $badgeClass }}">{{ ucfirst($ord['status']) }}</span>
                    </div>
                    <div class="small text-white-50 mb-1">
                        📦 {{ $ord['service'] }} (Qty: {{ $ord['quantity'] }}) — ${{ number_format($ord['amount'], 2) }}
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small text-muted" style="font-size: 11px;">{{ $ord['created_at'] }}</span>
                        <button type="button" class="btn btn-xs btn-outline-primary tg-order-detail-btn px-2 py-0" data-order-id="{{ $ord['order_id'] }}">
                            Details 👁️
                        </button>
                    </div>
                </div>
            @empty
                <div class="alert alert-info py-2 mb-0">No orders found in your history.</div>
            @endforelse
        </div>

        <button type="button" class="btn btn-secondary btn-sm w-100 mt-3 tg-action-btn" data-action="start">
            ⬅️ Back to Menu
        </button>
    </div>
</div>
