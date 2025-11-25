<?php
require_once '../config/database.php';
requireLogin();

$pdo = getDBConnection();
$id = $_GET['id'] ?? 0;

try {
    // Get transaction details
    $stmt = $pdo->prepare("SELECT * FROM borrowing_transactions WHERE id = ? AND status = 'borrowed'");
    $stmt->execute([$id]);
    $transaction = $stmt->fetch();
    
    if ($transaction) {
        $pdo->beginTransaction();
        
        // Update transaction as returned
        $stmt = $pdo->prepare("UPDATE borrowing_transactions SET status = 'returned', date_returned = CURDATE() WHERE id = ?");
        $stmt->execute([$id]);
        
        // Update equipment status back to available
        $stmt = $pdo->prepare("UPDATE equipment SET status = 'available' WHERE id = ?");
        $stmt->execute([$transaction['equipment_id']]);
        
        $pdo->commit();
    }
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
}

header('Location: list.php');
exit;
