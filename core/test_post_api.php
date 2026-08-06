<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$res = \Illuminate\Support\Facades\Http::withHeaders([
    'Api-key' => 'e637cd7e-c2bb-406f-ad30-8ae69178e1f6',
    'Content-Type' => 'application/json',
    'Accept' => 'application/json'
])->post('https://omnireach.shahabtech.com/api/whatsapp/send', [
    'contact' => [
        [
            'number' => '923006859611',
            'message' => 'TEST WHATSAPP DIRECT MESSAGE DISPATCH'
        ]
    ]
]);

echo "API STATUS: " . $res->status() . "\n";
echo "API BODY: " . $res->body() . "\n";
