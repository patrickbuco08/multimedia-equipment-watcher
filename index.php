<?php
require_once 'config/database.php';

startSession();

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$pageTitle = 'Welcome - Multimedia Equipment Watcher';
require_once 'includes/auth-header.php';
?>

<div class="max-w-4xl w-full bg-white rounded-lg shadow-lg p-12">
    <!-- Hero Section -->
    <div class="text-center mb-12">
        <div class="mb-6">
            <svg class="mx-auto h-20 w-20 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
            </svg>
        </div>
        <h1 class="text-4xl font-bold text-gray-900 mb-4">Multimedia Equipment Watcher</h1>
        <p class="text-xl text-gray-600 mb-8">Professional Equipment Management System</p>
        <p class="text-base text-gray-500 max-w-2xl mx-auto leading-relaxed">
            A comprehensive solution for managing multimedia equipment borrowing, tracking, and reporting. 
            Streamline your equipment inventory, monitor borrowing transactions, and ensure timely returns.
        </p>
    </div>

    <!-- Features Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
        <div class="text-center p-6 bg-gray-50 rounded-lg">
            <div class="mb-4">
                <svg class="mx-auto h-12 w-12 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Equipment Inventory</h3>
            <p class="text-sm text-gray-600">Manage and track all your multimedia equipment in one place</p>
        </div>

        <div class="text-center p-6 bg-gray-50 rounded-lg">
            <div class="mb-4">
                <svg class="mx-auto h-12 w-12 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Borrowing System</h3>
            <p class="text-sm text-gray-600">Efficient borrowing workflows with due dates and notifications</p>
        </div>

        <div class="text-center p-6 bg-gray-50 rounded-lg">
            <div class="mb-4">
                <svg class="mx-auto h-12 w-12 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Reports & Tracking</h3>
            <p class="text-sm text-gray-600">Track damage, loss, and overdue items with automated alerts</p>
        </div>
    </div>

    <!-- CTA Button -->
    <div class="text-center">
        <button onclick="showAccountDialog()" class="bg-primary hover:bg-primary-dark text-white font-bold py-4 px-8 rounded-lg text-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
            Get Started
        </button>
        <p class="mt-4 text-sm text-gray-500">Join us in managing equipment efficiently</p>
    </div>
</div>

<!-- Custom Dialog Modal -->
<div id="accountDialog" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-8 border w-96 shadow-2xl rounded-xl bg-white">
        <div class="text-center">
            <div class="mb-6">
                <svg class="mx-auto h-16 w-16 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-4">Do you have an account?</h3>
            <p class="text-sm text-gray-500 mb-8">Choose an option to continue</p>
            <div class="space-y-3">
                <a href="login.php" class="block w-full bg-primary hover:bg-primary-dark text-white font-semibold py-3 px-6 rounded-lg transition duration-200">
                    Yes, I have an account
                </a>
                <a href="register.php" class="block w-full bg-gray-600 hover:bg-gray-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-200">
                    No, create new account
                </a>
                <button onclick="closeAccountDialog()" class="block w-full bg-white hover:bg-gray-50 text-gray-700 font-medium py-3 px-6 rounded-lg border border-gray-300 transition duration-200">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function showAccountDialog() {
    document.getElementById('accountDialog').classList.remove('hidden');
}

function closeAccountDialog() {
    document.getElementById('accountDialog').classList.add('hidden');
}

// Close dialog when clicking outside
document.getElementById('accountDialog').addEventListener('click', function(event) {
    if (event.target === this) {
        closeAccountDialog();
    }
});
</script>

<?php require_once 'includes/auth-footer.php'; ?>
