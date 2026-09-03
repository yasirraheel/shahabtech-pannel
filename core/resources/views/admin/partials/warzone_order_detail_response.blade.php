<div class="tg-bot-card">
    @if($order)
        <div class="tg-card-header mb-2">
            🔎 <strong>Order Details: #{{ $order['order_id'] }}</strong>
        </div>
        <div class="tg-card-body">
            <div class="tg-info-box mb-3">
                <div class="text-white">📦 <strong>Service:</strong> {{ $order['service'] }} (<code>{{ $order['service_id'] }}</code>)</div>
                <div class="text-white">🔢 <strong>Quantity:</strong> {{ $order['quantity'] }}</div>
                <div class="text-white">💰 <strong>Total Amount:</strong> ${{ number_format($order['amount'], 2) }}</div>
                <div class="text-white">⚡ <strong>Status:</strong> <span class="badge {{ $order['status'] === 'success' ? 'bg-success' : 'bg-danger' }}">{{ ucfirst($order['status']) }}</span></div>
                <div class="text-white">📅 <strong>Date:</strong> {{ $order['created_at'] }}</div>
            </div>

            @if(!empty($order['delivered_products']))
                <div class="mb-3">
                    <h6 class="text-success mb-2">🔑 Delivered Product(s):</h6>
                    @foreach($order['delivered_products'] as $prod)
                        <div class="p-2 bg-dark rounded border border-success text-break mb-1 small text-white">
                            <code>{{ $prod }}</code>
                            @if(filter_var($prod, FILTER_VALIDATE_URL))
                                <a href="{{ $prod }}" target="_blank" class="btn btn-xs btn-outline-info ms-2 py-0">Open Link 🔗</a>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            <button type="button" class="btn btn-secondary btn-sm w-100 tg-action-btn" data-action="orders">
                ⬅️ Back to Order History
            </button>
        </div>
    @else
        <div class="alert alert-danger mb-0">Order not found.</div>
    @endif
</div>
