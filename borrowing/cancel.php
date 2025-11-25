<?php
require_once '../config/database.php';
requireLogin();
require_once '../includes/header.php';

// Check if transaction ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = 'Invalid transaction ID';
    header('Location: /staff-dashboard.php');
    exit;
}

$transactionId = $_GET['id'];
$userId = $_SESSION['user_id'];

try {
    $pdo = getDBConnection();
    
    // Start transaction
    $pdo->beginTransaction();
    
    // Get transaction details and verify ownership
    $stmt = $pdo->prepare("
        SELECT bt.*, e.name as equipment_name 
        FROM borrowing_transactions bt 
        JOIN equipment e ON bt.equipment_id = e.id 
        WHERE bt.id = ? AND bt.user_id = ? AND bt.status = 'pending'
    ");
    $stmt->execute([$transactionId, $userId]);
    $transaction = $stmt->fetch();
    
    if (!$transaction) {
        $_SESSION['error'] = 'Transaction not found or cannot be cancelled';
        header('Location: /staff-dashboard.php');
        exit;
    }
    
    // Update transaction status to cancelled
    $stmt = $pdo->prepare("UPDATE borrowing_transactions SET status = 'cancelled' WHERE id = ?");
    $stmt->execute([$transactionId]);
    
    // Restore equipment quantity
    $stmt = $pdo->prepare("UPDATE equipment SET available_quantity = available_quantity + ? WHERE id = ?");
    $stmt->execute([$transaction['quantity'], $transaction['equipment_id']]);
    
    // Create notification for admins about cancellation
    $stmt = $pdo->prepare("SELECT id FROM users WHERE role = 'admin'");
    $stmt->execute();
    $admins = $stmt->fetchAll();
    
    $notificationMessage = htmlspecialchars($_SESSION['user_name']) . " cancelled their borrowing request for " . htmlspecialchars($transaction['equipment_name']) . " (" . $transaction['quantity'] . " item" . ($transaction['quantity'] > 1 ? "s" : "") . ")";
    
    foreach ($admins as $admin) {
        $notifStmt = $pdo->prepare("INSERT INTO notifications (user_id, transaction_id, message) VALUES (?, ?, ?)");
        $notifStmt->execute([$admin['id'], $transactionId, $notificationMessage]);
    }
    
    $pdo->commit();
    
    $_SESSION['success'] = 'Borrowing request cancelled successfully';
    
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = 'Error cancelling request: ' . $e->getMessage();
}

echo "<script>window.location.href = '/staff-dashboard.php';</script>";
exit;
?>
