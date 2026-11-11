
<?php
set_time_limit(300);

require_once __DIR__ . '/include/bootstrap.php';


$apiKey = GEOAPIFY_API_KEY;

if (empty($apiKey)) {
    die("Fel: API-nyckel saknas.");
}

echo "<h1>Uppdaterar restaurangkoordinater...</h1>";
echo "<p>Detta kan ta en stund. Ladda inte om sidan.</p><pre>";
ob_flush();
flush();

try {
    $pdo = getDB();

    
    $stmt = $pdo->prepare("
        SELECT 
            p.place_id,
            p.place_name,
            p.place_address,
            c.city_name
        FROM places p
        JOIN city c ON p.city_id = c.city_id
        WHERE p.latitude IS NULL OR p.longitude IS NULL
    ");
    $stmt->execute();
    $placesToUpdate = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($placesToUpdate)) {
        echo "\n<b>Klart! Alla restauranger har redan koordinater.</b>";
        exit;
    }

    echo "Hittade " . count($placesToUpdate) . " restauranger att uppdatera.\n\n";

    $updatedCount = 0;

    foreach ($placesToUpdate as $place) {

        $placeId = $place['place_id'];

        
        $fullAddress = $place['place_address'] . ', ' . $place['city_name'] . ', Sweden';

        echo "Söker: '{$place['place_name']}' på '{$fullAddress}'... ";

        $apiUrl = "https://api.geoapify.com/v1/geocode/search?text=" 
                  . urlencode($fullAddress) 
                  . "&apiKey=" . $apiKey;

        $response = @file_get_contents($apiUrl);

        if (!$response) {
            echo "<span style='color:red;'>FEL (API misslyckades)</span>\n";
            continue;
        }

        $data = json_decode($response, true);

        if ($data && !empty($data['features'])) {

            $longitude = $data['features'][0]['geometry']['coordinates'][0];
            $latitude  = $data['features'][0]['geometry']['coordinates'][1];

            $updateStmt = $pdo->prepare("
                UPDATE places 
                SET latitude = ?, longitude = ? 
                WHERE place_id = ?
            ");
            $updateStmt->execute([$latitude, $longitude, $placeId]);

            echo "<span style='color:green;'>OK</span>\n";
            $updatedCount++;

        } else {
            echo "<span style='color:red;'>FEL (ingen träff)</span>\n";
        }

        ob_flush();
        flush();

        usleep(250000); 
    }

    echo "\n<b>Klart! Uppdaterade $updatedCount av " . count($placesToUpdate) . " restauranger.</b>";

} catch (Exception $e) {
    die("<b>Fel:</b> " . $e->getMessage());
}

echo "</pre>";

