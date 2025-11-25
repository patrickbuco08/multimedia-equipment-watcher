<?php
require_once '../config/database.php';
require_once '../config/database.php';
requireAdmin();

$pdo = getDBConnection();
$id = $_GET['id'] ?? 0;
$currentUser = getCurrentUser();

try {
    // Prevent deleting own account
    if ($id != $currentUser['id']) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
    }
} catch (Exception $e) {
    // Handle error silently and redirect
}

header('Location: list.php');
exit;
