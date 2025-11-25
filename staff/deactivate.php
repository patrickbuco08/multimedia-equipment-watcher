<?php
require_once '../config/database.php';

startSession();
require_once '../config/database.php';
requireAdmin();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: list.php');
    exit;
}

$userId = (int)$_GET['id'];
$currentUserId = getCurrentUser()['id'];

// Prevent deactivating yourself
if ($userId == $currentUserId) {
    $_SESSION['error_message'] = 'You cannot deactivate your own account.';
    header('Location: list.php');
    exit;
}

$pdo = getDBConnection();

try {
    // Deactivate the user
    $stmt = $pdo->prepare("UPDATE users SET is_active = 0 WHERE id = ?");
    $stmt->execute([$userId]);
    
    $_SESSION['success_message'] = 'User account deactivated successfully!';
} catch (Exception $e) {
    $_SESSION['error_message'] = 'Error deactivating user account.';
}

header('Location: list.php');
exit;
