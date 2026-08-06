<?php
$gateways = [
    "3ZZ2UPim-YVwgoqdzD3h0Lr-KiIUPiEI", // Gateway ID 5
    "3alunMWv-uQVihZYW1aDB4l-PWQysN1V"  // Gateway ID 8
];

foreach ($gateways as $gw) {
    $curl = curl_init();
    $data = [
        "contact" => [
            [
                "number" => "923006859611",
                "message" => "Hello from Panel! Test WhatsApp message using gateway $gw.",
                "gateway_identifier" => $gw
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
            "Content-Type: application/json"
        ],
    ]);

    $response = curl_exec($curl);
    $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    echo "GW: $gw | CODE: $code | RESP: $response\n";
}
