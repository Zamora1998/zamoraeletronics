<?php
require_once __ROOT__ . '/assets/php/libLocale.php';
require_once __ROOT__ . '/model/modAuth.php';

// Clear remember token on logout - use session id since authorization is cleared after
$userId = $_SESSION['id'] ?? 0;

if ($userId) {
    $objAuth = new auth($_MYSQLI_);
    $objAuth->clearRememberToken($userId);
}

header("Location: /{$chrLocale}");
session_unset();
