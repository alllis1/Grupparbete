
<?php
require_once __DIR__ . '/include/bootstrap.php';
require_once __DIR__ . '/include/auth.php';
require_once __DIR__ . '/models/reviews.php';
require_once __DIR__ . '/models/places.php';

if (!is_logged_in()) {
    header("Location: /Grupparbete/views/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $pdo = getDB();

    $city = $_POST['city'] ?? '';
    $place_name = $_POST['place_name'] ?? '';
    $address = $_POST['address'] ?? null;
    $rating = (int)($_POST['rating'] ?? 0);
    $review_text = $_POST['review_text'] ?? '';
    $user_id = $_SESSION['user_id'];

    
    $city_id = getCityIdByName($pdo, $city);

    if (!$city_id) {
        die("Ogiltig stad");
    }

    
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {

        $upload_dir = __DIR__ . '/uploads/';

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_name = uniqid() . '-' . basename($_FILES['image']['name']);
        $target_file = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image_path = 'uploads/' . $file_name;
        }
    }

    
    $place_id = getOrCreatePlace(
        $pdo,
        $place_name,
        $city_id,
        $address,
        null 
    );

   
    createReview(
        $pdo,
        $review_text,
        $rating,
        $image_path,
        $user_id,
        $place_id
    );

    
    header(
        'Location: /Grupparbete/restaurant.php?name='
        . urlencode($place_name)
        . '&city=' . urlencode($city)
        . '&address=' . urlencode($address)
    );
    exit;
}
