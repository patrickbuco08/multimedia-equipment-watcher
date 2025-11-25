<?php
$pageTitle = 'New Borrow Transaction - Multimedia Equipment Watcher';
require_once '../includes/header.php';

use PHPMailer\PHPMailer\PHPMailer;

// Email notification function
function sendAdminNotification($pdo, $transaction_id, $adminUsers) {
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
        
        // Build email content
        $content = '<h2 style="margin: 0 0 20px 0; color: #212529; font-size: 20px;">New Equipment Borrow Request</h2>
        <p style="margin: 0 0 20px 0; color: #495057; font-size: 14px; line-height: 1.6;">
            Dear Administrator,
        </p>
        <p style="margin: 0 0 20px 0; color: #495057; font-size: 14px; line-height: 1.6;">
            A new equipment borrow request has been submitted and is pending your approval.
        </p>';
        
        $content .= getAlertBox('&#8987; <strong>Status: Pending Approval</strong> - This request requires your review.', 'warning');
        
        $content .= getInfoBox([
            'Equipment' => $transaction['equipment_name'],
            'Borrower' => $transaction['user_name'],
            'Email' => $transaction['user_email'],
            'Quantity' => $transaction['quantity'],
            'Date Borrowed' => date('F d, Y', strtotime($transaction['date_borrowed'])),
            'Expected Return' => date('F d, Y', strtotime($transaction['due_date'])),
            'Remarks' => $transaction['remarks'] ?: 'None'
        ]);
        
        $content .= '<p style="margin: 20px 0 0 0; color: #495057; font-size: 14px; line-height: 1.6;">
            Please log in to the system to review and approve this request.
        </p>';
        
        $htmlBody = getEmailTemplate('New Borrow Request', $content);
        
        foreach ($adminUsers as $admin) {
            $phpmailer = getMailer();
            $phpmailer->addAddress($admin['email']);
            
            $phpmailer->Subject = 'New Equipment Borrow Request - ' . $transaction['equipment_name'];
            $phpmailer->Body = $htmlBody;
            $phpmailer->AltBody = "New Equipment Borrow Request\n\nEquipment: " . $transaction['equipment_name'] . "\nBorrower: " . $transaction['user_name'] . "\nQuantity: " . $transaction['quantity'];
            
            $phpmailer->send();
            
            // Log email for each admin
            $stmt = $pdo->prepare("INSERT INTO email_logs (transaction_id, email_to, subject, message, status) VALUES (?, ?, ?, ?, 'success')");
            $stmt->execute([$transaction_id, $admin['email'], $phpmailer->Subject, $phpmailer->AltBody]);
        }
        
        return true;
    } catch (Exception $e) {
        // Log failed email
        if (isset($transaction_id) && isset($admin['email'])) {
            $stmt = $pdo->prepare("INSERT INTO email_logs (transaction_id, email_to, subject, message, status) VALUES (?, ?, ?, ?, 'failed')");
            $stmt->execute([$transaction_id, $admin['email'], 'New Equipment Borrow Request', 'Failed to send email: ' . $e->getMessage()]);
        }
        return false;
    }
}

$pdo = getDBConnection();
$success = '';
$error = '';

// Get current logged-in user
$currentUser = getCurrentUser();

// Get available equipment (with available quantity > 0)
$availableEquipment = $pdo->query("SELECT id, name, available_quantity FROM equipment WHERE available_quantity > 0 ORDER BY name")->fetchAll();

// Get admin users for email notification
$adminUsers = $pdo->query("SELECT email FROM users WHERE role = 'admin'")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $equipment_id = $_POST['equipment_id'] ?? 0;
    $user_id = $currentUser['id']; // Auto-select current user
    $quantity = intval($_POST['quantity'] ?? 1);
    $date_borrowed = $_POST['date_borrowed'] ?? date('Y-m-d\TH:i');
    $expected_return_date = $_POST['expected_return_date'] ?? '';
    $remarks = trim($_POST['remarks'] ?? '');
    
    if (empty($equipment_id) || empty($expected_return_date)) {
        $error = 'Please fill in all required fields';
    } elseif (strtotime($expected_return_date) < strtotime($date_borrowed)) {
        $error = 'Expected return date cannot be earlier than borrow date';
    } elseif ($quantity < 1) {
        $error = 'Quantity must be at least 1';
    } else {
        try {
            $pdo->beginTransaction();
            
            // Check available quantity
            $stmt = $pdo->prepare("SELECT available_quantity FROM equipment WHERE id = ?");
            $stmt->execute([$equipment_id]);
            $equip = $stmt->fetch();
            
            if (!$equip || $equip['available_quantity'] < $quantity) {
                throw new Exception('Not enough quantity available');
            }
            
            // Create borrow transaction with 'pending' status
            $stmt = $pdo->prepare("INSERT INTO borrowing_transactions (equipment_id, user_id, quantity, quantity_returned, date_borrowed, due_date, status, remarks) VALUES (?, ?, ?, 0, ?, ?, 'pending', ?)");
            $stmt->execute([$equipment_id, $user_id, $quantity, $date_borrowed, $expected_return_date, $remarks]);
            
            $transaction_id = $pdo->lastInsertId();
            
            // Deduct from available quantity
            $stmt = $pdo->prepare("UPDATE equipment SET available_quantity = available_quantity - ? WHERE id = ?");
            $stmt->execute([$quantity, $equipment_id]);
            
            $pdo->commit();
            
            // Send email notification to admin
            sendAdminNotification($pdo, $transaction_id, $adminUsers);
            
            // Create notification for admin about new borrowing request
            $stmt = $pdo->prepare("SELECT e.name as equipment_name, u.name as user_name FROM equipment e JOIN users u ON u.id = ? WHERE e.id = ?");
            $stmt->execute([$user_id, $equipment_id]);
            $info = $stmt->fetch();
            
            if ($info) {
                $notificationMessage = htmlspecialchars($info['user_name']) . " is requesting to borrow " . htmlspecialchars($info['equipment_name']) . " (" . $quantity . " item" . ($quantity > 1 ? "s" : "") . ")";
                
                // Get all admin users
                $adminStmt = $pdo->prepare("SELECT id FROM users WHERE role = 'admin'");
                $adminStmt->execute();
                $admins = $adminStmt->fetchAll();
                
                foreach ($admins as $admin) {
                    $notifStmt = $pdo->prepare("INSERT INTO notifications (user_id, transaction_id, message) VALUES (?, ?, ?)");
                    $notifStmt->execute([$admin['id'], $transaction_id, $notificationMessage]);
                }
            }
            
            $success = 'Borrow transaction created successfully! Admin has been notified.';
            
            // Use JavaScript redirect instead of header()
            echo '<script>window.location.href = "../staff-dashboard.php";</script>';
            exit;
            
            // Clear form
            $equipment_id = 0;
            $remarks = '';
            $quantity = 1;
            $date_borrowed = date('Y-m-d\TH:i');
            $expected_return_date = '';
            
            // Refresh available equipment
            $availableEquipment = $pdo->query("SELECT id, name, available_quantity FROM equipment WHERE available_quantity > 0 ORDER BY name")->fetchAll();
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Error creating transaction: ' . $e->getMessage();
        }
    }
}
?>

<!-- Page Header -->
<div class="flex justify-between items-center mb-6">
    <h2 class="text-3xl font-bold text-white">New Borrow Transaction</h2>
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
    
    <?php if (count($availableEquipment) === 0): ?>
        <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-4">
            No equipment available for borrowing at the moment. Please check back later or add new equipment.
        </div>
    <?php endif; ?>
    
    <form method="POST" action="" class="space-y-5">
        <div>
            <label for="equipment_id" class="block text-sm font-medium text-gray-700 mb-2">Equipment *</label>
            <select id="equipment_id" name="equipment_id" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent" <?php echo count($availableEquipment) === 0 ? 'disabled' : ''; ?> onchange="updateMaxQuantity()">
                <option value="">Select Equipment</option>
                <?php foreach ($availableEquipment as $equip): ?>
                    <option value="<?php echo $equip['id']; ?>" data-available="<?php echo $equip['available_quantity']; ?>" <?php echo ($equipment_id ?? 0) == $equip['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($equip['name']); ?> (Available: <?php echo $equip['available_quantity']; ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div>
            <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">Quantity to Borrow *</label>
            <input type="number" id="quantity" name="quantity" min="1" required value="<?php echo htmlspecialchars($quantity ?? 1); ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
            <p class="text-xs text-gray-500 mt-1" id="quantity-hint">Select equipment to see available quantity</p>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Borrower (User) *</label>
            <input type="text" value="<?php echo htmlspecialchars($currentUser['name']); ?> (<?php echo htmlspecialchars($currentUser['email']); ?>)" readonly class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 text-gray-600">
            <input type="hidden" name="user_id" value="<?php echo $currentUser['id']; ?>">
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label for="date_borrowed" class="block text-sm font-medium text-gray-700 mb-2">Date & Time Borrowed *</label>
                <input type="datetime-local" id="date_borrowed" name="date_borrowed" required value="<?php echo $date_borrowed ?? date('Y-m-d\TH:i'); ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
            </div>
            
            <div>
                <label for="expected_return_date" class="block text-sm font-medium text-gray-700 mb-2">Expected Return Date & Time *</label>
                <input type="datetime-local" id="expected_return_date" name="expected_return_date" required value="<?php echo $expected_return_date ?? ''; ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
            </div>
        </div>
        
        <div>
            <label for="remarks" class="block text-sm font-medium text-gray-700 mb-2">Remarks</label>
            <textarea id="remarks" name="remarks" placeholder="Optional notes about this transaction" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"><?php echo htmlspecialchars($remarks ?? ''); ?></textarea>
        </div>
        
        <div class="flex gap-3 pt-4">
            <button type="submit" class="px-6 py-3 bg-primary hover:bg-primary-dark text-white font-semibold rounded-lg transition" <?php echo count($availableEquipment) === 0 ? 'disabled' : ''; ?>>Create Transaction</button>
            <a href="list.php" class="px-6 py-3 bg-gray-400 hover:bg-gray-500 text-white font-semibold rounded-lg transition">Cancel</a>
        </div>
    </form>
</div>

<script>
function updateMaxQuantity() {
    const select = document.getElementById('equipment_id');
    const quantityInput = document.getElementById('quantity');
    const hint = document.getElementById('quantity-hint');
    
    const selectedOption = select.options[select.selectedIndex];
    const available = selectedOption.getAttribute('data-available');
    
    if (available) {
        quantityInput.max = available;
        hint.textContent = `Maximum available: ${available}`;
        hint.classList.remove('text-gray-500');
        hint.classList.add('text-primary');
    } else {
        quantityInput.max = '';
        hint.textContent = 'Select equipment to see available quantity';
        hint.classList.remove('text-primary');
        hint.classList.add('text-gray-500');
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', updateMaxQuantity);
</script>

<?php require_once '../includes/footer.php'; ?>
