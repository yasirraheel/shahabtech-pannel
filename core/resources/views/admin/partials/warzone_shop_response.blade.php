<div class="tg-bot-card">
    <div class="tg-card-header mb-2">
        🛒 <strong>Warzone Shop Products</strong>
    </div>
    <div class="tg-card-body">
        <p class="text-white-50 small mb-3">Select a product below to view details and place an order:</p>
        <div class="d-flex flex-column gap-2">
            @forelse($services as $service)
                @php
                    $inStock = ($service['stock'] ?? 0) > 0;
                    $btnColor = $inStock ? 'btn-success' : 'btn-danger';
                    $icon = $inStock ? '🟢' : '🔴';
                @endphp
                <button type="button" class="btn {{ $btnColor }} w-100 text-start py-2 px-3 tg-product-btn" 
                        data-service-id="{{ $service['service_id'] }}">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold">{{ $icon }} {{ $service['name'] }}</span>
                        <span class="badge bg-dark text-white">${{ number_format($service['price'] ?? 0, 2) }} | Stock: {{ $service['stock'] ?? 0 }}</span>
                    </div>
                </button>
            @empty
                <div class="alert alert-warning mb-0">No products available at the moment.</div>
            @endforelse
            <button type="button" class="btn btn-primary w-100 py-2 mt-2 tg-action-btn" data-action="start">
                ⬅️ Back to Menu
            </button>
        </div>
    </div>
</div>
