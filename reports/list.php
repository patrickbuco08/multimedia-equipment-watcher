<?php
$pageTitle = 'Reports List - Multimedia Equipment Watcher';
require_once '../config/database.php';
requireAdmin();
require_once '../includes/header.php';

$pdo = getDBConnection();

// Get all reports with equipment and user info
$reports = $pdo->query("
    SELECT 
        er.*,
        e.name as equipment_name,
        u.name as reporter_name
    FROM equipment_reports er
    JOIN equipment e ON er.equipment_id = e.id
    JOIN users u ON er.reported_by = u.id
    ORDER BY er.created_at DESC
")->fetchAll();
?>

<!-- Page Header -->
<div class="flex justify-between items-center mb-6">
    <h2 class="text-3xl font-bold text-white">Equipment Reports</h2>
</div>

<!-- Content Box -->
<div class="bg-white rounded-lg shadow-sm p-6">
    <?php if (count($reports) > 0): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Equipment</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reported By</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Report Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Image</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($reports as $report): ?>
                        <?php
                        $typeClass = $report['report_type'] === 'damage' ? 'bg-red-100 text-red-800' : 'bg-orange-100 text-orange-800';
                        ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($report['equipment_name']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $typeClass; ?>">
                                    <?php echo ucfirst($report['report_type']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo htmlspecialchars($report['reporter_name']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo date('M d, Y', strtotime($report['report_date'])); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo $report['quantity']; ?></td>
                            <td class="px-6 py-4 text-sm text-gray-700"><?php echo htmlspecialchars(substr($report['description'], 0, 80)) . (strlen($report['description']) > 80 ? '...' : ''); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <?php if ($report['image_path']): ?>
                                    <a href="<?php echo htmlspecialchars($report['image_path']); ?>" target="_blank" class="text-blue-600 hover:text-blue-800">View Image</a>
                                <?php else: ?>
                                    <span class="text-gray-400">No image</span>
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
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <h3 class="mt-2 text-lg font-medium text-gray-900">No Reports Yet</h3>
            <p class="mt-1 text-sm text-gray-500">Staff members can report damaged or lost equipment.</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
