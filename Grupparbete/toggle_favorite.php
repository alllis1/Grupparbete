<?php
require_once __DIR__ . '/include/bootstrap.php';
require_once __DIR__ . '/include/auth.php';
require_once __DIR__ . '/models/favorites.php';

header('Content-Type: application/json');


if (!is_logged_in()) {
    http_response_code(401); 
    echo json_encode([
        'status' => 'error',
        'message' => 'Du måste vara inloggad för att kunna spara favoriter.'
    ]);
    exit;
}

try {
    
    $data = json_decode(file_get_contents('php://input'), true);
    $place_id = isset($data['place_id']) ? (int)$data['place_id'] : 0;

    
    if ($place_id <= 0) {
        http_response_code(400); 
        echo json_encode([
            'status' => 'error',
            'message' => 'Ogiltigt place_id'
        ]);
        exit;
    }

    $pdo = getDB();
    $user_id = get_user_id();

    
    $stmt = $pdo->prepare("SELECT 1 FROM places WHERE place_id = ?");
    $stmt->execute([$place_id]);
    if (!$stmt->fetch()) {
        http_response_code(404); 
        echo json_encode([
            'status' => 'error',
            'message' => 'Platsen finns inte i databasen'
        ]);
        exit;
    }

    
    $is_currently_favorite = isFavorite($pdo, $user_id, $place_id);

    
    if ($is_currently_favorite) {
        removeFavorite($pdo, $user_id, $place_id);
        echo json_encode([
            'status' => 'success',
            'isFavorite' => false
        ]);
    } else {
        addFavorite($pdo, $user_id, $place_id);
        echo json_encode([
            'status' => 'success',
            'isFavorite' => true
        ]);
    }
    exit;

} catch (Exception $e) {
    
    http_response_code(500); 
    error_log($e->getMessage()); 
    echo json_encode([
        'status' => 'error',
        'message' => 'Ett serverfel uppstod. Försök igen senare.'
    ]);
}