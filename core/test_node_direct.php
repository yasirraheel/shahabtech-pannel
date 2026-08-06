<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$res = \Illuminate\Support\Facades\Http::withHeaders([
    'X-API-Key' => 'dwtolqDXSdoEoAqmZVEvb7yzvxbua65R',
    'Content-Type' => 'application/json',
])->post('http://127.0.0.1:3001/messages/send', [
    'sessionId' => 'Mohsin',
    'receiver' => '923006859611',
    'message' => [
        'text' => 'DIRECT NODE WHATSAPP TEST TO 923006859611 FROM WEMATE ADMIN ALERT'
    ]
]);

echo "NODE RESPONSE STATUS: " . $res->status() . "\n";
echo "NODE RESPONSE BODY: " . $res->body() . "\n";
