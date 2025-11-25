<?php
$pageTitle = 'Borrowing Transactions - Multimedia Equipment Watcher';
require_once '../config/database.php';
require_once '../config/database.php';
requireAdmin();
require_once '../includes/header.php';

$pdo = getDBConnection();

// Handle search and filters
$search = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? '';

$sql = "SELECT bt.*, e.name as equipment_name, u.name as borrower_name, u.email as borrower_email
        FROM borrowing_transactions bt
        JOIN equipment e ON bt.equipment_id = e.id
        JOIN users u ON bt.user_id = u.id
        WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (e.name LIKE ? OR u.name LIKE ? OR u.email LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

if (!empty($statusFilter)) {
    if ($statusFilter === 'overdue') {
        $sql .= " AND bt.status = 'borrowed' AND bt.due_date < CURDATE()";
    } else {
        $sql .= " AND bt.status = ?";
        $params[] = $statusFilter;
    }
}

$sql .= " ORDER BY bt.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$transactions = $stmt->fetchAll();
?>

<!-- Page Header -->
<div class="flex justify-between items-center mb-6">
    <h2 class="text-3xl font-bold text-white">Borrowing Transactions</h2>
    <a href="add.php" class="px-4 py-2 bg-primary hover:bg-primary-dark text-white font-semibold rounded-lg transition">+ New Borrow</a>
</div>

<!-- Content Box -->
<div class="bg-white rounded-lg shadow-sm p-6">
    <!-- Search and Filter Form -->
    <form method="GET" class="mb-6 flex flex-wrap gap-3">
        <input type="text" name="search" placeholder="Search transactions..." 
               value="<?php echo htmlspecialchars($search); ?>" 
               class="flex-1 min-w-[200px] px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
        
        <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
            <option value="">All Status</option>
            <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
            <option value="borrowed" <?php echo $statusFilter === 'borrowed' ? 'selected' : ''; ?>>Borrowed</option>
            <option value="returned" <?php echo $statusFilter === 'returned' ? 'selected' : ''; ?>>Returned</option>
            <option value="partially_returned" <?php echo $statusFilter === 'partially_returned' ? 'selected' : ''; ?>>Partially Returned</option>
            <option value="lost" <?php echo $statusFilter === 'lost' ? 'selected' : ''; ?>>Lost</option>
        </select>
        
        <button type="submit" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition">Filter</button>
        <?php if ($search || $statusFilter): ?>
            <a href="list.php" class="px-4 py-2 bg-gray-400 hover:bg-gray-500 text-white font-medium rounded-lg transition">Clear</a>
        <?php endif; ?>
    </form>
    
    <?php if (count($transactions) > 0): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Equipment</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Borrower</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Returned</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Borrowed</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Returned</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($transactions as $transaction): ?>
                        <?php
                        $statusClass = 'bg-gray-100 text-gray-800';
                        $statusText = ucfirst(str_replace('_', ' ', $transaction['status']));
                        $isOverdue = (in_array($transaction['status'], ['borrowed', 'partially_returned']) && $transaction['due_date'] < date('Y-m-d'));

                        if ($transaction['status'] === 'pending') {
                            $statusClass = 'bg-blue-100 text-blue-800';
                        } elseif ($transaction['status'] === 'returned') {
                            $statusClass = 'bg-green-100 text-green-800';
                        } elseif ($transaction['status'] === 'partially_returned') {
                            $statusClass = 'bg-orange-100 text-orange-800';
                        } elseif ($transaction['status'] === 'lost') {
                            $statusClass = 'bg-purple-100 text-purple-800';
                        } elseif ($isOverdue) {
                            $statusClass = 'bg-red-100 text-red-800';
                            $statusText = 'Overdue';
                        } elseif ($transaction['status'] === 'borrowed') {
                            $statusClass = 'bg-yellow-100 text-yellow-800';
                        }
                        ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?php echo htmlspecialchars($transaction['equipment_name']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <?php echo htmlspecialchars($transaction['borrower_name']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <?php echo htmlspecialchars($transaction['borrower_email']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <?php echo $transaction['quantity']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <?php echo $transaction['quantity_returned'] ?? 0; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <?php echo date('M d, Y', strtotime($transaction['date_borrowed'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <?php echo date('M d, Y', strtotime($transaction['due_date'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <?php echo $transaction['date_returned'] ? date('M d, Y', strtotime($transaction['date_returned'])) : '-'; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusClass; ?>">
                                    <?php echo $statusText; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <?php if (isAdmin()): ?>
                                    <?php if ($transaction['status'] === 'pending'): ?>
                                        <a href="edit-status.php?id=<?php echo $transaction['id']; ?>" class="text-blue-600 hover:text-blue-800 font-medium mr-3">Approve</a>
                                    <?php elseif (in_array($transaction['status'], ['borrowed', 'partially_returned'])): ?>
                                        <a href="return.php?id=<?php echo $transaction['id']; ?>" class="text-primary hover:text-primary-dark font-medium mr-3">Return</a>
                                    <?php endif; ?>
                                    <a href="edit-status.php?id=<?php echo $transaction['id']; ?>" class="text-blue-600 hover:text-blue-800 font-medium">Edit</a>
                                <?php else: ?>
                                    <span class="text-gray-400">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M5 7V5a2 2 0 012-2h10a2 2 0 012 2v2m-2 4h2m-8 0h2m-8 0h2m-4 4h16v2a2 2 0 01-2 2H5a2 2 0 01-2-2v-2z" />
            </svg>
            <h3 class="mt-2 text-lg font-medium text-gray-900">No Transactions Found</h3>
            <p class="mt-1 text-sm text-gray-500">Start by creating a new borrow transaction.</p>
            <a href="add.php" class="mt-4 inline-block px-4 py-2 bg-primary hover:bg-primary-dark text-white font-semibold rounded-lg transition">+ New Borrow</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
