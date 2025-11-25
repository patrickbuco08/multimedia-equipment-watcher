<?php
$pageTitle = 'Edit Equipment - Multimedia Equipment Watcher';
require_once '../includes/header.php';
requireAdmin();

$pdo = getDBConnection();
$success = '';
$error = '';

$id = $_GET['id'] ?? 0;

// Fetch equipment data
try {
    $stmt = $pdo->prepare("SELECT * FROM equipment WHERE id = ?");
    $stmt->execute([$id]);
    $equipment = $stmt->fetch();
    
    if (!$equipment) {
        header('Location: list.php');
        exit;
    }
} catch (Exception $e) {
    header('Location: list.php');
    exit;
}

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
            // Calculate new available quantity based on difference
            $quantity_diff = $total_quantity - $equipment['total_quantity'];
            $new_available = $equipment['available_quantity'] + $quantity_diff;
            
            // Ensure available quantity doesn't go negative
            if ($new_available < 0) {
                $new_available = 0;
            }
            
            $stmt = $pdo->prepare("UPDATE equipment SET name = ?, description = ?, category = ?, total_quantity = ?, available_quantity = ?, status = ? WHERE id = ?");
            $stmt->execute([$name, $description, $category, $total_quantity, $new_available, $status, $id]);
            $success = 'Equipment updated successfully!';
            
            // Refresh equipment data
            $equipment['name'] = $name;
            $equipment['description'] = $description;
            $equipment['category'] = $category;
            $equipment['total_quantity'] = $total_quantity;
            $equipment['available_quantity'] = $new_available;
            $equipment['status'] = $status;
        } catch (Exception $e) {
            $error = 'Error updating equipment: ' . $e->getMessage();
        }
    }
}
?>

<!-- Page Header -->
<div class="flex justify-between items-center mb-6">
    <h2 class="text-3xl font-bold text-gray-800">Edit Equipment</h2>
    <a href="list.php" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg transition">← Back to List</a>
</div>

<!-- Form Container -->
<div class="max-w-3xl bg-white rounded-lg shadow-sm p-6">
    <?php if ($success): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <form method="POST" action="" class="space-y-5">
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Equipment Name *</label>
            <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($equipment['name']); ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
        </div>
        
        <div>
            <label for="category" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
            <input type="text" id="category" name="category" placeholder="e.g., Camera, Audio, Lighting" value="<?php echo htmlspecialchars($equipment['category']); ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
        </div>
        
        <div>
            <label for="total_quantity" class="block text-sm font-medium text-gray-700 mb-2">Total Quantity *</label>
            <input type="number" id="total_quantity" name="total_quantity" min="1" required value="<?php echo htmlspecialchars($equipment['total_quantity']); ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
            <p class="text-xs text-gray-500 mt-1">Available: <?php echo $equipment['available_quantity']; ?> | Total: <?php echo $equipment['total_quantity']; ?></p>
        </div>
        
        <div>
            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
            <textarea id="description" name="description" rows="4" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"><?php echo htmlspecialchars($equipment['description']); ?></textarea>
        </div>
        
        <div>
            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select id="status" name="status" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                <option value="available" <?php echo $equipment['status'] === 'available' ? 'selected' : ''; ?>>Available</option>
                <option value="borrowed" <?php echo $equipment['status'] === 'borrowed' ? 'selected' : ''; ?>>Borrowed</option>
                <option value="damaged" <?php echo $equipment['status'] === 'damaged' ? 'selected' : ''; ?>>Damaged</option>
                <option value="lost" <?php echo $equipment['status'] === 'lost' ? 'selected' : ''; ?>>Lost</option>
            </select>
        </div>
        
        <div class="flex gap-3 pt-4">
            <button type="submit" class="px-6 py-3 bg-primary hover:bg-primary-dark text-white font-semibold rounded-lg transition">Update Equipment</button>
            <a href="list.php" class="px-6 py-3 bg-gray-400 hover:bg-gray-500 text-white font-semibold rounded-lg transition">Cancel</a>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
