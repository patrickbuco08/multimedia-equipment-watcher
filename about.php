<?php
$pageTitle = 'About Us - Multimedia Equipment Watcher';
require_once 'includes/public-header.php';
?>

<!-- Main Content -->
<main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 content-enhanced">
    <!-- Hero Card -->
    <div class="card-glass rounded-lg p-8 mb-8">
        <div class="text-center">
            <h1 class="text-4xl font-bold text-gray-800 mb-4">About Us</h1>
            <p class="text-xl text-gray-600 leading-relaxed max-w-3xl mx-auto">
                We provide a simple, efficient, and secure online system for tracking all borrowed
                items, from extension cords to sound systems. Our intuitive interface allows
                administrators to easily:
            </p>
        </div>
    </div>

    <!-- Features Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <!-- Record Log Card -->
        <div class="card-glass rounded-lg p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-center mb-4">
                <div class="flex-shrink-0 w-12 h-12 bg-primary rounded-full flex items-center justify-center text-white font-bold text-lg mr-4">1</div>
                <h3 class="text-xl font-semibold text-gray-800">Record Log</h3>
            </div>
            <p class="text-gray-600 leading-relaxed">
                Log every borrowed item, including the borrower's name, item borrowed, and date/time. 
                Keep comprehensive records of all equipment transactions.
            </p>
        </div>

        <!-- Monitor Inventory Card -->
        <div class="card-glass rounded-lg p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-center mb-4">
                <div class="flex-shrink-0 w-12 h-12 bg-primary rounded-full flex items-center justify-center text-white font-bold text-lg mr-4">2</div>
                <h3 class="text-xl font-semibold text-gray-800">Monitor Inventory</h3>
            </div>
            <p class="text-gray-600 leading-relaxed">
                Always know what's out, when it's due back, and who has it. 
                Real-time tracking ensures you never lose sight of your equipment.
            </p>
        </div>

        <!-- Track Damage Card -->
        <div class="card-glass rounded-lg p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-center mb-4">
                <div class="flex-shrink-0 w-12 h-12 bg-primary rounded-full flex items-center justify-center text-white font-bold text-lg mr-4">3</div>
                <h3 class="text-xl font-semibold text-gray-800">Track Damage</h3>
            </div>
            <p class="text-gray-600 leading-relaxed">
                Document damage upon return and assign responsibility. 
                Maintain equipment quality and accountability with detailed damage reports.
            </p>
        </div>

        <!-- Generate Reports Card -->
        <div class="card-glass rounded-lg p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-center mb-4">
                <div class="flex-shrink-0 w-12 h-12 bg-primary rounded-full flex items-center justify-center text-white font-bold text-lg mr-4">4</div>
                <h3 class="text-xl font-semibold text-gray-800">Generate Reports</h3>
            </div>
            <p class="text-gray-600 leading-relaxed">
                Access detailed reports to monitor borrowing trends and optimize resources. 
                Make data-driven decisions with comprehensive analytics.
            </p>
        </div>
    </div>

    <!-- Mission Card -->
    <div class="card-glass rounded-lg p-8 border-l-4 border-primary">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="w-8 h-8 text-primary" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-4">
                <h3 class="text-2xl font-bold text-gray-800 mb-3">Our Mission</h3>
                <p class="text-lg text-gray-600 leading-relaxed">
                    To streamline equipment management processes and provide administrators with the tools they need 
                    to efficiently track, monitor, and manage borrowed multimedia equipment.
                </p>
                <div class="mt-6">
                    <a href="/register.php" class="inline-flex items-center px-6 py-3 bg-primary hover:bg-primary-dark text-white font-semibold rounded-lg transition duration-200">
                        Get Started Today
                        <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once 'includes/public-footer.php'; ?>
