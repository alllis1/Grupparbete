
<?php
require_once __DIR__ . '/include/bootstrap.php';
require_once __DIR__ . '/models/places.php';

echo "<h1>Synkronisera alla städer</h1>";

$pdo = getDB();


$cities = ['Stockholm', 'Göteborg', 'Malmö', 'Uppsala'];

foreach ($cities as $city) {

    echo "<p>Synkroniserar: <strong>$city</strong>...</p>";

    
    syncPlacesFromGeoapify($pdo, $city);

    
    $stmtCity = $pdo->prepare("
        SELECT city_id FROM city WHERE city_name = ?
    ");
    $stmtCity->execute([$city]);
    $city_id = $stmtCity->fetchColumn();

    if ($city_id) {
        
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM places WHERE city_id = ?
        ");
        $stmt->execute([$city_id]);
        $count = $stmt->fetchColumn();

        echo "<p style='color:green;'>✅ $city: $count platser i databasen</p>";
    } else {
        echo "<p style='color:red;'>⚠️ Kunde inte hitta city_id för $city</p>";
    }

    echo "<hr>";
}

echo "<h2>Klart! Alla städer synkroniserade.</h2>";
