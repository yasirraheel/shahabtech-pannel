<?php
$numbers = ["923006859611", "+923006859611", "8801712345678"];

foreach ($numbers as $num) {
    $curl = curl_init();
    $data = [
        "contact" => [
            [
                "number" => $num,
                "message" => "Test message for number format $num"
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

    echo "NUMBER: $num | CODE: $code | RESP: $response\n";
}
