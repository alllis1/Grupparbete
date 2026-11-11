<?php

require_once __DIR__ . '/config.php';

function getPlacesFromGeoapify(string $city, array $categories, int $limit = 20): array
{
    
    $apiKey = GEOAPIFY_API_KEY;

    $encodedCity = urlencode($city);
    $encodedCategories = implode(',', $categories);


    $url = "https://api.geoapify.com/v2/places"
        . "?categories={$encodedCategories}"
        . "&filter=place:pcode:SE-{$encodedCity}"
        . "&limit={$limit}"
        . "&apiKey={$apiKey}";

    $response = @file_get_contents($url);

    if ($response === false) {
        return [];
    }

    $data = json_decode($response, true);

    return $data['features'] ?? [];
}
