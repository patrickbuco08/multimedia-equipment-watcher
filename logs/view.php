<?php
$pageTitle = 'Email Logs - Multimedia Equipment Watcher';
require_once '../includes/header.php';
requireAdmin();

$pdo = getDBConnection();

// Get email logs with transaction details
$logs = $pdo->query("
    SELECT el.*, bt.borrower_name, e.name as equipment_name
    FROM email_logs el
    JOIN borrowing_transactions bt ON el.transaction_id = bt.id
    JOIN equipment e ON bt.equipment_id = e.id
    ORDER BY el.sent_at DESC
")->fetchAll();
?>

<!-- Page Header -->
<div class="mb-6">
    <h2 class="text-3xl font-bold text-gray-800">Email Notification Logs</h2>
</div>

<!-- Content Box -->
<div class="bg-white rounded-lg shadow-sm p-6">
    <?php if (count($logs) > 0): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Sent</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Equipment</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Borrower</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email To</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($logs as $log): ?>
                        <?php
                        $statusClass = $log['status'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                        ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo date('M d, Y H:i', strtotime($log['sent_at'])); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($log['equipment_name']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo htmlspecialchars($log['borrower_name']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo htmlspecialchars($log['email_to']); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-700"><?php echo htmlspecialchars($log['subject']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusClass; ?>">
                                    <?php echo ucfirst($log['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <button onclick="alert('<?php echo addslashes($log['message']); ?>')" class="text-primary hover:text-primary-dark font-medium">View Message</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
            </svg>
            <h3 class="mt-2 text-lg font-medium text-gray-900">No Email Logs</h3>
            <p class="mt-1 text-sm text-gray-500">No email notifications have been sent yet.</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
