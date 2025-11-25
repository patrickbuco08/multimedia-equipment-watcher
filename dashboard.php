<?php
$pageTitle = 'Dashboard - Multimedia Equipment Watcher';
require_once './config/database.php';
requireAdmin();
require_once 'includes/header.php';

$pdo = getDBConnection();

// Get statistics
$totalEquipment = $pdo->query("SELECT COUNT(*) FROM equipment")->fetchColumn();
$availableEquipment = $pdo->query("SELECT COUNT(*) FROM equipment WHERE status = 'available'")->fetchColumn();
$borrowedEquipment = $pdo->query("SELECT COUNT(*) FROM equipment WHERE status = 'borrowed'")->fetchColumn();
$overdueTransactions = $pdo->query("SELECT COUNT(*) FROM borrowing_transactions WHERE status = 'borrowed' AND due_date < CURDATE()")->fetchColumn();

// Get recent transactions
$recentTransactions = $pdo->query("
    SELECT bt.*, e.name as equipment_name, u.name as borrower_name
    FROM borrowing_transactions bt
    JOIN equipment e ON bt.equipment_id = e.id
    JOIN users u ON bt.user_id = u.id
    ORDER BY bt.created_at DESC
    LIMIT 5
")->fetchAll();
?>

<!-- Page Header -->
<div class="mb-8">
    <h2 class="text-3xl font-bold text-white">Dashboard</h2>
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Equipment -->
    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-primary">
        <h3 class="text-sm font-medium text-gray-500 uppercase mb-2">Total Equipment</h3>
        <p class="text-4xl font-bold text-gray-800"><?php echo $totalEquipment; ?></p>
    </div>

    <!-- Available -->
    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
        <h3 class="text-sm font-medium text-gray-500 uppercase mb-2">Available</h3>
        <p class="text-4xl font-bold text-gray-800"><?php echo $availableEquipment; ?></p>
    </div>
    
    <!-- Currently Borrowed -->
    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-yellow-500">
        <h3 class="text-sm font-medium text-gray-500 uppercase mb-2">Currently Borrowed</h3>
        <p class="text-4xl font-bold text-gray-800"><?php echo $borrowedEquipment; ?></p>
    </div>
    
    <!-- Overdue Items -->
    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-red-500">
        <h3 class="text-sm font-medium text-gray-500 uppercase mb-2">Overdue Items</h3>
        <p class="text-4xl font-bold text-gray-800"><?php echo $overdueTransactions; ?></p>
    </div>
</div>

<!-- Recent Transactions -->
<div class="bg-white rounded-lg shadow-sm p-6">
    <h3 class="text-xl font-semibold text-gray-800 mb-6">Recent Transactions</h3>
    
    <?php if (count($recentTransactions) > 0): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Equipment</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Borrower</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Borrowed</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($recentTransactions as $transaction): ?>
                        <?php
                        $statusClass = 'bg-gray-100 text-gray-800';
                        $statusText = ucfirst($transaction['status']);
                        
                        if ($transaction['status'] === 'returned') {
                            $statusClass = 'bg-green-100 text-green-800';
                        } elseif ($transaction['status'] === 'borrowed' && $transaction['due_date'] < date('Y-m-d')) {
                            $statusClass = 'bg-red-100 text-red-800';
                            $statusText = 'Overdue';
                        } elseif ($transaction['status'] === 'borrowed') {
                            $statusClass = 'bg-yellow-100 text-yellow-800';
                        }
                        ?>
                        <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location.href='/borrowing/edit-status.php?id=<?php echo $transaction['id']; ?>'">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($transaction['equipment_name']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo htmlspecialchars($transaction['borrower_name'] ?? 'Unknown'); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo date('M d, Y', strtotime($transaction['date_borrowed'])); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo date('M d, Y', strtotime($transaction['due_date'])); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusClass; ?>">
                                    <?php echo $statusText; ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <h3 class="mt-2 text-lg font-medium text-gray-900">No Transactions Yet</h3>
            <p class="mt-1 text-sm text-gray-500">Start by adding equipment and creating borrow transactions.</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
