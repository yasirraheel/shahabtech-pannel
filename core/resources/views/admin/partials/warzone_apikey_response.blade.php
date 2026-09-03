<div class="tg-bot-card">
    <div class="tg-card-header mb-2">
        📱 <strong>Your Warzone API Key</strong>
    </div>
    <div class="tg-card-body">
        <div class="p-3 bg-dark rounded border border-info mb-3">
            <label class="form-label text-white-50 small mb-1">API Key:</label>
            <div class="input-group">
                <input type="text" class="form-control form-control-sm bg-secondary text-white border-0" id="apiKeyInput" value="{{ $apiKey }}" readonly>
                <button class="btn btn-info btn-sm" type="button" onclick="copyApiKey()">📋 Copy</button>
            </div>
        </div>
        <div class="small text-white-50 mb-3">
            🌐 <strong>Base URL:</strong> <code>https://api.warzoneshop.in</code><br>
            🔑 <strong>Auth Header:</strong> <code>X-API-Key: {{ $apiKey }}</code>
        </div>
        <button type="button" class="btn btn-primary btn-sm w-100 tg-action-btn" data-action="start">
            ⬅️ Back to Menu
        </button>
    </div>
</div>
