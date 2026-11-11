<?php


require_once __DIR__ . '/../include/db.php';


function getReviewsByCity(PDO $pdo, string $city): array {

    $stmt = $pdo->prepare("
        SELECT
            r.review_id,
            r.review_text,
            r.rating,
            r.image_path,
            r.created_at,
            p.place_name,
            c.city_name,
            cat.type AS category_name,
            u.user_name
        FROM reviews r
        JOIN places p ON r.place_id = p.place_id
        JOIN city c ON p.city_id = c.city_id
        LEFT JOIN category cat ON p.category_id = cat.category_id
        JOIN users u ON r.user_id = u.user_id
        WHERE c.city_name = ?
        ORDER BY r.created_at DESC
    ");

    $stmt->execute([$city]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


function createReview(
    PDO $pdo,
    string $review_text,
    int $rating,
    ?string $image_path,
    int $user_id,
    int $place_id
): bool {

    $stmt = $pdo->prepare("
        INSERT INTO reviews (review_text, rating, image_path, user_id, place_id)
        VALUES (?, ?, ?, ?, ?)
    ");

    return $stmt->execute([$review_text, $rating, $image_path, $user_id, $place_id]);
}


function getReviewsByPlaceId(PDO $pdo, int $place_id): array {

    $stmt = $pdo->prepare("
        SELECT
            r.review_text,
            r.rating,
            r.image_path,
            r.created_at,
            u.user_name
        FROM reviews r
        JOIN users u ON r.user_id = u.user_id
        WHERE r.place_id = ?
        ORDER BY r.created_at DESC
    ");

    $stmt->execute([$place_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
