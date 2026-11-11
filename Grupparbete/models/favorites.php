<?php


function isFavorite(PDO $pdo, int $user_id, int $place_id): bool {
    $stmt = $pdo->prepare("
        SELECT 1 FROM favorites
        WHERE user_id = ? AND place_id = ?
    ");
    $stmt->execute([$user_id, $place_id]);
    return $stmt->fetchColumn() !== false;
}

function addFavorite(PDO $pdo, int $user_id, int $place_id): bool {
    
    $stmt = $pdo->prepare("
        SELECT 1 FROM places WHERE place_id = ?
    ");
    $stmt->execute([$place_id]);
    if (!$stmt->fetch()) {
        return false; 
    }

    $stmt = $pdo->prepare("
        INSERT OR IGNORE INTO favorites (user_id, place_id)
        VALUES (?, ?)
    ");
    return $stmt->execute([$user_id, $place_id]);
}

function removeFavorite(PDO $pdo, int $user_id, int $place_id): bool {
    $stmt = $pdo->prepare("
        DELETE FROM favorites
        WHERE user_id = ? AND place_id = ?
    ");
    return $stmt->execute([$user_id, $place_id]);
}

function getFavoritesByUserId(PDO $pdo, int $user_id): array {
    $stmt = $pdo->prepare("
        SELECT
            p.place_id,
            p.place_name,
            p.place_address,
            c.city_name,
            cat.type AS category_name
        FROM favorites f
        JOIN places p ON p.place_id = f.place_id
        JOIN city c ON p.city_id = c.city_id
        LEFT JOIN category cat ON p.category_id = cat.category_id
        WHERE f.user_id = ?
        ORDER BY c.city_name, p.place_name
    ");

    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}