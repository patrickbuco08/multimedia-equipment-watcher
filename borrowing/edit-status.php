<?php
$pageTitle = 'Edit Transaction Status - Multimedia Equipment Watcher';
require_once '../config/database.php';
requireAdmin();
require_once '../includes/header.php';

use PHPMailer\PHPMailer\PHPMailer;

// Email notification function for status changes
function sendStatusChangeEmail($pdo, $transaction_id, $oldStatus, $newStatus) {
    try {
        // Get transaction details
        $stmt = $pdo->prepare("
            SELECT bt.*, e.name as equipment_name, u.name as user_name, u.email as user_email 
            FROM borrowing_transactions bt 
            JOIN equipment e ON bt.equipment_id = e.id 
            JOIN users u ON bt.user_id = u.id 
            WHERE bt.id = ?
        ");
        $stmt->execute([$transaction_id]);
        $transaction = $stmt->fetch();
        
        if (!$transaction) return false;
        
        // Import PHPMailer, email template, and mail config
        require_once '../vendor/autoload.php';
        require_once '../includes/email_template.php';
        require_once '../config/mail.php';
        
        // Build email content based on status
        $statusMessages = [
            'borrowed' => [
                'title' => 'Borrow Request Approved',
                'message' => 'Your equipment borrow request has been approved by the administrator.',
                'color' => 'success'
            ],
            'rejected' => [
                'title' => 'Borrow Request Rejected',
                'message' => 'Unfortunately, your equipment borrow request has been rejected by the administrator.',
                'color' => 'danger'
            ],
            'returned' => [
                'title' => 'Equipment Return Confirmed',
                'message' => 'Your equipment return has been confirmed by the administrator. Thank you for returning the equipment on time.',
                'color' => 'success'
            ],
            'partially_returned' => [
                'title' => 'Partial Return Confirmed',
                'message' => 'Your partial equipment return has been confirmed by the administrator.',
                'color' => 'info'
            ],
            'lost' => [
                'title' => 'Equipment Marked as Lost',
                'message' => 'The equipment you borrowed has been marked as lost. Please contact the administrator for further instructions.',
                'color' => 'danger'
            ]
        ];
        
        $statusInfo = $statusMessages[$newStatus] ?? [
            'title' => 'Transaction Status Updated',
            'message' => 'Your transaction status has been updated.',
            'color' => 'info'
        ];
        
        $content = '<h2 style="margin: 0 0 20px 0; color: #212529; font-size: 20px;">' . $statusInfo['title'] . '</h2>
        <p style="margin: 0 0 20px 0; color: #495057; font-size: 14px; line-height: 1.6;">
            Dear ' . htmlspecialchars($transaction['user_name']) . ',
        </p>
        <p style="margin: 0 0 20px 0; color: #495057; font-size: 14px; line-height: 1.6;">
            ' . $statusInfo['message'] . '
        </p>';
        
        $content .= getInfoBox([
            'Equipment' => $transaction['equipment_name'],
            'Quantity' => $transaction['quantity'],
            'Date Borrowed' => date('F d, Y', strtotime($transaction['date_borrowed'])),
            'Due Date' => date('F d, Y', strtotime($transaction['due_date'])),
            'New Status' => ucfirst(str_replace('_', ' ', $newStatus))
        ]);
        
        if ($newStatus === 'rejected') {
            $content .= '<p style="margin: 20px 0 0 0; color: #495057; font-size: 14px; line-height: 1.6;">
                If you have any questions, please contact the administrator.
            </p>';
        }
        
        $htmlBody = getEmailTemplate($statusInfo['title'], $content);
        
        $phpmailer = getMailer();
        $phpmailer->addAddress($transaction['user_email']);
        $phpmailer->Subject = $statusInfo['title'] . ' - ' . $transaction['equipment_name'];
        $phpmailer->Body = $htmlBody;
        $phpmailer->AltBody = $statusInfo['title'] . "\n\nEquipment: " . $transaction['equipment_name'] . "\nStatus: " . ucfirst(str_replace('_', ' ', $newStatus));
        
        $phpmailer->send();
        
        // Log email
        $stmt = $pdo->prepare("INSERT INTO email_logs (transaction_id, email_to, subject, message, status) VALUES (?, ?, ?, ?, 'success')");
        $stmt->execute([$transaction_id, $transaction['user_email'], $phpmailer->Subject, $phpmailer->AltBody]);
        
        return true;
    } catch (Exception $e) {
        // Log failed email
        if (isset($transaction_id) && isset($transaction['user_email'])) {
            $stmt = $pdo->prepare("INSERT INTO email_logs (transaction_id, email_to, subject, message, status) VALUES (?, ?, ?, ?, 'failed')");
            $stmt->execute([$transaction_id, $transaction['user_email'], 'Status Change Notification', 'Failed to send email: ' . $e->getMessage()]);
        }
        return false;
    }
}

$pdo = getDBConnection();
$success = '';
$error = '';

$id = $_GET['id'] ?? 0;

// Fetch transaction data
try {
    $stmt = $pdo->prepare("
        SELECT bt.*, e.name as equipment_name, u.name as borrower_name, u.email as borrower_email
        FROM borrowing_transactions bt
        JOIN equipment e ON bt.equipment_id = e.id
        JOIN users u ON bt.user_id = u.id
        WHERE bt.id = ?
    ");
    $stmt->execute([$id]);
    $transaction = $stmt->fetch();
    
    if (!$transaction) {
        header('Location: list.php');
        exit;
    }
} catch (Exception $e) {
    header('Location: list.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = $_POST['status'] ?? '';
    $date_returned = !empty($_POST['date_returned']) ? $_POST['date_returned'] : null;
    $quantity_returned = intval($_POST['quantity_returned'] ?? 0);
    
    if (empty($status)) {
        $error = 'Status is required';
    } else {
        try {
            $pdo->beginTransaction();
            
            $oldStatus = $transaction['status'];
            $oldQtyReturned = $transaction['quantity_returned'];
            
            // Handle inventory changes based on status transitions
            if ($oldStatus === 'pending' && $status === 'borrowed') {
                // Approve pending request - quantity already deducted when created
                // No inventory change needed
                $date_returned = null;
            } elseif (in_array($oldStatus, ['borrowed', 'partially_returned']) && $status === 'returned') {
                // Full return - add back remaining quantity
                $qtyToReturn = $transaction['quantity'] - $oldQtyReturned;
                $stmt = $pdo->prepare("UPDATE equipment SET available_quantity = available_quantity + ? WHERE id = ?");
                $stmt->execute([$qtyToReturn, $transaction['equipment_id']]);
                $quantity_returned = $transaction['quantity'];
                
                if (empty($date_returned)) {
                    $date_returned = date('Y-m-d');
                }
            } elseif (in_array($oldStatus, ['borrowed', 'pending']) && $status === 'partially_returned') {
                // Partial return - add back the returned quantity
                $qtyToReturn = $quantity_returned - $oldQtyReturned;
                if ($qtyToReturn > 0) {
                    $stmt = $pdo->prepare("UPDATE equipment SET available_quantity = available_quantity + ? WHERE id = ?");
                    $stmt->execute([$qtyToReturn, $transaction['equipment_id']]);
                }
                if (empty($date_returned)) {
                    $date_returned = date('Y-m-d');
                }
            } elseif ($oldStatus === 'returned' && in_array($status, ['borrowed', 'partially_returned'])) {
                // Unreturning - deduct quantity again
                $qtyToDeduct = $transaction['quantity'] - $quantity_returned;
                $stmt = $pdo->prepare("UPDATE equipment SET available_quantity = available_quantity - ? WHERE id = ?");
                $stmt->execute([$qtyToDeduct, $transaction['equipment_id']]);
                $date_returned = null;
            } elseif ($status === 'lost') {
                // Mark as lost - quantity remains deducted
                // Admin may need to adjust equipment total_quantity separately
                $date_returned = null;
            } elseif ($oldStatus === 'pending' && $status === 'rejected') {
                // Reject pending request - add back the deducted quantity
                $stmt = $pdo->prepare("UPDATE equipment SET available_quantity = available_quantity + ? WHERE id = ?");
                $stmt->execute([$transaction['quantity'], $transaction['equipment_id']]);
                $date_returned = null;
            } else {
                // For other status transitions, ensure date_returned is null unless explicitly returned
                if ($status !== 'returned' && $status !== 'partially_returned') {
                    $date_returned = null;
                }
            }
            
            // Update transaction status
            $stmt = $pdo->prepare("UPDATE borrowing_transactions SET status = ?, date_returned = ?, quantity_returned = ? WHERE id = ?");
            $stmt->execute([$status, $date_returned, $quantity_returned, $id]);
            
            // Create notification for user about status change with equipment name
            $statusMessages = [
                'pending' => 'Your borrowing request for ' . htmlspecialchars($transaction['equipment_name']) . ' is pending approval',
                'borrowed' => 'Your borrowing request for ' . htmlspecialchars($transaction['equipment_name']) . ' has been approved',
                'rejected' => 'Your borrowing request for ' . htmlspecialchars($transaction['equipment_name']) . ' has been rejected',
                'returned' => 'Your borrowed equipment ' . htmlspecialchars($transaction['equipment_name']) . ' has been marked as returned',
                'partially_returned' => 'Some of your borrowed equipment ' . htmlspecialchars($transaction['equipment_name']) . ' has been returned',
                'lost' => 'Your borrowed equipment ' . htmlspecialchars($transaction['equipment_name']) . ' has been marked as lost'
            ];
            
            $notificationMessage = $statusMessages[$status] ?? 'Your borrowing transaction status for ' . htmlspecialchars($transaction['equipment_name']) . ' has been updated';
            
            $stmt = $pdo->prepare("INSERT INTO notifications (user_id, transaction_id, message) VALUES (?, ?, ?)");
            $stmt->execute([$transaction['user_id'], $id, $notificationMessage]);
            
            $pdo->commit();
            
            // Send email notification to borrower about status change
            sendStatusChangeEmail($pdo, $id, $oldStatus, $status);
            
            $success = 'Transaction status updated successfully! Borrower has been notified.';
            
            // Refresh transaction data
            $stmt = $pdo->prepare("
                SELECT bt.*, e.name as equipment_name, u.name as borrower_name, u.email as borrower_email
                FROM borrowing_transactions bt
                JOIN equipment e ON bt.equipment_id = e.id
                JOIN users u ON bt.user_id = u.id
                WHERE bt.id = ?
            ");
            $stmt->execute([$id]);
            $transaction = $stmt->fetch();
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Error updating status: ' . $e->getMessage();
        }
    }
}
?>

<!-- Page Header -->
<div class="flex justify-between items-center mb-6">
    <h2 class="text-3xl font-bold text-white">Edit Transaction Status</h2>
    <a href="list.php" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg transition">← Back to List</a>
</div>

<!-- Form Container -->
<div class="max-w-3xl mx-auto bg-white rounded-lg shadow-sm p-6">
    <?php if ($success): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <!-- Transaction Info -->
    <div class="mb-6 p-4 bg-gray-50 rounded-lg">
        <h3 class="text-lg font-semibold text-gray-800 mb-3">Transaction Details</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
                <span class="text-gray-600">Equipment:</span>
                <span class="font-medium text-gray-900 ml-2"><?php echo htmlspecialchars($transaction['equipment_name']); ?></span>
            </div>
            <div>
                <span class="text-gray-600">Borrower:</span>
                <span class="font-medium text-gray-900 ml-2"><?php echo htmlspecialchars($transaction['borrower_name']); ?></span>
            </div>
            <div>
                <span class="text-gray-600">Quantity:</span>
                <span class="font-medium text-gray-900 ml-2"><?php echo $transaction['quantity']; ?></span>
            </div>
            <div>
                <span class="text-gray-600">Quantity Returned:</span>
                <span class="font-medium text-gray-900 ml-2"><?php echo $transaction['quantity_returned'] ?? 0; ?></span>
            </div>
            <div>
                <span class="text-gray-600">Date Borrowed:</span>
                <span class="font-medium text-gray-900 ml-2"><?php echo date('M d, Y', strtotime($transaction['date_borrowed'])); ?></span>
            </div>
            <div>
                <span class="text-gray-600">Due Date:</span>
                <span class="font-medium text-gray-900 ml-2"><?php echo date('M d, Y', strtotime($transaction['due_date'])); ?></span>
            </div>
            <div>
                <span class="text-gray-600">Current Status:</span>
                <span class="font-medium text-gray-900 ml-2"><?php echo ucfirst(str_replace('_', ' ', $transaction['status'])); ?></span>
            </div>
        </div>
    </div>
    
    <form method="POST" action="" class="space-y-5">
        <div>
            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">New Status *</label>
            <select id="status" name="status" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent" onchange="toggleFields()">
                <option value="pending" <?php echo $transaction['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="borrowed" <?php echo $transaction['status'] === 'borrowed' ? 'selected' : ''; ?>>Borrowed (Approved)</option>
                <option value="rejected" <?php echo $transaction['status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                <option value="partially_returned" <?php echo $transaction['status'] === 'partially_returned' ? 'selected' : ''; ?>>Partially Returned</option>
                <option value="returned" <?php echo $transaction['status'] === 'returned' ? 'selected' : ''; ?>>Returned</option>
                <option value="lost" <?php echo $transaction['status'] === 'lost' ? 'selected' : ''; ?>>Lost</option>
            </select>
        </div>
        
        <div id="quantity_returned_field" style="display: none;">
            <label for="quantity_returned" class="block text-sm font-medium text-gray-700 mb-2">Quantity Returned *</label>
            <input type="number" id="quantity_returned" name="quantity_returned" min="0" max="<?php echo $transaction['quantity']; ?>" value="<?php echo $transaction['quantity_returned'] ?? 0; ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
            <p class="text-xs text-gray-500 mt-1">Enter the number of items returned (max: <?php echo $transaction['quantity']; ?>)</p>
        </div>
        
        <div id="date_returned_field" style="display: none;">
            <label for="date_returned" class="block text-sm font-medium text-gray-700 mb-2">Date Returned *</label>
            <input type="date" id="date_returned" name="date_returned" value="<?php echo $transaction['date_returned'] ?? date('Y-m-d'); ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
            <p class="text-xs text-gray-500 mt-1">Date when the equipment was returned</p>
        </div>
        
        <div class="flex gap-3 pt-4">
            <button type="submit" class="px-6 py-3 bg-primary hover:bg-primary-dark text-white font-semibold rounded-lg transition">Update Status</button>
            <a href="list.php" class="px-6 py-3 bg-gray-400 hover:bg-gray-500 text-white font-semibold rounded-lg transition">Cancel</a>
        </div>
    </form>
</div>

<script>
function toggleFields() {
    const status = document.getElementById('status').value;
    const qtyField = document.getElementById('quantity_returned_field');
    const dateField = document.getElementById('date_returned_field');
    
    // Show quantity returned field only for partially_returned
    if (status === 'partially_returned') {
        qtyField.style.display = 'block';
    } else {
        qtyField.style.display = 'none';
    }
    
    // Show date returned field only for returned or partially_returned
    if (status === 'returned' || status === 'partially_returned') {
        dateField.style.display = 'block';
    } else {
        dateField.style.display = 'none';
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', toggleFields);
</script>

<?php require_once '../includes/footer.php'; ?>
