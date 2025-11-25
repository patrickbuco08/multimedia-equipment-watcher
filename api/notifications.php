<?php
require_once __DIR__ . '/../config/database.php';
requireLogin();

header('Content-Type: application/json');

$currentUser = getCurrentUser();
$pdo = getDBConnection();

$action = $_GET['action'] ?? '';

if ($action === 'fetch') {
    // Get unread notifications for current user
    $stmt = $pdo->prepare("
        SELECT n.id, n.message, n.created_at, n.is_read,
               e.name as equipment_name
        FROM notifications n
        LEFT JOIN borrowing_transactions bt ON n.transaction_id = bt.id
        LEFT JOIN equipment e ON bt.equipment_id = e.id
        WHERE n.user_id = ? 
        ORDER BY n.created_at DESC
        LIMIT 10
    ");
    $stmt->execute([$currentUser['id']]);
    $notifications = $stmt->fetchAll();
    
    // Get unread count
    $stmt = $pdo->prepare("SELECT COUNT(*) as unread_count FROM notifications WHERE user_id = ? AND is_read = FALSE");
    $stmt->execute([$currentUser['id']]);
    $unreadCount = $stmt->fetch()['unread_count'];
    
    echo json_encode([
        'success' => true,
        'notifications' => $notifications,
        'unread_count' => $unreadCount
    ]);
    
} elseif ($action === 'mark_read') {
    // Mark all notifications as read for current user
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = TRUE WHERE user_id = ? AND is_read = FALSE");
    $stmt->execute([$currentUser['id']]);
    
    echo json_encode([
        'success' => true,
        'message' => 'All notifications marked as read'
    ]);
    
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid action'
    ]);
}
?>
