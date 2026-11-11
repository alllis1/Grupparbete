<?php
require_once __DIR__ . '/../include/bootstrap.php';

if (empty($_SESSION['user_id'])) {
  header('Location: /Grupparbete/index.php');
  exit;
}

$cities = [
  ['name' => 'Stockholm', 'image' => '../images/stockholm.jpg'],
  ['name' => 'Göteborg', 'image' => '../images/goteborg.jpg'],
  ['name' => 'Malmö', 'image' => '../images/malmo.jpg'],
  ['name' => 'Uppsala', 'image' => '../images/uppsala.jpg'],
];

include 'partials/header.php';
?>

<h1 class="page-title">Välj en stad att utforska</h1>

<p class="page-subtitle">
  Upptäck restauranger, caféer och barer
</p>

<div class="city-grid">

<?php foreach ($cities as $c): ?>

    <div class="city-card">

        
        <a href="/Grupparbete/views/city.php?city=<?= urlencode($c['name']) ?>">

            <img src="<?= htmlspecialchars($c['image']) ?>" alt="<?= htmlspecialchars($c['name']) ?>">

            <div class="city-card-overlay">
                <h2><?= htmlspecialchars($c['name']) ?></h2>
            </div>

        </a>

    </div>

<?php endforeach; ?>

</div>

<?php include 'partials/footer.php'; ?>
