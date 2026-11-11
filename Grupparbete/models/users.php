<?php
require_once __DIR__ . '/../include/bootstrap.php';

function getUserByEmail(PDO $pdo, string $email): ?array {
    $stmt = $pdo->prepare("
        SELECT user_id, user_name, email, password_hash, role
        FROM users 
        WHERE email = ?
    ");
    $stmt->execute([$email]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

function createUser(PDO $pdo, string $username, string $email, string $hash): bool {
    $stmt = $pdo->prepare("
        INSERT INTO users (user_name, email, password_hash)
        VALUES (?, ?, ?)
    ");
    return $stmt->execute([$username, $email, $hash]);
}
