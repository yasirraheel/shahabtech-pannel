<?php
$curl = curl_init();

$data = [
    "contact" => [
        [
            "number" => "923006859611",
            "message" => "Hello from Panel! Test message to 923006859611."
        ]
    ]
];

curl_setopt_array($curl, [
    CURLOPT_URL => "https://omnireach.shahabtech.com/api/whatsapp/send",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_HTTPHEADER => [
        "Api-key: e637cd7e-c2bb-406f-ad30-8ae69178e1f6",
        "Content-Type: application/json",
        "Accept: application/json"
    ],
]);

$response = curl_exec($curl);
$info = curl_getinfo($curl);
curl_close($curl);

echo "HTTP CODE: " . $info['http_code'] . "\n";
echo "RESPONSE: " . $response . "\n";
