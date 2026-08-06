<?php
$curl = curl_init();

$data = [
    "contact" => [
        [
            "number" => "923006859611",
            "message" => "Hello from Panel! Test message to verify WhatsApp integration."
        ]
    ]
];

curl_setopt_array($curl, [
    CURLOPT_URL => "https://omnireach.shahabtech.com/api/whatsapp/send",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_HTTPHEADER => [
        "Api-key: e637cd7e-c2bb-406f-ad30-8ae69178e1f6",
        "Content-Type: application/json",
        "Accept: application/json"
    ],
    CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 10.0; Win64; x64)"
]);

$response = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

echo "HTTP CODE: " . $httpCode . "\n";
echo "RESPONSE:\n" . $response . "\n";
