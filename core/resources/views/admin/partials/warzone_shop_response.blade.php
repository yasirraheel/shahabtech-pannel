<div class="tg-bot-card">
    <div class="tg-card-header mb-2">
        🛒 <strong>Warzone Shop Products</strong>
    </div>
    <div class="tg-card-body">
        <p class="text-white-50 small mb-3">Select a product below to view details and place an order:</p>
        <div class="d-flex flex-column gap-1">
            @forelse($services as $service)
                @php
                    $inStock = ($service['stock'] ?? 0) > 0;
                    $btnClass = $inStock ? 'tg-btn-prod-stock' : 'tg-btn-prod-nostock';
                    $icon = $inStock ? '🟢' : '🔴';
                @endphp
                <button type="button" class="btn {{ $btnClass }} w-100 text-start py-1 px-2 tg-product-btn" 
                        data-service-id="{{ $service['service_id'] }}">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-white small">{{ $icon }} {{ $service['name'] }}</span>
                        <span class="badge bg-black text-white px-2 py-1 small">${{ number_format($service['price'] ?? 0, 2) }} | Stock: {{ $service['stock'] ?? 0 }}</span>
                    </div>
                </button>
            @empty
                <div class="alert alert-warning py-1 mb-0 small text-white">No products available at the moment.</div>
            @endforelse
            <button type="button" class="btn btn-primary btn-sm w-100 py-1 mt-1 tg-action-btn text-white fw-bold" data-action="start">
                ⬅️ Back to Menu
            </button>
        </div>

    </div>
</div>
