
<?php
require_once __DIR__ . '/include/bootstrap.php';
require_once __DIR__ . '/models/users.php';


$username = trim($_POST['user_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');


if ($username === '' || $email === '' || $password === '') {
    die('Alla fält måste fyllas i.');
}


if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die('Ogiltig e-postadress.');
}

if (strlen($password) < 6) {
    die('Lösenordet måste vara minst 6 tecken.');
}

$pdo = getDB();


$stmt = $pdo->prepare("
    SELECT user_id 
    FROM users 
    WHERE email = ?
");
$stmt->execute([$email]);

if ($stmt->fetch()) {
    die('E-post redan registrerad.');
}


$hash = password_hash($password, PASSWORD_DEFAULT);


createUser($pdo, $username, $email, $hash);

header('Location: /Grupparbete/views/login.php?status=registered');
exit;
