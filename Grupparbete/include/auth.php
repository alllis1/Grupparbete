<?php
require_once __DIR__ . '/bootstrap.php';

function is_logged_in(): bool {
    return isset($_SESSION['user_id']);
}

function get_user_id(): ?int {
    return $_SESSION['user_id'] ?? null;
}

function is_admin(): bool {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}