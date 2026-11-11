<?php
require_once __DIR__ . '/include/bootstrap.php';
require_once __DIR__ . '/models/places.php'; 

echo "<h1>Importerar platser från Geoapify...</h1>";
echo "<p>Detta kan ta en stund. Ladda inte om sidan.</p>";
flush();


$cities = ['Stockholm', 'Göteborg', 'Malmö', 'Uppsala'];
$totalPlacesAdded = 0;

try {
    $pdo = getDB();
    $pdo->exec('DELETE FROM places');
    echo "<p>Rensat all gammal data från 'places'-tabellen.</p>";

    $sql = 'INSERT OR IGNORE INTO places (place_name, place_address, latitude, longitude, city_id, category_id)
            VALUES (:place_name, :place_address, :latitude, :longitude, :city_id, :category_id)';
    $stmt = $pdo->prepare($sql);

    foreach ($cities as $cityName) {
        echo "<h3>Hämtar platser för {$cityName}...</h3>";
        flush();

        
        $city_id = getCityIdByName($pdo, $cityName);
        if (!$city_id) {
            echo "<p style='color:red;'>Kunde inte hitta city_id för {$cityName}. Hoppar över.</p>";
            continue;
        }

        
        $placesFromApi = getPlacesFromGeoapify($cityName);

        if (empty($placesFromApi)) {
            echo "<p>Inga platser hittades från API för {$cityName}.</p>";
            continue;
        }

        echo "<ul>";
        foreach ($placesFromApi as $feature) {
            $props = $feature['properties'];
            $place_name = $props['name'] ?? null;

            if (!$place_name) {
                continue; 
            }

           
            $api_category = 'Okänd';
            if (!empty($props['categories'])) {
                if (in_array('catering.cafe', $props['categories'])) {
                    $api_category = 'Café';
                } elseif (in_array('catering.bar', $props['categories'])) {
                    $api_category = 'Bar';
                } elseif (in_array('catering.restaurant', $props['categories'])) {
                    $api_category = 'Restaurang';
                }
            }
            
            
            $category_id = getCategoryIdByType($pdo, $api_category);

            
            $place_address = $props['address_line2'] ?? 'Adress saknas';
            $latitude = $props['lat'] ?? null;
            $longitude = $props['lon'] ?? null;

            echo "<li>Sparar: " . htmlspecialchars($place_name) . " (Kategori: <b>" . $api_category . "</b>)</li>";
            flush();

            $stmt->bindValue(':place_name', $place_name);
            $stmt->bindValue(':place_address', $place_address);
            $stmt->bindValue(':latitude', $latitude);
            $stmt->bindValue(':longitude', $longitude);
            $stmt->bindValue(':city_id', $city_id);
            $stmt->bindValue(':category_id', $category_id);

            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $totalPlacesAdded++;
            }
        }
        echo "</ul>";
    }

    echo "<h2>✅ Klar!</h2>";
    echo "<p>Databasen är nu uppdaterad med totalt <strong>{$totalPlacesAdded}</strong> nya platser.</p>";

} catch (Exception $e) {
    die("<b>Fel uppstod:</b> " . $e->getMessage());
}