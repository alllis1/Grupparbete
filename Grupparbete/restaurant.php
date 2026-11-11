<?php
require_once __DIR__ . '/include/bootstrap.php';
require_once __DIR__ . '/include/auth.php';
require_once __DIR__ . '/models/reviews.php';
require_once __DIR__ . '/models/places.php';

$name = $_GET['name'] ?? '';
$city = $_GET['city'] ?? '';
$address = $_GET['address'] ?? '';

if (empty($name) || empty($city)) {
    die('Restaurangnamn och stad krävs.');
}

$pdo = getDB();


$city_id = getCityIdByName($pdo, $city);

if (!$city_id) {
    die('Stad hittades inte.');
}


$place = getPlaceByNameCity($pdo, $name, $city_id);

if ($place) {
    $place_id = (int)$place['place_id'];
} else {
   
    $place_id = getOrCreatePlace($pdo, $name, $city_id, $address, null);
    $place = getPlaceByNameCity($pdo, $name, $city_id);
}


$rating_filter = $_GET['rating'] ?? null;

$sql = "
    SELECT
        reviews.review_text,
        reviews.rating,
        reviews.image_path,
        reviews.created_at,
        users.user_name
    FROM reviews
    JOIN users ON reviews.user_id = users.user_id
    WHERE reviews.place_id = ?
";

$params = [$place_id];

if ($rating_filter && is_numeric($rating_filter)) {
    $sql .= " AND reviews.rating = ?";
    $params[] = $rating_filter;
}

$sql .= " ORDER BY reviews.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);


$avgRating = 0;
if (!empty($reviews)) {
    $totalRating = array_sum(array_column($reviews, 'rating'));
    $avgRating = round($totalRating / count($reviews), 1);
}

include 'views/partials/header.php';
?>

<div style="max-width: 900px; margin: 0 auto;">

    
    <div class="card" style="margin-bottom: 30px; padding: 30px;">
        <h1 style="font-size: 2.5rem;">
            <?= htmlspecialchars($name) ?>
        </h1>

        <?php if (!empty($place['place_address'])): ?>
            <p>📍 <?= htmlspecialchars($place['place_address']) ?></p>
        <?php endif; ?>

        <?php if ($avgRating > 0): ?>
            <div>
                <strong><?= $avgRating ?> / 5</strong>
                (<?= count($reviews) ?> recensioner)
            </div>
        <?php endif; ?>
    </div>

    
    <div class="card" style="margin-bottom: 30px; padding: 30px;">
        <h2>Recensioner</h2>

        <form method="GET">
            <input type="hidden" name="name" value="<?= htmlspecialchars($name) ?>">
            <input type="hidden" name="city" value="<?= htmlspecialchars($city) ?>">

            <label>Filtrera:</label>
            <select name="rating" onchange="this.form.submit();">
                <option value="">Alla</option>
                <option value="5" <?= ($rating_filter == 5) ? 'selected' : '' ?>>5</option>
                <option value="4" <?= ($rating_filter == 4) ? 'selected' : '' ?>>4</option>
                <option value="3" <?= ($rating_filter == 3) ? 'selected' : '' ?>>3</option>
                <option value="2" <?= ($rating_filter == 2) ? 'selected' : '' ?>>2</option>
                <option value="1" <?= ($rating_filter == 1) ? 'selected' : '' ?>>1</option>
            </select>
        </form>

        <?php if (!empty($reviews)): ?>
            <?php foreach ($reviews as $review): ?>
                <div style="border-bottom: 1px solid #eee; padding: 15px 0;">
                    <strong><?= htmlspecialchars($review['user_name']) ?></strong>
                    <span><?= $review['rating'] ?>/5</span>

                    <p><?= nl2br(htmlspecialchars($review['review_text'])) ?></p>

                    <?php if (!empty($review['image_path'])): ?>
                        <img src="/Grupparbete/<?= htmlspecialchars($review['image_path']) ?>" style="max-width:200px;">
                    <?php endif; ?>

                    <small><?= $review['created_at'] ?></small>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Inga recensioner ännu.</p>
        <?php endif; ?>
    </div>

    
    <?php if (is_logged_in()): ?>
        <div class="card" style="padding: 30px;">
            <h2>Skriv en recension</h2>

            <form action="/Grupparbete/add_review_process.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="place_name" value="<?= htmlspecialchars($name) ?>">
                <input type="hidden" name="city" value="<?= htmlspecialchars($city) ?>">
                <input type="hidden" name="address" value="<?= htmlspecialchars($address) ?>">

                <label>Betyg:</label>
                <select name="rating" required>
                    <option value="">Välj</option>
                    <option value="5">5</option>
                    <option value="4">4</option>
                    <option value="3">3</option>
                    <option value="2">2</option>
                    <option value="1">1</option>
                </select>

                <label>Text:</label>
                <textarea name="review_text" required></textarea>

                <label>Bild:</label>
                <input type="file" name="image">

                <button type="submit">Skicka</button>
            </form>
        </div>
    <?php endif; ?>

    <div style="text-align:center; margin-top:20px;">
        <a href="/Grupparbete/views/city.php?city=<?= urlencode(strtolower($city)) ?>">
            ← Tillbaka
        </a>
    </div>

</div>

<?php include 'views/partials/footer.php'; ?>
