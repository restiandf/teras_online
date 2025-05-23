<?php
header('Content-Type: application/json');

$api_key = "568f1a71a258527138ddf23a2eba5945";
$base_url = "https://api.rajaongkir.com/starter";
$province_id = $_GET['province_id'];

// Debug: Log request
error_log("Requesting cities for province_id: " . $province_id);

if (empty($province_id)) {
    echo json_encode(['error' => 'Province ID is required']);
    exit;
}

$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => "$base_url/city?province=$province_id",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
    CURLOPT_HTTPHEADER => array(
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

// Transform response to match expected format
$transformed_response = [
    'rajaongkir' => [
        'results' => array_map(function ($city) {
            return [
                'city_id' => $city['city_id'],
                'type' => $city['type'],
                'city_name' => $city['city_name']
            ];
        }, $response_data['rajaongkir']['results'])
    ]
];

echo json_encode($transformed_response);
