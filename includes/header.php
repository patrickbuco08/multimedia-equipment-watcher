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
    <!-- Responsive Header -->
    <header class="bg-white shadow-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Desktop Header -->
            <div class="hidden lg:flex justify-between items-center py-4">
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
                        <?php else: ?>
                            <a href="/staff-dashboard.php" class="px-3 py-2 text-sm font-medium rounded-md transition <?php echo $currentPage === 'staff-dashboard.php' ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100'; ?>">My Dashboard</a>
                            <a href="/reports/damage.php" class="px-3 py-2 text-sm font-medium rounded-md transition <?php echo $currentPage === 'damage.php' ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100'; ?>">Report Damage</a>
                            <a href="/reports/lost.php" class="px-3 py-2 text-sm font-medium rounded-md transition <?php echo $currentPage === 'lost.php' ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100'; ?>">Report Lost</a>
                        <?php endif; ?>
                    </nav>
                </div>
                
                <!-- Desktop User Profile Dropdown -->
                <div class="relative">
                    <button onclick="toggleUserDropdown('desktop')" class="flex items-center space-x-3 text-sm rounded-lg hover:bg-gray-100 p-2 transition">
                        <div class="w-8 h-8 bg-primary rounded-full flex items-center justify-center text-white font-semibold">
                            <?php echo strtoupper(substr($currentUser['name'], 0, 1)); ?>
                        </div>
                        <div class="text-left">
                            <p class="text-sm font-semibold text-gray-800"><?php echo htmlspecialchars($currentUser['name']); ?></p>
                            <p class="text-xs text-gray-500 uppercase"><?php echo htmlspecialchars($currentUser['role']); ?></p>
                        </div>
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    
                    <!-- Desktop Dropdown Menu -->
                    <div id="desktop-dropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200">
                        <div class="py-1">
                            <a href="/logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition">
                                <div class="flex items-center space-x-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                    </svg>
                                    <span>Logout</span>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile Header -->
            <div class="lg:hidden">
                <!-- Mobile Top Bar -->
                <div class="flex justify-between items-center py-3">
                    <div class="flex items-center space-x-2">
                        <h1 class="text-base font-bold text-gray-800">Equipment Watcher</h1>
                    </div>
                    
                    <!-- Mobile Right Side: Hamburger + User Profile -->
                    <div class="flex items-center space-x-2">
                        <!-- Mobile User Profile Button -->
                        <button onclick="toggleUserDropdown('mobile')" class="flex items-center space-x-2 text-sm rounded-lg hover:bg-gray-100 p-2 transition">
                            <div class="w-7 h-7 bg-primary rounded-full flex items-center justify-center text-white text-sm font-semibold">
                                <?php echo strtoupper(substr($currentUser['name'], 0, 1)); ?>
                            </div>
                        </button>
                        
                        <!-- Hamburger Menu Button -->
                        <button onclick="toggleMobileMenu()" class="p-2 rounded-lg hover:bg-gray-100 transition">
                            <svg id="hamburger-icon" class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                            <svg id="close-icon" class="hidden w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Mobile User Dropdown -->
                <div id="mobile-dropdown" class="hidden border-t border-gray-200 bg-gray-50">
                    <div class="px-4 py-3 border-b border-gray-200">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-primary rounded-full flex items-center justify-center text-white font-semibold">
                                <?php echo strtoupper(substr($currentUser['name'], 0, 1)); ?>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-800"><?php echo htmlspecialchars($currentUser['name']); ?></p>
                                <p class="text-xs text-gray-500 uppercase"><?php echo htmlspecialchars($currentUser['role']); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="py-2">
                        <a href="/logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                </svg>
                                <span>Logout</span>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Mobile Navigation Menu (Hidden by default) -->
                <nav id="mobile-menu" class="hidden bg-gray-50 border-t border-gray-200">
                    <div class="px-2 py-2 space-y-1">
                        <?php if (isAdmin()): ?>
                            <a href="/dashboard.php" class="block px-3 py-2 text-sm font-medium rounded-md transition <?php echo $currentPage === 'dashboard.php' ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100'; ?>">Dashboard</a>
                            <a href="/equipment/list.php" class="block px-3 py-2 text-sm font-medium rounded-md transition <?php echo strpos($_SERVER['PHP_SELF'], '/equipment/') !== false ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100'; ?>">Equipment</a>
                            <a href="/borrowing/list.php" class="block px-3 py-2 text-sm font-medium rounded-md transition <?php echo strpos($_SERVER['PHP_SELF'], '/borrowing/') !== false ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100'; ?>">Borrowing</a>
                            <a href="/overdue/list.php" class="block px-3 py-2 text-sm font-medium rounded-md transition <?php echo strpos($_SERVER['PHP_SELF'], '/overdue/') !== false ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100'; ?>">Overdue</a>
                            <a href="/reports/list.php" class="block px-3 py-2 text-sm font-medium rounded-md transition <?php echo strpos($_SERVER['PHP_SELF'], '/reports/') !== false ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100'; ?>">Reports</a>
                            <a href="/staff/list.php" class="block px-3 py-2 text-sm font-medium rounded-md transition <?php echo strpos($_SERVER['PHP_SELF'], '/staff/') !== false ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100'; ?>">Staff</a>
                        <?php else: ?>
                            <a href="/staff-dashboard.php" class="block px-3 py-2 text-sm font-medium rounded-md transition <?php echo $currentPage === 'staff-dashboard.php' ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100'; ?>">My Dashboard</a>
                            <a href="/reports/damage.php" class="block px-3 py-2 text-sm font-medium rounded-md transition <?php echo $currentPage === 'damage.php' ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100'; ?>">Report Damage</a>
                            <a href="/reports/lost.php" class="block px-3 py-2 text-sm font-medium rounded-md transition <?php echo $currentPage === 'lost.php' ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100'; ?>">Report Lost</a>
                        <?php endif; ?>
                    </div>
                </nav>
            </div>
        </div>
    </header>

    <!-- JavaScript for Dropdown Toggle -->
    <script>
        function toggleUserDropdown(type) {
            const dropdown = document.getElementById(type + '-dropdown');
            const otherDropdown = document.getElementById(type === 'desktop' ? 'mobile-dropdown' : 'desktop-dropdown');
            
            // Close other dropdown
            otherDropdown.classList.add('hidden');
            
            // Toggle current dropdown
            dropdown.classList.toggle('hidden');
            
            // Close dropdown when clicking outside
            if (!dropdown.classList.contains('hidden')) {
                setTimeout(() => {
                    document.addEventListener('click', closeDropdownOnClickOutside);
                }, 100);
            }
        }
        
        function toggleMobileMenu() {
            const mobileMenu = document.getElementById('mobile-menu');
            const hamburgerIcon = document.getElementById('hamburger-icon');
            const closeIcon = document.getElementById('close-icon');
            const mobileDropdown = document.getElementById('mobile-dropdown');
            
            // Close user dropdown when opening menu
            mobileDropdown.classList.add('hidden');
            
            // Toggle menu visibility
            mobileMenu.classList.toggle('hidden');
            hamburgerIcon.classList.toggle('hidden');
            closeIcon.classList.toggle('hidden');
            
            // Close menu when clicking outside
            if (!mobileMenu.classList.contains('hidden')) {
                setTimeout(() => {
                    document.addEventListener('click', closeMobileMenuOnClickOutside);
                }, 100);
            } else {
                document.removeEventListener('click', closeMobileMenuOnClickOutside);
            }
        }
        
        function closeDropdownOnClickOutside(event) {
            const desktopDropdown = document.getElementById('desktop-dropdown');
            const mobileDropdown = document.getElementById('mobile-dropdown');
            
            if (!event.target.closest('#desktop-dropdown') && !event.target.closest('#mobile-dropdown')) {
                desktopDropdown.classList.add('hidden');
                mobileDropdown.classList.add('hidden');
                document.removeEventListener('click', closeDropdownOnClickOutside);
            }
        }
        
        function closeMobileMenuOnClickOutside(event) {
            const mobileMenu = document.getElementById('mobile-menu');
            const hamburgerIcon = document.getElementById('hamburger-icon');
            const closeIcon = document.getElementById('close-icon');
            
            if (!event.target.closest('#mobile-menu') && !event.target.closest('[onclick="toggleMobileMenu()"]')) {
                mobileMenu.classList.add('hidden');
                hamburgerIcon.classList.remove('hidden');
                closeIcon.classList.add('hidden');
                document.removeEventListener('click', closeMobileMenuOnClickOutside);
            }
        }
    </script>
    
    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" >
