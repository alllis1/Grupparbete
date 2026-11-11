<?php
require_once __DIR__ . '/../include/bootstrap.php';
require_once __DIR__ . '/../include/auth.php';


if (!is_admin()) {
    header('Location: /Grupparbete/views/index.php');
    exit;
}

$pdo = getDB();


$stmt = $pdo->query("
    SELECT user_id, user_name, email, role 
    FROM users 
    ORDER BY user_name
");

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$current_user_id = get_user_id();

include __DIR__ . '/../views/partials/header.php';
?>

<div class="card">
    <h1 class="page-title">Adminpanel: Hantera Användare</h1>

       <nav style="margin-bottom: 20px; display: flex; gap: 10px; flex-wrap: wrap;">
       <a href="manage_reviews.php" class="button secondary">Hantera Recensioner</a>
       <a href="manage_users.php" class="button">Hantera Användare</a>
       
   </nav>

    <?php if (isset($_GET['success'])): ?>
        <p style="background: #d4edda; color: #155724; padding: 10px; border-radius: 8px;">
            Åtgärden lyckades!
        </p>
    <?php endif; ?>

    <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
        <thead>
            <tr style="text-align: left; background-color: var(--section-pink);">
                <th style="padding: 12px;">Användarnamn</th>
                <th style="padding: 12px;">E-post</th>
                <th style="padding: 12px;">Roll</th>
                <th style="padding: 12px;">Åtgärd</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 12px;"><?= htmlspecialchars($user['user_name']) ?></td>
                    <td style="padding: 12px;"><?= htmlspecialchars($user['email']) ?></td>
                    <td style="padding: 12px;"><?= htmlspecialchars($user['role']) ?></td>
                    <td style="padding: 12px;">

                        <?php if ($user['user_id'] != $current_user_id): ?>
                            <a href="user_action.php?action=delete&id=<?= $user['user_id'] ?>"
                               class="button secondary"
                               onclick="return confirm('Är du säker på att du vill radera denna användare? Alla hens recensioner tas också bort.');"
                               style="padding: 5px 10px; font-size: 0.9rem;">
                               Ta bort
                            </a>
                        <?php else: ?>
                            (Du)
                        <?php endif; ?>

                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../views/partials/footer.php'; ?>
