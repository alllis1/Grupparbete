<?php
require_once __DIR__ . '/../include/bootstrap.php';
require_once __DIR__ . '/../include/auth.php';
require_once __DIR__ . '/../models/favorites.php';

if (!is_logged_in()) {
    header('Location: /Grupparbete/views/login.php');
    exit;
}

$pdo = getDB();
$user_id = get_user_id();

$favorites_flat = getFavoritesByUserId($pdo, $user_id);


$favorites_by_city = [];
foreach ($favorites_flat as $place) {
    $favorites_by_city[$place['city_name']][] = $place;
}

include 'partials/header.php';
?>

<h1 class="page-title">Mina Favoriter</h1>

<?php if (empty($favorites_by_city)): ?>

    <div class="card" style="text-align: center; padding: 40px;">
        <p>Du har inte sparat några favoriter än.</p>
        <p>Klicka på hjärtat ♡ bredvid en restaurang för att lägga till den här.</p>
    </div>

<?php else: ?>

    <?php foreach ($favorites_by_city as $city_name => $places): ?>
        <h2 style="margin-top: 30px; border-bottom: 2px solid var(--section-pink); padding-bottom: 10px;">
            <?= htmlspecialchars($city_name) ?>
        </h2>
        <div class="places-grid">
            <?php foreach ($places as $place): ?>
                <div class="place-card">
                    <a href="/Grupparbete/restaurant.php?name=<?= urlencode($place['place_name']) ?>&city=<?= urlencode($place['city_name']) ?>&address=<?= urlencode($place['place_address'] ?? '') ?>">
                        <h2><?= htmlspecialchars($place['place_name']) ?></h2>
                        <p><?= htmlspecialchars($place['place_address'] ?? 'Adress saknas') ?></p>
                        <?php if (!empty($place['category_name'])): ?>
                            <span class="category-tag" style="background-color: #eee; padding: 4px 8px; border-radius: 8px; font-size: 0.8em;">
                                <?= htmlspecialchars($place['category_name']) ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>

<?php endif; ?>

<?php include 'partials/footer.php'; ?>