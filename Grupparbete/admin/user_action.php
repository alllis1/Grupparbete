<?php
require_once __DIR__ . '/../include/bootstrap.php';
require_once __DIR__ . '/../include/auth.php';


if (!is_admin()) {
    header('Location: /Grupparbete/views/index.php');
    exit;
}

$action = $_GET['action'] ?? null;
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$current_user_id = get_user_id();

if (!$action || $user_id <= 0) {
    header('Location: manage_users.php');
    exit;
}


if ($user_id === $current_user_id) {
    header('Location: manage_users.php?error=self_delete');
    exit;
}

$pdo = getDB();

if ($action === 'delete') {

    
    $stmt = $pdo->prepare("
        DELETE FROM users 
        WHERE user_id = ?
    ");

    $stmt->execute([$user_id]);
}


header('Location: manage_users.php?success=true');
exit;