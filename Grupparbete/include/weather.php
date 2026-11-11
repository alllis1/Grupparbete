<?php

require_once __DIR__ . '/config.php';

function getWeatherForCity($city) {

    
    $apiKey = OPENWEATHER_API_KEY;

    $cityMap = [
        'göteborg' => 'Gothenburg',
        'malmö' => 'Malmo',
        'stockholm' => 'Stockholm',
        'uppsala' => 'Uppsala'
    ];

    $cityLower = mb_strtolower($city);
    $cityName = $cityMap[$cityLower] ?? $city;

    
    $url = "https://api.openweathermap.org/data/2.5/weather?q={$cityName},SE&appid={$apiKey}&units=metric&lang=sv";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    curl_close($ch);

    if (!$response) {
        return getDefaultWeather();
    }

    $data = json_decode($response, true);

    if (!$data || !isset($data['main']['temp'])) {
        return getDefaultWeather();
    }

    $temp = round($data['main']['temp']);
    $weatherId = $data['weather'][0]['id'] ?? 800;
    $weatherMain = $data['weather'][0]['main'] ?? 'Clear';
    $weatherDesc = $data['weather'][0]['description'] ?? 'klart väder';

    $emoji = getWeatherEmoji($weatherId);
    $weatherType = getWeatherType($temp, $weatherId);

    return [
        'temp' => $temp,
        'emoji' => $emoji,
        'main' => $weatherMain,
        'description' => $weatherDesc,
        'weatherType' => $weatherType,
        'raw' => $data
    ];
}

function getDefaultWeather() {
    return [
        'temp' => 15,
        'emoji' => '🌤️',
        'main' => 'Okänt väder',
        'description' => 'Kunde inte hämta väderdata',
        'weatherType' => 'outdoor',
        'raw' => []
    ];
}

function getWeatherEmoji($weatherId) {

    if ($weatherId >= 200 && $weatherId < 300) return '⛈️';
    if ($weatherId >= 300 && $weatherId < 400) return '🌧️';
    if ($weatherId >= 500 && $weatherId < 600) return '🌧️';
    if ($weatherId >= 600 && $weatherId < 700) return '❄️';
    if ($weatherId >= 700 && $weatherId < 800) return '🌫️';

    if ($weatherId === 800) return '☀️';
    if ($weatherId === 801) return '🌤️';
    if ($weatherId === 802) return '⛅';
    if ($weatherId >= 803) return '☁️';

    return '🌤️';
}

function getWeatherType($temp, $weatherId) {

    if ($temp < 10) return 'indoor';

    if ($weatherId >= 200 && $weatherId < 600) return 'indoor';
    if ($weatherId >= 600 && $weatherId < 700) return 'indoor';

    if ($weatherId >= 803) return 'indoor';

    return 'outdoor';
}

function getWeatherRecommendationText($weatherType, $temp, $emoji) {

    if ($weatherType === 'indoor') {
        return "Det är {$temp}°C och inte det bästa vädret för uteservering. Här är mysiga inomhusrestauranger!";
    }

    return "Det är {$temp}°C och perfekt väder för uteservering!";
}