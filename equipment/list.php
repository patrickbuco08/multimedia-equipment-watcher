<?php
$pageTitle = 'Equipment List - Multimedia Equipment Watcher';
require_once '../config/database.php';
requireAdmin();
require_once '../includes/header.php';

$pdo = getDBConnection();

// Handle search and filters
$search = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? '';

$sql = "SELECT * FROM equipment WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (name LIKE ? OR description LIKE ? OR category LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

if (!empty($statusFilter)) {
    $sql .= " AND status = ?";
    $params[] = $statusFilter;
}

$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$equipment = $stmt->fetchAll();
?>

<!-- Page Header -->
<div class="flex justify-between items-center mb-6">
    <h2 class="text-3xl font-bold text-white">Equipment Management</h2>
    <a href="add.php" class="px-4 py-2 bg-primary hover:bg-primary-dark text-white font-semibold rounded-lg transition">+ Add Equipment</a>
</div>

<!-- Content Box -->
<div class="bg-white rounded-lg shadow-sm p-6">
    <!-- Search and Filter Form -->
    <form method="GET" class="mb-6 flex flex-wrap gap-3">
        <input type="text" name="search" placeholder="Search equipment..." 
               value="<?php echo htmlspecialchars($search); ?>" 
               class="flex-1 min-w-[200px] px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
        
        <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
            <option value="">All Status</option>
            <option value="available" <?php echo $statusFilter === 'available' ? 'selected' : ''; ?>>Available</option>
            <option value="borrowed" <?php echo $statusFilter === 'borrowed' ? 'selected' : ''; ?>>Borrowed</option>
            <option value="damaged" <?php echo $statusFilter === 'damaged' ? 'selected' : ''; ?>>Damaged</option>
            <option value="lost" <?php echo $statusFilter === 'lost' ? 'selected' : ''; ?>>Lost</option>
        </select>
        
        <button type="submit" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition">Filter</button>
        <?php if ($search || $statusFilter): ?>
            <a href="list.php" class="px-4 py-2 bg-gray-400 hover:bg-gray-500 text-white font-medium rounded-lg transition">Clear</a>
        <?php endif; ?>
    </form>
    
    <?php if (count($equipment) > 0): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($equipment as $item): ?>
                        <?php
                        $statusClass = 'bg-gray-100 text-gray-800';
                        switch ($item['status']) {
                            case 'available': $statusClass = 'bg-green-100 text-green-800'; break;
                            case 'borrowed': $statusClass = 'bg-yellow-100 text-yellow-800'; break;
                            case 'damaged': $statusClass = 'bg-red-100 text-red-800'; break;
                            case 'lost': $statusClass = 'bg-red-100 text-red-800'; break;
                        }
                        ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($item['name']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo htmlspecialchars($item['category']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <span class="font-semibold text-primary"><?php echo $item['available_quantity']; ?></span> / <?php echo $item['total_quantity']; ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700"><?php echo htmlspecialchars(substr($item['description'], 0, 60)) . (strlen($item['description']) > 60 ? '...' : ''); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusClass; ?>">
                                    <?php echo ucfirst($item['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <a href="edit.php?id=<?php echo $item['id']; ?>" class="text-primary hover:text-primary-dark font-medium mr-3">Edit</a>
                                <a href="delete.php?id=<?php echo $item['id']; ?>" class="text-red-600 hover:text-red-800 font-medium" onclick="return confirm('Are you sure you want to delete this equipment?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
            </svg>
            <h3 class="mt-2 text-lg font-medium text-gray-900">No Equipment Found</h3>
            <p class="mt-1 text-sm text-gray-500">Start by adding your first equipment item.</p>
            <a href="add.php" class="mt-4 inline-block px-4 py-2 bg-primary hover:bg-primary-dark text-white font-semibold rounded-lg transition">+ Add Equipment</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
