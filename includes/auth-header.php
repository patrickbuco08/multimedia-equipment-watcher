<?php
require_once __DIR__ . '/../config/database.php';

// Get current page for active state
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Multimedia Equipment Watcher'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/assets/css/background.css">
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

<body class="bg-enhanced">
    <!-- Simple Auth Header -->
    <header class="bg-white shadow-sm sticky top-0 z-50 content-enhanced">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <!-- Logo -->
                <div class="flex items-center space-x-2">
                    <h1 class="text-xl font-bold text-gray-800">Multimedia Equipment Watcher</h1>
                </div>
                
                <!-- Auth Navigation -->
                <nav class="flex items-center space-x-6">
                    <a href="/about.php" class="text-gray-600 hover:text-primary font-medium transition">
                        About Us
                    </a>
                    <?php if ($currentPage === 'index.php'): ?>
                        <a href="/register.php" class="text-primary hover:text-primary-dark font-medium transition">
                            Register
                        </a>
                    <?php elseif ($currentPage === 'register.php'): ?>
                        <a href="/index.php" class="text-primary hover:text-primary-dark font-medium transition">
                            Login
                        </a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </header>

    <!-- Auth Content -->
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 content-enhanced">
