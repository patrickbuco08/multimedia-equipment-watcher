<?php
$pageTitle = 'My Dashboard - Multimedia Equipment Watcher';
require_once 'includes/header.php';

$pdo = getDBConnection();
$userId = $_SESSION['user_id'];

// Get staff's borrowed items
$myBorrowings = $pdo->prepare("
    SELECT 
        bt.*,
        e.name as equipment_name,
        e.category,
        u.name as borrower_name
    FROM borrowing_transactions bt
    JOIN equipment e ON bt.equipment_id = e.id
    JOIN users u ON bt.user_id = u.id
    WHERE bt.status IN ('pending', 'borrowed', 'partially_returned') AND bt.user_id = ?
    ORDER BY bt.due_date ASC
");
$myBorrowings->execute([$userId]);
$borrowings = $myBorrowings->fetchAll();

// Get statistics
$totalBorrowed = 0;
$pendingCount = 0;
$overdueCount = 0;
$dueSoon = 0;

foreach ($borrowings as $item) {
    if ($item['status'] === 'pending') {
        $pendingCount++;
    } elseif ($item['status'] === 'borrowed' || $item['status'] === 'partially_returned') {
        $totalBorrowed++;
        if ($item['due_date'] < date('Y-m-d')) {
            $overdueCount++;
        } elseif ($item['due_date'] <= date('Y-m-d', strtotime('+3 days'))) {
            $dueSoon++;
        }
    }
}
?>

<!-- Page Header -->
<div class="mb-8">
    <h2 class="text-3xl font-bold text-white">My Dashboard</h2>
    <p class="text-gray-100 mt-2">Welcome, <?php echo htmlspecialchars($currentUser['name']); ?>!</p>
</div>
<div class="mb-8">
    <a href="borrowing/add.php" class="px-4 py-2 bg-primary hover:bg-primary-dark text-white font-semibold rounded-lg transition">+ New Borrow</a>
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <!-- Pending -->
    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
        <h3 class="text-sm font-medium text-gray-500 uppercase mb-2">Pending Approval</h3>
        <p class="text-4xl font-bold text-gray-800"><?php echo $pendingCount; ?></p>
    </div>
    
    <!-- Total Borrowed -->
    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
        <h3 class="text-sm font-medium text-gray-500 uppercase mb-2">Currently Borrowed</h3>
        <p class="text-4xl font-bold text-gray-800"><?php echo $totalBorrowed; ?></p>
    </div>
    
    <!-- Due Soon -->
    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-yellow-500">
        <h3 class="text-sm font-medium text-gray-500 uppercase mb-2">Due Soon (3 days)</h3>
        <p class="text-4xl font-bold text-gray-800"><?php echo $dueSoon; ?></p>
    </div>
    
    <!-- Overdue -->
    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-red-500">
        <h3 class="text-sm font-medium text-gray-500 uppercase mb-2">Overdue Items</h3>
        <p class="text-4xl font-bold text-gray-800"><?php echo $overdueCount; ?></p>
    </div>
</div>

<!-- My Borrowed Items -->
<div class="bg-white rounded-lg shadow-sm p-6">
    <h3 class="text-xl font-semibold text-gray-800 mb-6">My Borrowed Items</h3>
    
    <?php if (count($borrowings) > 0): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Equipment</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Borrower</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Borrowed</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($borrowings as $item): ?>
                        <?php
                        $statusClass = 'bg-gray-100 text-gray-800';
                        $statusText = ucfirst(str_replace('_', ' ', $item['status']));
                        
                        if ($item['status'] === 'pending') {
                            $statusClass = 'bg-blue-100 text-blue-800';
                            $statusText = 'Pending Approval';
                        } elseif ($item['status'] === 'partially_returned') {
                            $statusClass = 'bg-orange-100 text-orange-800';
                            $statusText = 'Partially Returned';
                        } elseif ($item['due_date'] < date('Y-m-d')) {
                            $statusClass = 'bg-red-100 text-red-800';
                            $statusText = 'Overdue';
                        } elseif ($item['due_date'] <= date('Y-m-d', strtotime('+3 days'))) {
                            $statusClass = 'bg-orange-100 text-orange-800';
                            $statusText = 'Due Soon';
                        } elseif ($item['status'] === 'borrowed') {
                            $statusClass = 'bg-yellow-100 text-yellow-800';
                            $statusText = 'Borrowed';
                        }
                        ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($item['equipment_name']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo htmlspecialchars($item['category']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo htmlspecialchars($item['borrower_name']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo $item['quantity']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo date('M d, Y', strtotime($item['date_borrowed'])); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo date('M d, Y', strtotime($item['due_date'])); ?></td>
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
            <h3 class="mt-2 text-lg font-medium text-gray-900">No Borrowed Items</h3>
            <p class="mt-1 text-sm text-gray-500">You currently have no items borrowed.</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
