<?php
header('Content-Type: application/json');

$api_key = "568f1a71a258527138ddf23a2eba5945";
$base_url = "https://api.rajaongkir.com/starter";

// Validasi input
if (empty($_GET['city_id'])) {
    echo json_encode(['error' => 'City ID is required']);
    exit;
}

if (empty($_GET['courier'])) {
    echo json_encode(['error' => 'Courier is required']);
    exit;
}

$origin = "151"; // ID kota asal (sesuaikan dengan kota pengiriman Anda)
$destination = $_GET['city_id'];
$weight = 1000; // Berat dalam gram (sesuaikan dengan berat produk Anda)
$courier = $_GET['courier'];

$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => "$base_url/cost",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => "origin=$origin&destination=$destination&weight=$weight&courier=$courier",
    CURLOPT_HTTPHEADER => array(
        "content-type: application/x-www-form-urlencoded",
        "key: $api_key"
    ),
));

$response = curl_exec($curl);
$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
$err = curl_error($curl);
curl_close($curl);

// Debug: Log response
error_log("API Response Code: " . $http_code);
error_log("API Response: " . $response);

if ($err) {
    echo json_encode(['error' => 'Curl Error: ' . $err]);
    exit;
}

if ($http_code !== 200) {
    echo json_encode(['error' => 'API Error: HTTP Code ' . $http_code]);
    exit;
}

$response_data = json_decode($response, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['error' => 'Invalid JSON response: ' . $response]);
    exit;
}

// Pastikan response memiliki format yang benar
if (!isset($response_data['rajaongkir']['results'][0]['costs'])) {
    echo json_encode(['error' => 'Invalid shipping cost data structure']);
    exit;
}

// Transform response untuk memastikan format yang konsisten
$transformed_response = [
    'rajaongkir' => [
        'results' => [
            [
                'costs' => array_map(function ($cost) {
                    return [
                        'service' => $cost['service'],
                        'description' => $cost['description'],
                        'cost' => [
                            [
                                'value' => $cost['cost'][0]['value'],
                                'etd' => $cost['cost'][0]['etd'],
                                'note' => $cost['cost'][0]['note'] ?? ''
                            ]
                        ]
                    ];
                }, $response_data['rajaongkir']['results'][0]['costs'])
            ]
        ]
    ]
];

echo json_encode($transformed_response);
