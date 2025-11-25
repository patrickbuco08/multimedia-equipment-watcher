<?php
require_once '../config/database.php';
require_once '../config/database.php';
requireAdmin();

$pdo = getDBConnection();
$id = $_GET['id'] ?? 0;

try {
    // Check if equipment exists
    $stmt = $pdo->prepare("SELECT * FROM equipment WHERE id = ?");
    $stmt->execute([$id]);
    $equipment = $stmt->fetch();
    
    if ($equipment) {
        // Delete equipment (cascade will handle related transactions)
        $stmt = $pdo->prepare("DELETE FROM equipment WHERE id = ?");
        $stmt->execute([$id]);
    }
} catch (Exception $e) {
    // Handle error silently and redirect
}

header('Location: list.php');
exit;
