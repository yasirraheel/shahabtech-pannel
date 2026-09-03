<div class="tg-bot-card">
    <div class="tg-card-header mb-2 text-white">
        📦 <strong>Product Details: {{ $service['name'] }}</strong>
    </div>
    <div class="tg-card-body">
        <div class="tg-info-box mb-3">
            <div class="text-white mb-1">🏷️ <strong class="text-white">Service ID:</strong> <code class="tg-code">{{ $service['service_id'] }}</code></div>
            <div class="text-white mb-1">💰 <strong class="text-white">Base Price:</strong> <span class="text-warning fw-bold">${{ number_format($service['price'] ?? 0, 2) }}</span></div>
            <div class="text-white mb-1">📦 <strong class="text-white">Available Stock:</strong> <span class="badge {{ ($service['stock'] ?? 0) > 0 ? 'bg-success' : 'bg-danger' }} text-white">{{ $service['stock'] ?? 0 }}</span></div>
            <div class="text-white">⚡ <strong class="text-white">Status:</strong> <span class="badge bg-info text-dark fw-bold">{{ ($service['orderable'] ?? false) ? 'Orderable' : 'Unavailable' }}</span></div>
        </div>

        @if(!empty($service['price_tiers']))
            <div class="mb-3">
                <h6 class="text-white mb-2 fw-bold">📊 Tiered Pricing Rules:</h6>
                <table class="tg-custom-table w-100 mb-0">
                    <thead>
                        <tr>
                            <th class="text-center">Min Qty</th>
                            <th class="text-center">Max Qty</th>
                            <th class="text-center">Unit Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($service['price_tiers'] as $tier)
                            <tr>
                                <td class="text-center text-white fw-bold">{{ $tier['min_qty'] }}</td>
                                <td class="text-center text-white fw-bold">{{ $tier['max_qty'] }}</td>
                                <td class="text-center text-success fw-bold">${{ number_format($tier['unit_price'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if(($service['stock'] ?? 0) > 0 && ($service['orderable'] ?? true))
            <form class="tg-order-form mt-3" data-service-id="{{ $service['service_id'] }}">
                <div class="mb-2">
                    <label class="form-label text-white small fw-bold">Enter Quantity to Buy:</label>
                    <div class="input-group">
                        <input type="number" class="form-control form-control-sm tg-order-qty bg-dark text-white border-secondary" value="1" min="1" max="{{ $service['stock'] }}" required>
                        <button type="submit" class="btn btn-success btn-sm px-4 fw-bold text-white">
                            💳 Buy Now
                        </button>
                    </div>
                </div>
            </form>
        @else
            <div class="tg-alert-danger mb-2">
                ⚠️ Product is Out of Stock or Currently Unavailable.
            </div>
        @endif

        <button type="button" class="btn tg-btn-blue btn-sm w-100 mt-2 tg-action-btn fw-bold text-white" data-action="shop">
            ⬅️ Back to Products List
        </button>
    </div>
</div>

