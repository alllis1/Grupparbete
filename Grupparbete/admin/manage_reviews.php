<?php
require_once __DIR__ . '/../include/bootstrap.php';
require_once __DIR__ . '/../include/auth.php';


if (!is_admin()) {
    header('Location: /Grupparbete/views/index.php');
    exit;
}

$pdo = getDB();


$stmt = $pdo->query("
    SELECT
        r.review_id,
        r.review_text,
        r.rating,
        r.created_at,
        u.user_name,
        p.place_name,
        c.city_name -- Lade till denna rad
    FROM reviews r
    JOIN users u ON r.user_id = u.user_id
    JOIN places p ON r.place_id = p.place_id
    JOIN city c ON p.city_id = c.city_id -- Lade till denna JOIN
    ORDER BY r.created_at DESC
");

$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../views/partials/header.php';
?>

<div class="card">
    <h1 class="page-title">Adminpanel: Hantera Recensioner</h1>

       <nav style="margin-bottom: 20px; display: flex; gap: 10px; flex-wrap: wrap;">
       <a href="manage_reviews.php" class="button">Hantera Recensioner</a>
       <a href="manage_users.php" class="button secondary">Hantera Användare</a>
       
   </nav>

    <?php if (isset($_GET['deleted']) && $_GET['deleted'] == 'true'): ?>
        <p style="background: #d4edda; color: #155724; padding: 10px; border-radius: 8px;">
            Recensionen har tagits bort!
        </p>
    <?php endif; ?>

    <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
        <thead>
            <tr style="text-align: left; background-color: var(--section-pink);">
                <th style="padding: 12px;">Restaurang</th>
                <th style="padding: 12px;">Användare</th>
                <th style="padding: 12px;">Betyg</th>
                <th style="padding: 12px;">Recension</th>
                <th style="padding: 12px;">Datum</th>
                <th style="padding: 12px;">Åtgärd</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reviews as $review): ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 12px;"><?= htmlspecialchars($review['place_name']) ?> (<?= htmlspecialchars($review['city_name']) ?>)</td>
                    <td style="padding: 12px;"><?= htmlspecialchars($review['user_name']) ?></td>
                    <td style="padding: 12px;"><?= htmlspecialchars($review['rating']) ?> / 5</td>
                    <td style="padding: 12px;"><?= htmlspecialchars(substr($review['review_text'], 0, 50)) ?>...</td>
                    <td style="padding: 12px;"><?= htmlspecialchars($review['created_at']) ?></td>
                    <td style="padding: 12px;">
                        <a href="delete_review.php?id=<?= $review['review_id'] ?>"
                           class="button secondary"
                           onclick="return confirm('Är du säker på att du vill ta bort denna recension?');"
                           style="padding: 5px 10px; font-size: 0.9rem;">
                           Ta bort
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../views/partials/footer.php'; ?>