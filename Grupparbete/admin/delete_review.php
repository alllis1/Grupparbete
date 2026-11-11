<?php
require_once __DIR__ . '/../include/bootstrap.php';
require_once __DIR__ . '/../include/auth.php';


if (!is_admin()) {
    header('Location: /Grupparbete/views/index.php');
    exit;
}

$review_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($review_id > 0) {

    $pdo = getDB();

    $stmt = $pdo->prepare("
        DELETE FROM reviews 
        WHERE review_id = ?
    ");

    $stmt->execute([$review_id]);
}


header('Location: manage_reviews.php?deleted=true');
exit;