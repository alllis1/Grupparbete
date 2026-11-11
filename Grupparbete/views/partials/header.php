<?php
require_once __DIR__ . '/../../include/auth.php';
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <title>Travelbite</title>

    <link rel="stylesheet" href="/Grupparbete/css/kod.css?v=5">

    <script src="/Grupparbete/js/main.js" defer></script>
</head>
<body>

<header class="main-header">
    <div class="container">
        
        <a href="/Grupparbete/index.php" class="logo">Travelbite</a>

               <nav class="main-nav">
           <?php if (is_logged_in()): ?>

               <?php if (is_admin()): ?>
                   <a href="/Grupparbete/admin/manage_reviews.php" style="font-weight: bold; color: var(--accent-deep); background: var(--section-pink);">Adminpanel</a>
               <?php endif; ?>

               <a href="/Grupparbete/views/index.php">Hem</a>
               <a href="/Grupparbete/views/favorites.php">Mina Favoriter</a>
               <a href="/Grupparbete/logout_process.php">Logga ut</a>
           <?php else: ?>
                <a href="/Grupparbete/views/login.php">Logga in</a>
                <a href="/Grupparbete/views/register.php">Registrera</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main class="container">