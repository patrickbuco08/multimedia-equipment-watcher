<?php
$pageTitle = 'Edit Transaction Status - Multimedia Equipment Watcher';
require_once '../config/database.php';
require_once '../config/database.php';
requireAdmin();
require_once '../includes/header.php';

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
            } else {
                // For other status transitions, ensure date_returned is null unless explicitly returned
                if ($status !== 'returned' && $status !== 'partially_returned') {
                    $date_returned = null;
                }
            }
            
            // Update transaction status
            $stmt = $pdo->prepare("UPDATE borrowing_transactions SET status = ?, date_returned = ?, quantity_returned = ? WHERE id = ?");
            $stmt->execute([$status, $date_returned, $quantity_returned, $id]);
            
            $pdo->commit();
            
            $success = 'Transaction status updated successfully!';
            
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
            <select id="status" name="status" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent" onchange="toggleQuantityReturned()">
                <option value="pending" <?php echo $transaction['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="borrowed" <?php echo $transaction['status'] === 'borrowed' ? 'selected' : ''; ?>>Borrowed</option>
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
        
        <div>
            <label for="date_returned" class="block text-sm font-medium text-gray-700 mb-2">Date Returned</label>
            <input type="date" id="date_returned" name="date_returned" value="<?php echo $transaction['date_returned'] ?? ''; ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
            <p class="text-xs text-gray-500 mt-1">Leave blank if not returned yet. Will auto-fill today's date when changing to "Returned".</p>
        </div>
        
        <div class="flex gap-3 pt-4">
            <button type="submit" class="px-6 py-3 bg-primary hover:bg-primary-dark text-white font-semibold rounded-lg transition">Update Status</button>
            <a href="list.php" class="px-6 py-3 bg-gray-400 hover:bg-gray-500 text-white font-semibold rounded-lg transition">Cancel</a>
        </div>
    </form>
</div>

<script>
function toggleQuantityReturned() {
    const status = document.getElementById('status').value;
    const qtyField = document.getElementById('quantity_returned_field');
    
    if (status === 'partially_returned') {
        qtyField.style.display = 'block';
    } else {
        qtyField.style.display = 'none';
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', toggleQuantityReturned);
</script>

<?php require_once '../includes/footer.php'; ?>
