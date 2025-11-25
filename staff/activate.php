<?php
require_once '../config/database.php';

startSession();
requireAdmin();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: list.php');
    exit;
}

$userId = (int)$_GET['id'];
$pdo = getDBConnection();

try {
    // Activate the user
    $stmt = $pdo->prepare("UPDATE users SET is_active = 1 WHERE id = ?");
    $stmt->execute([$userId]);
    
    $_SESSION['success_message'] = 'User account activated successfully!';
} catch (Exception $e) {
    $_SESSION['error_message'] = 'Error activating user account.';
}

header('Location: list.php');
exit;
