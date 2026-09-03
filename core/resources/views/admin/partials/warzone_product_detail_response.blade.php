<div class="tg-bot-card">
    <div class="tg-card-header mb-2">
        📦 <strong>Product Details: {{ $service['name'] }}</strong>
    </div>
    <div class="tg-card-body">
        <div class="tg-info-box mb-3">
            <div class="text-white">🏷️ <strong>Service ID:</strong> <code>{{ $service['service_id'] }}</code></div>
            <div class="text-white">💰 <strong>Base Price:</strong> ${{ number_format($service['price'] ?? 0, 2) }}</div>
            <div class="text-white">📦 <strong>Available Stock:</strong> <span class="badge {{ ($service['stock'] ?? 0) > 0 ? 'bg-success' : 'bg-danger' }}">{{ $service['stock'] ?? 0 }}</span></div>
            <div class="text-white">⚡ <strong>Status:</strong> {{ ($service['orderable'] ?? false) ? 'Orderable' : 'Unavailable' }}</div>
        </div>

        @if(!empty($service['price_tiers']))
            <div class="mb-3">
                <h6 class="text-white mb-2">📊 Tiered Pricing Rules:</h6>
                <table class="table table-sm table-dark text-white border mb-0">
                    <thead>
                        <tr>
                            <th>Min Qty</th>
                            <th>Max Qty</th>
                            <th>Unit Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($service['price_tiers'] as $tier)
                            <tr>
                                <td>{{ $tier['min_qty'] }}</td>
                                <td>{{ $tier['max_qty'] }}</td>
                                <td class="text-success">${{ number_format($tier['unit_price'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if(($service['stock'] ?? 0) > 0 && ($service['orderable'] ?? true))
            <form class="tg-order-form mt-3" data-service-id="{{ $service['service_id'] }}">
                <div class="mb-2">
                    <label class="form-label text-white small">Enter Quantity to Buy:</label>
                    <div class="input-group">
                        <input type="number" class="form-control form-control-sm tg-order-qty" value="1" min="1" max="{{ $service['stock'] }}" required>
                        <button type="submit" class="btn btn-success btn-sm px-3">
                            💳 Buy Now
                        </button>
                    </div>
                </div>
            </form>
        @else
            <div class="alert alert-danger py-2 mb-2 small">⚠️ Product is Out of Stock or Currently Unavailable.</div>
        @endif

        <button type="button" class="btn btn-secondary btn-sm w-100 mt-2 tg-action-btn" data-action="shop">
            ⬅️ Back to Products List
        </button>
    </div>
</div>
