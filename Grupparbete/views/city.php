<?php
require_once __DIR__ . '/../include/bootstrap.php';
require_once __DIR__ . '/../include/auth.php';
require_once __DIR__ . '/../include/weather.php';
require_once __DIR__ . '/../models/places.php';
require_once __DIR__ . '/../models/favorites.php';


$city = $_GET['city'] ?? 'Stockholm';
$city = trim($city);

$pdo = getDB();


$city_id = getCityIdByName($pdo, $city);
if ($city_id === null) {
    echo "Staden hittades inte.";
    exit;
}


$filter = $_GET['type'] ?? null;
$search = $_GET['q'] ?? null;


$user_favorites = [];
if (is_logged_in()) {
    $user_id = get_user_id();
    $fav_places = getFavoritesByUserId($pdo, $user_id);
    $user_favorites = array_column($fav_places, 'place_id');
}


$weather = getWeatherForCity($city);
$weatherType = $weather['weatherType'] ?? 'outdoor';
$temp = $weather['temp'] ?? 20;
$emoji = $weather['emoji'] ?? '🌡️';


$places = getFilteredPlaces($pdo, $city_id, $search, $filter);


if (!$filter && !$search && $weatherType === 'indoor') {
    $places = array_filter($places, function($place) {
        $cat = strtolower($place['category_name'] ?? '');
        return strpos($cat, 'café') !== false ||
               strpos($cat, 'restaurant') !== false ||
               strpos($cat, 'restaurang') !== false ||
               strpos($cat, 'bar') !== false;
    });
}


$recommended = array_slice($places, 0, 6);
$otherPlaces = array_slice($places, 6);


$city_slug = strtolower(str_replace(['å','ä','ö'], ['a','a','o'], $city));

include __DIR__ . '/partials/header.php';
?>


<div class="city-hero" style="background-image: url('../images/<?php echo $city_slug; ?>.jpg');">
    <h1><?php echo htmlspecialchars($city); ?></h1>
</div>


<div class="card soft" style="margin-bottom:30px; padding:24px; text-align:center; background:linear-gradient(135deg,#667eea,#764ba2); color:white;">
    <p style="font-size:4rem;"><?php echo $emoji; ?></p>
    <p style="font-size:3rem; font-weight:700;"><?php echo $temp; ?>°C</p>
    <p><?php echo getWeatherRecommendationText($weatherType, $temp, $emoji); ?></p>
</div>


<div class="card" style="margin-bottom:24px; padding:20px;">
    <form method="get" style="display:flex; gap:12px; flex-wrap:wrap;">
        <input type="hidden" name="city" value="<?= htmlspecialchars($city); ?>">

        <input type="text" name="q" placeholder="Sök restaurang..."
               value="<?= htmlspecialchars($search ?? ''); ?>">

        <select name="type">
            <option value="">Alla typer</option>
            <option value="restaurant" <?= ($filter === 'restaurant') ? 'selected' : ''; ?>>Restaurang</option>
            <option value="cafe" <?= ($filter === 'cafe') ? 'selected' : ''; ?>>Café</option>
            <option value="bar" <?= ($filter === 'bar') ? 'selected' : ''; ?>>Bar</option>
        </select>

        <button type="submit" class="button">Sök</button>
        <a href="?city=<?= urlencode($city); ?>" class="button secondary">Rensa</a>
    </form>
</div>


<?php if (!empty($recommended)): ?>
    <h3>
        <?= $weatherType === 'indoor'
            ? '🏠 Rekommenderade inomhusställen'
            : '☀️ Rekommenderade uteserveringar'; ?>
    </h3>

    <div class="places-grid">
        <?php foreach ($recommended as $place): ?>
            <div class="place-card">

                <?php if (is_logged_in()): ?>
                    <?php $is_fav = in_array($place['place_id'], $user_favorites); ?>
                    <button class="favorite-btn <?= $is_fav ? 'active' : '' ?>"
                            data-place-id="<?= $place['place_id']; ?>"
                            onclick="toggleFavorite(this)">
                        <?= $is_fav ? '♥' : '♡'; ?>
                    </button>
                <?php endif; ?>

                
                <a href="/Grupparbete/restaurant.php?name=<?= urlencode($place['place_name']) ?>&city=<?= urlencode($city) ?>&address=<?= urlencode($place['place_address'] ?? '') ?>">

    <h2><?= htmlspecialchars($place['place_name']) ?></h2>
    <p><?= htmlspecialchars($place['place_address'] ?? 'Ingen adress') ?></p>

    
    <div class="rating-display" style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #f0f0f0;">
        <?php if ($place['review_count'] > 0): ?>
            <strong>⭐ <?= number_format($place['avg_rating'], 1) ?> / 5</strong>
            <span style="color: #666;">(<?= $place['review_count'] ?> recensioner)</span>
        <?php else: ?>
            <span style="color: #888;">Inga betyg än</span>
        <?php endif; ?>
    </div>
    

    <?php if (!empty($place['category_name'])): ?>
        <span class="category-tag" style="display: inline-block; margin-top: 10px; background: #eee; padding: 4px 8px; border-radius: 8px; font-size: 0.8em;">
            <?= htmlspecialchars($place['category_name']) ?>
        </span>
    <?php endif; ?>
</a>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>


<?php if (!empty($otherPlaces)): ?>
    <h3>Fler restauranger i <?= htmlspecialchars($city); ?></h3>

    <div class="places-grid">
        <?php foreach ($otherPlaces as $place): ?>
            <div class="place-card">

                <?php if (is_logged_in()): ?>
                    <?php $is_fav = in_array($place['place_id'], $user_favorites); ?>
                    <button class="favorite-btn <?= $is_fav ? 'active' : '' ?>"
                            data-place-id="<?= $place['place_id']; ?>"
                            onclick="toggleFavorite(this)">
                        <?= $is_fav ? '♥' : '♡'; ?>
                    </button>
                <?php endif; ?>

                
            <a href="/Grupparbete/restaurant.php?name=<?= urlencode($place['place_name']) ?>&city=<?= urlencode($city) ?>&address=<?= urlencode($place['place_address'] ?? '') ?>">

    <h2><?= htmlspecialchars($place['place_name']) ?></h2>
    <p><?= htmlspecialchars($place['place_address'] ?? 'Ingen adress') ?></p>

    
    <div class="rating-display" style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #f0f0f0;">
        <?php if ($place['review_count'] > 0): ?>
            <strong>⭐ <?= number_format($place['avg_rating'], 1) ?> / 5</strong>
            <span style="color: #666;">(<?= $place['review_count'] ?> recensioner)</span>
        <?php else: ?>
            <span style="color: #888;">Inga betyg än</span>
        <?php endif; ?>
    </div>
    

    <?php if (!empty($place['category_name'])): ?>
        <span class="category-tag" style="display: inline-block; margin-top: 10px; background: #eee; padding: 4px 8px; border-radius: 8px; font-size: 0.8em;">
            <?= htmlspecialchars($place['category_name']) ?>
        </span>
    <?php endif; ?>
</a>    
            </div>
        <?php endforeach; ?>
    </div>

<?php elseif (empty($recommended)): ?>
    <p>Inga restauranger hittades.</p>
<?php endif; ?>

<?php include __DIR__ . '/partials/footer.php'; ?>
