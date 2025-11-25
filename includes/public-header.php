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
    <!-- Public Header -->
    <header class="bg-white shadow-sm sticky top-0 z-50 content-enhanced">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <!-- Logo -->
                <div class="flex items-center space-x-2">
                    <h1 class="text-xl font-bold text-gray-800">Multimedia Equipment Watcher</h1>
                </div>
                
                <!-- Public Navigation -->
                <nav class="flex items-center space-x-6">
                    <a href="/about.php" class="text-primary font-medium transition">
                        About Us
                    </a>
                    <a href="/index.php" class="text-gray-600 hover:text-primary font-medium transition">
                        Login
                    </a>
                    <a href="/register.php" class="bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg font-medium transition">
                        Register
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Public Content -->
    <div class="content-enhanced">
