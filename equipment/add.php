<?php
$pageTitle = 'Add Equipment - Multimedia Equipment Watcher';
require_once '../config/database.php';
requireAdmin();
require_once '../includes/header.php';

$pdo = getDBConnection();
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $total_quantity = intval($_POST['total_quantity'] ?? 1);
    $status = $_POST['status'] ?? 'available';
    
    if (empty($name)) {
        $error = 'Equipment name is required';
    } elseif ($total_quantity < 1) {
        $error = 'Quantity must be at least 1';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO equipment (name, description, category, total_quantity, available_quantity, status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $description, $category, $total_quantity, $total_quantity, $status]);
            $success = 'Equipment added successfully!';
            
            // Clear form
            $name = $description = $category = '';
            $total_quantity = 1;
            $status = 'available';
        } catch (Exception $e) {
            $error = 'Error adding equipment: ' . $e->getMessage();
        }
    }
}
?>

<!-- Page Header -->
<div class="flex justify-between items-center mb-6">
    <h2 class="text-3xl font-bold text-white">Add New Equipment</h2>
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
    
    <form method="POST" action="" class="space-y-5">
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Equipment Name *</label>
            <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($name ?? ''); ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
        </div>
        
        <div>
            <label for="category" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
            <input type="text" id="category" name="category" placeholder="e.g., Camera, Audio, Lighting" value="<?php echo htmlspecialchars($category ?? ''); ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
        </div>
        
        <div>
            <label for="total_quantity" class="block text-sm font-medium text-gray-700 mb-2">Total Quantity *</label>
            <input type="number" id="total_quantity" name="total_quantity" min="1" required value="<?php echo htmlspecialchars($total_quantity ?? 1); ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
            <p class="text-xs text-gray-500 mt-1">Total number of items in stock</p>
        </div>
        
        <div>
            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
            <textarea id="description" name="description" rows="4" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"><?php echo htmlspecialchars($description ?? ''); ?></textarea>
        </div>
        
        <div>
            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select id="status" name="status" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                <option value="available" <?php echo ($status ?? 'available') === 'available' ? 'selected' : ''; ?>>Available</option>
            </select>
        </div>
        
        <div class="flex gap-3 pt-4">
            <button type="submit" class="px-6 py-3 bg-primary hover:bg-primary-dark text-white font-semibold rounded-lg transition">Add Equipment</button>
            <a href="list.php" class="px-6 py-3 bg-gray-400 hover:bg-gray-500 text-white font-semibold rounded-lg transition">Cancel</a>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
