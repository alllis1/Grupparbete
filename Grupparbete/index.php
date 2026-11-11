<?php

require_once __DIR__ . '/include/bootstrap.php';


if (!empty($_SESSION['user_id'])) {
    header('Location: views/index.php');
    exit;
}
?>
<?php include 'views/partials/header.php'; ?>

<main style="text-align:center; padding:60px;">
  <h2>Välkommen till Travelbite</h2>
  <p>Registrera konto eller logga in för att se rekommendationer.</p>

  <div style="display:flex; gap:16px; justify-content:center; margin-top:24px;">
    <a class="button" href="views/register.php">Registrera</a>
    <a class="button" href="views/login.php">Logga in</a>
  </div>
</main>

<?php include 'views/partials/footer.php'; ?>