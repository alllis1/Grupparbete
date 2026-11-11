<?php
require_once __DIR__ . '/include/bootstrap.php';
require_once __DIR__ . '/models/users.php';


$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');


if ($email === '' || $password === '') {
    header("Location: /Grupparbete/views/login.php?error=empty_fields");
    exit;
}


if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: /Grupparbete/views/login.php?error=invalid_email");
    exit;
}

$pdo = getDB();


$user = getUserByEmail($pdo, $email);

if (!$user) {
    header("Location: /Grupparbete/views/login.php?error=user_not_found");
    exit;
}


if (!password_verify($password, $user['password_hash'])) {
    header("Location: /Grupparbete/views/login.php?error=wrong_password");
    exit;
}


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


$_SESSION['user_id'] = $user['user_id'];
$_SESSION['user_name'] = $user['user_name'];
$_SESSION['role'] = $user['role'] ?? 'user';


header('Location: /Grupparbete/views/index.php');
exit;
