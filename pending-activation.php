<?php
require_once 'config/database.php';

startSession();

// If not logged in, redirect to login
if (!isLoggedIn()) {
    header('Location: index.php');
    exit;
}

// Get user info
$pdo = getDBConnection();
$stmt = $pdo->prepare("SELECT is_active FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// If account is already active, redirect to dashboard
if ($user && $user['is_active'] == 1) {

    if(isAdmin()) {
        header('Location: dashboard.php');
        exit;
    }

    header('Location: staff-dashboard.php');
    exit;
}

$userName = $_SESSION['user_name'] ?? 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Pending Activation - Multimedia Equipment Watcher</title>
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
<body class="bg-gray-50" style="background-image: url('images/background.jpg'); background-size: cover; background-position: center; background-attachment: fixed;">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8">
            <div class="text-center">
                <!-- Pending Icon -->
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-yellow-100 mb-4">
                    <svg class="h-10 w-10 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                
                <h1 class="text-2xl font-bold text-gray-800 mb-2">Account Pending Activation</h1>
                <p class="text-gray-600 mb-6">Hello, <?php echo htmlspecialchars($userName); ?>!</p>
                
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                    <p class="text-sm text-gray-700 leading-relaxed">
                        Your account has been successfully created but is currently <strong>pending activation</strong>. 
                        An administrator will review and activate your account shortly.
                    </p>
                </div>
                
                <div class="text-left bg-gray-50 rounded-lg p-4 mb-6">
                    <h3 class="font-semibold text-gray-800 mb-2">What happens next?</h3>
                    <ul class="text-sm text-gray-600 space-y-2">
                        <li class="flex items-start">
                            <svg class="h-5 w-5 text-primary mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>An administrator will review your registration</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="h-5 w-5 text-primary mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>Once activated, you'll be able to access the system</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="h-5 w-5 text-primary mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>You can try logging in again after activation</span>
                        </li>
                    </ul>
                </div>
                
                <div class="space-y-3">
                    <a href="logout.php" class="block w-full bg-primary hover:bg-primary-dark text-white font-semibold py-3 px-4 rounded-lg transition duration-200">
                        Logout
                    </a>
                    <button onclick="location.reload()" class="block w-full bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-3 px-4 rounded-lg transition duration-200">
                        Check Activation Status
                    </button>
                </div>
                
                <p class="text-xs text-gray-500 mt-6">
                    If you have any questions, please contact the administrator.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
