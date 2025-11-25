<?php
require_once __DIR__ . '/../config/database.php';
requireLogin();
$currentUser = getCurrentUser();
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Multimedia Equipment Watcher'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#2ecc71',
                        'primary-dark': '#27ae60',
                        secondary: '#4b4b4b',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50" style="background-image: url('/images/background.jpg'); background-size: cover; background-position: center; background-attachment: fixed;">
    <!-- Header - One Line -->
    <header class="bg-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <!-- Left: Logo + Navigation -->
                <div class="flex items-center space-x-6">
                    <div class="flex items-center space-x-2">
                        <h1 class="text-lg font-bold text-gray-800">Multimedia Equipment Watcher</h1>
                    </div>
                    <nav class="flex space-x-1">
                        <?php if (isAdmin()): ?>
                            <a href="/dashboard.php" class="px-3 py-2 text-sm font-medium rounded-md transition <?php echo $currentPage === 'dashboard.php' ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100'; ?>">Dashboard</a>
                            <a href="/equipment/list.php" class="px-3 py-2 text-sm font-medium rounded-md transition <?php echo strpos($_SERVER['PHP_SELF'], '/equipment/') !== false ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100'; ?>">Equipment</a>
                            <a href="/borrowing/list.php" class="px-3 py-2 text-sm font-medium rounded-md transition <?php echo strpos($_SERVER['PHP_SELF'], '/borrowing/') !== false ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100'; ?>">Borrowing</a>
                            <a href="/overdue/list.php" class="px-3 py-2 text-sm font-medium rounded-md transition <?php echo strpos($_SERVER['PHP_SELF'], '/overdue/') !== false ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100'; ?>">Overdue</a>
                            <a href="/reports/list.php" class="px-3 py-2 text-sm font-medium rounded-md transition <?php echo strpos($_SERVER['PHP_SELF'], '/reports/') !== false ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100'; ?>">Reports</a>
                            <a href="/staff/list.php" class="px-3 py-2 text-sm font-medium rounded-md transition <?php echo strpos($_SERVER['PHP_SELF'], '/staff/') !== false ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100'; ?>">Staff</a>
                            <!-- <a href="/logs/view.php" class="px-3 py-2 text-sm font-medium rounded-md transition <?php echo strpos($_SERVER['PHP_SELF'], '/logs/') !== false ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100'; ?>">Logs</a> -->
                        <?php else: ?>
                            <a href="/staff-dashboard.php" class="px-3 py-2 text-sm font-medium rounded-md transition <?php echo $currentPage === 'staff-dashboard.php' ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100'; ?>">My Dashboard</a>
                            <a href="/reports/damage.php" class="px-3 py-2 text-sm font-medium rounded-md transition <?php echo $currentPage === 'damage.php' ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100'; ?>">Report Damage</a>
                            <a href="/reports/lost.php" class="px-3 py-2 text-sm font-medium rounded-md transition <?php echo $currentPage === 'lost.php' ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100'; ?>">Report Lost</a>
                        <?php endif; ?>
                    </nav>
                </div>
                
                <!-- Right: User Info + Logout -->
                <div class="flex items-center space-x-4">
                    <div class="text-right">
                        <p class="text-sm font-semibold text-gray-800"><?php echo htmlspecialchars($currentUser['name']); ?></p>
                        <p class="text-xs text-gray-500 uppercase"><?php echo htmlspecialchars($currentUser['role']); ?></p>
                    </div>
                    <a href="/logout.php" class="px-4 py-2 bg-gray-600 text-white text-sm rounded-lg hover:bg-gray-700 transition">Logout</a>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" >
