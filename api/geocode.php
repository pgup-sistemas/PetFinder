<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Método não permitido. Use GET.'
    ]);
    exit;
}

$latParam = $_GET['lat'] ?? null;
$lngParam = $_GET['lng'] ?? null;

if ($latParam === null || $lngParam === null) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Informe latitude e longitude.'
    ]);
    exit;
}

$lat = filter_var($latParam, FILTER_VALIDATE_FLOAT);
$lng = filter_var($lngParam, FILTER_VALIDATE_FLOAT);

if ($lat === false || $lng === false) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Latitude ou longitude inválidas.'
    ]);
    exit;
}

if (empty(GOOGLE_MAPS_API_KEY) || GOOGLE_MAPS_API_KEY === 'your-google-maps-api-key') {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Chave da Google Maps API não configurada.'
    ]);
    exit;
}

$query = http_build_query([
    'latlng' => sprintf('%.8f,%.8f', $lat, $lng),
    'key' => GOOGLE_MAPS_API_KEY,
    'language' => 'pt-BR'
]);

$url = 'https://maps.googleapis.com/maps/api/geocode/json?' . $query;

try {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 8,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);

    $response = curl_exec($ch);

    if ($response === false) {
        throw new RuntimeException('Erro ao consultar Google Geocoding: ' . curl_error($ch));
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode >= 400) {
        http_response_code(502);
        echo json_encode([
            'success' => false,
            'message' => 'Serviço de geocodificação indisponível no momento.'
        ]);
        exit;
    }

    $payload = json_decode($response, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new RuntimeException('Resposta inválida do Google Maps.');
    }

    if (($payload['status'] ?? '') !== 'OK' || empty($payload['results'])) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Endereço não encontrado para as coordenadas informadas.'
        ]);
        exit;
    }

    $result = $payload['results'][0];
    $components = $result['address_components'] ?? [];

    $addressData = [
        'logradouro' => '',
        'bairro' => '',
        'cidade' => '',
        'estado' => '',
        'cep' => '',
        'pais' => '',
        'latitude' => $lat,
        'longitude' => $lng
    ];

    foreach ($components as $component) {
        if (in_array('route', $component['types'], true)) {
            $addressData['logradouro'] = $component['long_name'];
        }
        if (in_array('sublocality', $component['types'], true) || in_array('administrative_area_level_3', $component['types'], true)) {
            $addressData['bairro'] = $component['long_name'];
        }
        if (in_array('administrative_area_level_2', $component['types'], true) || in_array('locality', $component['types'], true)) {
            $addressData['cidade'] = $component['long_name'];
        }
        if (in_array('administrative_area_level_1', $component['types'], true)) {
            $addressData['estado'] = $component['short_name'];
        }
        if (in_array('postal_code', $component['types'], true)) {
            $addressData['cep'] = $component['long_name'];
        }
        if (in_array('country', $component['types'], true)) {
            $addressData['pais'] = $component['long_name'];
        }
    }

    echo json_encode([
        'success' => true,
        'data' => $addressData,
        'raw' => $result
    ]);
} catch (Throwable $e) {
    error_log('[API Geocode] ' . $e->getMessage());
    http_response_code(502);
    echo json_encode([
        'success' => false,
        'message' => 'Não foi possível obter o endereço. Tente novamente mais tarde.'
    ]);
}
