
<?php
// models/places.php

if (!defined('GEOAPIFY_API_KEY')) {
    throw new Exception("Geoapify API-nyckel saknas.");
}


function getFilteredPlaces(PDO $pdo, int $city_id, ?string $search = null, ?string $filter = null): array {

    
    $sql = "SELECT
                p.*,
                c.city_name,
                cat.type AS category_name,
                AVG(r.rating) AS avg_rating,         
                COUNT(r.review_id) AS review_count  
            FROM places p
            JOIN city c ON p.city_id = c.city_id
            LEFT JOIN category cat ON p.category_id = cat.category_id
            LEFT JOIN reviews r ON p.place_id = r.place_id 
            WHERE p.city_id = :city_id";

    $params = ['city_id' => $city_id];

    
    if ($search) {
        $sql .= " AND p.place_name LIKE :search";
        $params['search'] = '%' . $search . '%';
    }

    if ($filter) {
        
        $f = strtolower($filter);
        $keywords = [];
        switch ($f) {
            case 'restaurant':
            case 'restaurang':
                $keywords = ['restaurang', 'restaurant'];
                break;
            case 'cafe':
            case 'café':
                $keywords = ['café', 'cafe'];
                break;
            case 'bar':
                $keywords = ['bar'];
                break;
            default:
                $keywords = [$f];
        }
        $likes = [];
        foreach ($keywords as $i => $kw) {
            $likes[] = "LOWER(cat.type) LIKE :cat{$i}";
            $params["cat{$i}"] = '%' . strtolower($kw) . '%';
        }
        $sql .= " AND (" . implode(" OR ", $likes) . ")";
    }

    
    $sql .= " GROUP BY p.place_id, c.city_name, cat.type";
    
    
    $sql .= " ORDER BY avg_rating DESC, review_count DESC";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database query failed: " . $e->getMessage());
        return [];
    }
}



function getPlaceByNameCity(PDO $pdo, string $name, int $city_id) {
    $stmt = $pdo->prepare("
        SELECT * FROM places 
        WHERE place_name = ? AND city_id = ?
    ");
    $stmt->execute([$name, $city_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}



function getOrCreatePlace(PDO $pdo, string $name, int $city_id, ?string $address, ?int $category_id): int {

    $place = getPlaceByNameCity($pdo, $name, $city_id);

    if ($place) {
        return (int)$place['place_id'];
    }

    $stmt = $pdo->prepare("
        INSERT INTO places (place_name, city_id, place_address, category_id) 
        VALUES (?, ?, ?, ?)
    ");

    $stmt->execute([$name, $city_id, $address, $category_id]);

    return (int)$pdo->lastInsertId();
}



function getPlacesFromGeoapify(string $city): array {

    $city_coordinates = [
        'Stockholm' => '59.3293,18.0686',
        'Göteborg' => '57.7089,11.9746',
        'Malmö' => '55.6049,13.0038',
        'Uppsala' => '59.8586,17.6389'
    ];

    if (!isset($city_coordinates[$city])) {
        return [];
    }

    $coords = $city_coordinates[$city];
    $apiKey = GEOAPIFY_API_KEY;

    $categories = "catering.restaurant,catering.cafe,catering.bar";

    $url = "https://api.geoapify.com/v2/places" .
           "?categories=" . urlencode($categories) .
           "&filter=circle:" . explode(',', $coords)[1] . "," . explode(',', $coords)[0] . ",5000" .
           "&limit=50" .
           "&apiKey=" . $apiKey;

    $resp = @file_get_contents($url);

    if ($resp === false) {
        return [];
    }

    $data = json_decode($resp, true);

    return $data['features'] ?? [];
}



function syncPlacesFromGeoapify(PDO $pdo, string $city): void {

    $city_id = getCityIdByName($pdo, $city);
    if (!$city_id) return;

    $placesFromApi = getPlacesFromGeoapify($city);

    $stmt = $pdo->prepare("
        INSERT OR IGNORE INTO places 
        (place_name, city_id, place_address, category_id) 
        VALUES (:name, :city_id, :address, :category_id)
    ");

    foreach ($placesFromApi as $feature) {

        $props = $feature['properties'];
        $name = $props['name'] ?? null;

        if (!$name) continue;

        
        $category = 'Okänd';

        if (!empty($props['categories'])) {
            foreach ($props['categories'] as $cat) {
                if (str_starts_with($cat, 'catering.')) {
                    $category = str_replace('catering.', '', $cat);
                    break;
                }
            }
        }

        
        switch (strtolower($category)) {
            case 'restaurant':
                $category = 'Restaurang';
                break;
            case 'cafe':
                $category = 'Café';
                break;
            case 'bar':
                $category = 'Bar';
                break;
        }

        $category_id = getCategoryIdByType($pdo, $category);

        $stmt->execute([
            ':name' => $name,
            ':city_id' => $city_id,
            ':address' => $props['address_line2'] ?? 'Adress saknas',
            ':category_id' => $category_id
        ]);
    }
}


function getCategoryIdByType(PDO $pdo, string $type): ?int {
    $stmt = $pdo->prepare("
        SELECT category_id 
        FROM category 
        WHERE type = ?
    ");
    $stmt->execute([$type]);

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return $result ? (int)$result['category_id'] : null;
}


function getCityIdByName(PDO $pdo, string $city_name): ?int {
    $stmt = $pdo->prepare("
        SELECT city_id 
        FROM city 
        WHERE city_name = ?
    ");
    $stmt->execute([$city_name]);

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return $result ? (int)$result['city_id'] : null;
}
