@php
    $success = isset($result['order_id']) || isset($result['success']);
@endphp

<div class="tg-bot-card">
    @if($success)
        <div class="tg-card-header text-success mb-2">
            🎉 <strong>Order Placed Successfully!</strong>
        </div>
        <div class="tg-card-body">
            <div class="tg-info-box mb-3">
                <div class="text-white">🆔 <strong>Order ID:</strong> <code>{{ $result['order_id'] ?? 'ORD-NEW' }}</code></div>
                <div class="text-white">📦 <strong>Product:</strong> {{ $result['service'] ?? ($result['name'] ?? 'Product') }}</div>
                <div class="text-white">🔢 <strong>Quantity:</strong> {{ $result['quantity'] ?? 1 }}</div>
                <div class="text-white">💵 <strong>Amount Deducted:</strong> ${{ number_format($result['amount'] ?? 0, 2) }}</div>
                <div class="text-white">💰 <strong>New Wallet Balance:</strong> <span class="text-success fw-bold">${{ number_format($account['wallet_balance'] ?? 0, 2) }}</span></div>
            </div>

            @if(!empty($result['delivered_products']))
                <div class="mb-3">
                    <h6 class="text-success mb-2">🔑 Delivered Product(s):</h6>
                    @foreach($result['delivered_products'] as $prod)
                        <div class="p-2 bg-dark rounded border border-success text-break mb-1 small text-white">
                            <code>{{ $prod }}</code>
                            @if(filter_var($prod, FILTER_VALIDATE_URL))
                                <a href="{{ $prod }}" target="_blank" class="btn btn-xs btn-outline-info ms-2 py-0">Open Link 🔗</a>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            <button type="button" class="btn btn-primary btn-sm w-100 tg-action-btn" data-action="orders">
                📃 View Order History
            </button>
        </div>
    @else
        <div class="tg-card-header text-danger mb-2">
            ❌ <strong>Order Placement Failed</strong>
        </div>
        <div class="tg-card-body">
            <div class="alert alert-danger mb-3">
                {{ $result['error'] ?? ($result['message'] ?? 'Unable to process order.') }}
            </div>
            <button type="button" class="btn btn-secondary btn-sm w-100 tg-action-btn" data-action="shop">
                ⬅️ Back to Products List
            </button>
        </div>
    @endif
</div>
