<?php
$pageTitle = 'Staff Management - Multimedia Equipment Watcher';
require_once '../includes/header.php';
requireAdmin();

$pdo = getDBConnection();

// Get all users
$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
?>

<!-- Page Header -->
<div class="flex justify-between items-center mb-6">
    <h2 class="text-3xl font-bold text-gray-800">Staff Management</h2>
    <a href="add.php" class="px-4 py-2 bg-primary hover:bg-primary-dark text-white font-semibold rounded-lg transition">+ Add Staff</a>
</div>

<!-- Content Box -->
<div class="bg-white rounded-lg shadow-sm p-6">
    <?php if (count($users) > 0): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($users as $user): ?>
                        <?php
                        $roleClass = $user['role'] === 'admin' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800';
                        ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($user['name']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo htmlspecialchars($user['email']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $roleClass; ?>">
                                    <?php echo ucfirst($user['role']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <a href="edit.php?id=<?php echo $user['id']; ?>" class="text-primary hover:text-primary-dark font-medium mr-3">Edit</a>
                                <?php if ($user['id'] != getCurrentUser()['id']): ?>
                                    <a href="delete.php?id=<?php echo $user['id']; ?>" class="text-red-600 hover:text-red-800 font-medium" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
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
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
            </svg>
            <h3 class="mt-2 text-lg font-medium text-gray-900">No Users Found</h3>
            <p class="mt-1 text-sm text-gray-500">Start by adding staff members.</p>
            <a href="add.php" class="mt-4 inline-block px-4 py-2 bg-primary hover:bg-primary-dark text-white font-semibold rounded-lg transition">+ Add Staff</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
