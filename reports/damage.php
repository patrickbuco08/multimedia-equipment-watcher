<?php
$pageTitle = 'Report Damage - Multimedia Equipment Watcher';
require_once '../includes/header.php';

use PHPMailer\PHPMailer\PHPMailer;

$pdo = getDBConnection();
$success = '';
$error = '';

// Get current logged-in user
$currentUser = getCurrentUser();

// Get only equipment that the current user has borrowed (status = 'borrowed')
$equipment = $pdo->prepare("
    SELECT e.id, e.name, 
           SUM(bt.quantity - bt.quantity_returned) as remaining_quantity
    FROM equipment e
    JOIN borrowing_transactions bt ON e.id = bt.equipment_id
    WHERE bt.user_id = ? AND bt.status = 'borrowed'
    GROUP BY e.id, e.name
    HAVING remaining_quantity > 0
    ORDER BY e.name
");
$equipment->execute([$currentUser['id']]);
$equipment = $equipment->fetchAll();

// Get admin users for email notification
$adminUsers = $pdo->query("SELECT email FROM users WHERE role = 'admin'")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $equipment_id = $_POST['equipment_id'] ?? '';
    $report_date = $_POST['report_date'] ?? '';
    $quantity = $_POST['quantity'] ?? 1;
    $description = trim($_POST['description'] ?? '');
    $image_path = null;
    
    if (empty($equipment_id) || empty($report_date) || empty($description)) {
        $error = 'Please fill in all required fields';
    } elseif ($quantity < 1) {
        $error = 'Quantity must be at least 1';
    } else {
        // Check if quantity exceeds borrowed amount
        $stmt = $pdo->prepare("
            SELECT SUM(bt.quantity - bt.quantity_returned) as remaining_quantity
            FROM borrowing_transactions bt
            WHERE bt.equipment_id = ? AND bt.user_id = ? AND bt.status = 'borrowed'
        ");
        $stmt->execute([$equipment_id, $currentUser['id']]);
        $borrowedInfo = $stmt->fetch();
        
        if (!$borrowedInfo || $borrowedInfo['remaining_quantity'] < $quantity) {
            $error = 'Cannot report more damaged items than you borrowed. You have ' . ($borrowedInfo['remaining_quantity'] ?? 0) . ' items remaining.';
        } else {
            try {
            // Handle image upload
            if (isset($_FILES['damage_image']) && $_FILES['damage_image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../uploads/reports/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $file_extension = strtolower(pathinfo($_FILES['damage_image']['name'], PATHINFO_EXTENSION));
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                
                if (in_array($file_extension, $allowed_extensions)) {
                    $new_filename = 'damage_' . time() . '_' . uniqid() . '.' . $file_extension;
                    $target_path = $upload_dir . $new_filename;
                    
                    if (move_uploaded_file($_FILES['damage_image']['tmp_name'], $target_path)) {
                        $image_path = '/uploads/reports/' . $new_filename;
                    }
                }
            }
            
            // Insert report
            $stmt = $pdo->prepare("INSERT INTO equipment_reports (equipment_id, report_type, reported_by, report_date, quantity, description, image_path) VALUES (?, 'damage', ?, ?, ?, ?, ?)");
            $stmt->execute([$equipment_id, $_SESSION['user_id'], $report_date, $quantity, $description, $image_path]);
            
            $report_id = $pdo->lastInsertId();
            
            // Update equipment status to damaged
            $stmt = $pdo->prepare("UPDATE equipment SET status = 'damaged' WHERE id = ?");
            $stmt->execute([$equipment_id]);
            
            // Send email notification to admin
            sendReportNotification($pdo, $report_id, 'damage', $adminUsers);
            
            // Create notification for admin about damage report
            $stmt = $pdo->prepare("SELECT e.name as equipment_name, u.name as user_name FROM equipment e JOIN users u ON u.id = ? WHERE e.id = ?");
            $stmt->execute([$currentUser['id'], $equipment_id]);
            $info = $stmt->fetch();
            
            if ($info) {
                $notificationMessage = htmlspecialchars($info['user_name']) . " reported damage to " . htmlspecialchars($info['equipment_name']) . " (" . $quantity . " item" . ($quantity > 1 ? "s" : "") . ")";
                
                // Get all admin users
                $adminStmt = $pdo->prepare("SELECT id FROM users WHERE role = 'admin'");
                $adminStmt->execute();
                $admins = $adminStmt->fetchAll();
                
                foreach ($admins as $admin) {
                    $notifStmt = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
                    $notifStmt->execute([$admin['id'], $notificationMessage]);
                }
            }
            
            $success = 'Damage report submitted successfully! Admin has been notified.';
            
            echo '<script>window.location.href = "/staff-dashboard.php";</script>';
            exit;
        } catch (Exception $e) {
            $error = 'Error submitting report: ' . $e->getMessage();
        }
        }
    }
}

// Email notification function
function sendReportNotification($pdo, $report_id, $report_type, $adminUsers) {
    try {
        // Get report details
        $stmt = $pdo->prepare("
            SELECT er.*, e.name as equipment_name, u.name as reporter_name, u.email as reporter_email
            FROM equipment_reports er
            JOIN equipment e ON er.equipment_id = e.id
            JOIN users u ON er.reported_by = u.id
            WHERE er.id = ?
        ");
        $stmt->execute([$report_id]);
        $report = $stmt->fetch();
        
        if (!$report) return false;
        
        require_once '../vendor/autoload.php';
        require_once '../includes/email_template.php';
        require_once '../config/mail.php';
        
        $type_label = ucfirst($report_type);
        $icon = $report_type === 'damage' ? '&#128295;' : '&#128269;';
        $alertType = $report_type === 'damage' ? 'danger' : 'warning';
        
        // Build email content
        $content = '<h2 style="margin: 0 0 20px 0; color: #212529; font-size: 20px;">' . $icon . ' Equipment ' . $type_label . ' Report</h2>
        <p style="margin: 0 0 20px 0; color: #495057; font-size: 14px; line-height: 1.6;">
            Dear Administrator,
        </p>
        <p style="margin: 0 0 20px 0; color: #495057; font-size: 14px; line-height: 1.6;">
            A new equipment ' . $report_type . ' report has been submitted by a staff member.
        </p>';
        
        $content .= getAlertBox($icon . ' <strong>' . strtoupper($type_label) . ' REPORT</strong> - Immediate attention required.', $alertType);
        
        $content .= getInfoBox([
            'Equipment' => $report['equipment_name'],
            'Reported By' => $report['reporter_name'],
            'Email' => $report['reporter_email'],
            'Report Date' => date('F d, Y', strtotime($report['report_date'])),
            'Quantity Affected' => $report['quantity'],
            'Description' => $report['description']
        ]);
        
        if ($report['image_path']) {
            $content .= '<p style="margin: 20px 0 10px 0; color: #495057; font-size: 14px;">
                <strong>&#128247; Image attached:</strong> An image has been uploaded with this report.
            </p>';
        }
        
        $content .= '<p style="margin: 20px 0 0 0; color: #495057; font-size: 14px; line-height: 1.6;">
            Please log in to the system to review this report and take appropriate action.
        </p>';
        
        $htmlBody = getEmailTemplate('Equipment ' . $type_label . ' Report', $content);
        
        foreach ($adminUsers as $admin) {
            $phpmailer = getMailer();
            $phpmailer->addAddress($admin['email']);
            
            $phpmailer->Subject = 'Equipment ' . $type_label . ' Report - ' . $report['equipment_name'];
            $phpmailer->Body = $htmlBody;
            $phpmailer->AltBody = "Equipment {$type_label} Report\n\nEquipment: " . $report['equipment_name'] . "\nReported By: " . $report['reporter_name'] . "\nQuantity: " . $report['quantity'];
            
            $phpmailer->send();
        }
        
        return true;
    } catch (Exception $e) {
        return false;
    }
}
?>

<!-- Page Header -->
<div class="mb-6">
    <h2 class="text-3xl font-bold text-white">Report Damage Item <span class="text-red-600">!</span></h2>
    <p class="text-white mt-2">Report damage to equipment you have borrowed</p>
</div>

<!-- Form Container -->
<div class="max-w-3xl mx-auto bg-white rounded-lg shadow-sm p-6">
    <?php if ($success): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if (count($equipment) === 0): ?>
        <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-4">
            You have no borrowed equipment to report. You can only report damage for equipment you have currently borrowed.
        </div>
    <?php endif; ?>
    
    <form method="POST" action="" enctype="multipart/form-data" class="space-y-5">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label for="equipment_id" class="block text-sm font-medium text-gray-700 mb-2">What Item Was Damaged *</label>
                <select id="equipment_id" name="equipment_id" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent" <?php echo count($equipment) === 0 ? 'disabled' : ''; ?> onchange="updateMaxQuantity()">
                    <option value="">Select an item</option>
                    <?php foreach ($equipment as $equip): ?>
                        <option value="<?php echo $equip['id']; ?>" data-max-quantity="<?php echo $equip['remaining_quantity']; ?>" <?php echo ($equipment_id ?? 0) == $equip['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($equip['name']); ?> (<?php echo $equip['remaining_quantity']; ?> borrowed)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label for="report_date" class="block text-sm font-medium text-gray-700 mb-2">Possible Date Damaged *</label>
                <input type="date" id="report_date" name="report_date" required value="<?php echo htmlspecialchars($report_date ?? date('Y-m-d')); ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
            </div>
        </div>
        
        <div>
            <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">Number of Items Damaged *</label>
            <input type="number" id="quantity" name="quantity" min="1" max="1" required value="<?php echo htmlspecialchars($quantity ?? 1); ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
            <p class="text-xs text-gray-500 mt-1">Cannot exceed the number of items you borrowed</p>
        </div>
        
        <div>
            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Detailed Description *</label>
            <textarea id="description" name="description" rows="5" maxlength="500" required placeholder="Max 500 characters" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"><?php echo htmlspecialchars($description ?? ''); ?></textarea>
            <p class="text-xs text-gray-500 mt-1">Maximum 500 characters</p>
        </div>
        
        <div>
            <label for="damage_image" class="block text-sm font-medium text-gray-700 mb-2">Upload Image (Optional)</label>
            <input type="file" id="damage_image" name="damage_image" accept="image/*" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
            <p class="text-xs text-gray-500 mt-1">Accepted formats: JPG, JPEG, PNG, GIF</p>
        </div>
        
        <div class="flex gap-3 pt-4">
            <button type="submit" class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition" <?php echo count($equipment) === 0 ? 'disabled' : ''; ?>>Submit Report</button>
            <a href="/staff-dashboard.php" class="px-6 py-3 bg-gray-400 hover:bg-gray-500 text-white font-semibold rounded-lg transition">Cancel</a>
        </div>
    </form>
</div>

<script>
function updateMaxQuantity() {
    const equipmentSelect = document.getElementById('equipment_id');
    const quantityInput = document.getElementById('quantity');
    
    const selectedOption = equipmentSelect.options[equipmentSelect.selectedIndex];
    const maxQuantity = selectedOption.getAttribute('data-max-quantity');
    
    if (maxQuantity) {
        quantityInput.max = maxQuantity;
        // If current quantity exceeds new max, adjust it
        if (parseInt(quantityInput.value) > parseInt(maxQuantity)) {
            quantityInput.value = maxQuantity;
        }
    } else {
        quantityInput.max = 1;
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateMaxQuantity();
});
</script>

<?php require_once '../includes/footer.php'; ?>
