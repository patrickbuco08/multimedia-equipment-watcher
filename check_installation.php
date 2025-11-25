<?php
/**
 * Installation Verification Script
 * Run this file to check if your system is properly configured
 * Access: http://localhost/multimedia-equipment-watcher/check_installation.php
 */

$checks = [];
$allPassed = true;

// Check PHP Version
$phpVersion = phpversion();
$phpCheck = version_compare($phpVersion, '7.4.0', '>=');
$checks[] = [
    'name' => 'PHP Version',
    'status' => $phpCheck,
    'message' => $phpCheck ? "✓ PHP $phpVersion (OK)" : "✗ PHP $phpVersion (Requires 7.4+)"
];
if (!$phpCheck) $allPassed = false;

// Check PDO MySQL Extension
$pdoCheck = extension_loaded('pdo_mysql');
$checks[] = [
    'name' => 'PDO MySQL Extension',
    'status' => $pdoCheck,
    'message' => $pdoCheck ? '✓ PDO MySQL extension loaded' : '✗ PDO MySQL extension not found'
];
if (!$pdoCheck) $allPassed = false;

// Check config file
$configCheck = file_exists(__DIR__ . '/config/database.php');
$checks[] = [
    'name' => 'Configuration File',
    'status' => $configCheck,
    'message' => $configCheck ? '✓ config/database.php exists' : '✗ config/database.php not found'
];
if (!$configCheck) $allPassed = false;

// Check database connection
$dbCheck = false;
$dbMessage = '';
if ($configCheck) {
    try {
        require_once 'config/database.php';
        $pdo = getDBConnection();
        $dbCheck = true;
        $dbMessage = '✓ Database connection successful';
    } catch (Exception $e) {
        $dbMessage = '✗ Database connection failed: ' . $e->getMessage();
        $allPassed = false;
    }
}
$checks[] = [
    'name' => 'Database Connection',
    'status' => $dbCheck,
    'message' => $dbMessage
];

// Check tables
$tablesCheck = false;
$tablesMessage = '';
if ($dbCheck) {
    try {
        $tables = ['users', 'equipment', 'borrowing_transactions', 'email_logs'];
        $foundTables = [];
        
        foreach ($tables as $table) {
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() > 0) {
                $foundTables[] = $table;
            }
        }
        
        if (count($foundTables) === count($tables)) {
            $tablesCheck = true;
            $tablesMessage = '✓ All required tables exist (' . implode(', ', $foundTables) . ')';
        } else {
            $tablesMessage = '✗ Missing tables. Found: ' . implode(', ', $foundTables);
            $allPassed = false;
        }
    } catch (Exception $e) {
        $tablesMessage = '✗ Could not check tables: ' . $e->getMessage();
        $allPassed = false;
    }
}
$checks[] = [
    'name' => 'Database Tables',
    'status' => $tablesCheck,
    'message' => $tablesMessage
];

// Check sample data
$dataCheck = false;
$dataMessage = '';
if ($tablesCheck) {
    try {
        $userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $equipmentCount = $pdo->query("SELECT COUNT(*) FROM equipment")->fetchColumn();
        
        if ($userCount > 0 && $equipmentCount > 0) {
            $dataCheck = true;
            $dataMessage = "✓ Sample data found ($userCount users, $equipmentCount equipment)";
        } else {
            $dataMessage = "⚠ No sample data found (users: $userCount, equipment: $equipmentCount)";
        }
    } catch (Exception $e) {
        $dataMessage = '✗ Could not check sample data: ' . $e->getMessage();
    }
}
$checks[] = [
    'name' => 'Sample Data',
    'status' => $dataCheck,
    'message' => $dataMessage
];

// Check directories
$directories = [
    'assets/css' => 'CSS Directory',
    'assets/js' => 'JavaScript Directory',
    'equipment' => 'Equipment Module',
    'borrowing' => 'Borrowing Module',
    'staff' => 'Staff Module',
    'logs' => 'Logs Module',
    'email' => 'Email Module',
    'overdue' => 'Overdue Module'
];

foreach ($directories as $dir => $name) {
    $dirCheck = is_dir(__DIR__ . '/' . $dir);
    $checks[] = [
        'name' => $name,
        'status' => $dirCheck,
        'message' => $dirCheck ? "✓ $dir exists" : "✗ $dir not found"
    ];
    if (!$dirCheck) $allPassed = false;
}

// Check key files
$files = [
    'index.php' => 'Login Page',
    'dashboard.php' => 'Dashboard Page',
    'assets/css/style.css' => 'Main Stylesheet',
    'setup/install.sql' => 'Database Schema'
];

foreach ($files as $file => $name) {
    $fileCheck = file_exists(__DIR__ . '/' . $file);
    $checks[] = [
        'name' => $name,
        'status' => $fileCheck,
        'message' => $fileCheck ? "✓ $file exists" : "✗ $file not found"
    ];
    if (!$fileCheck) $allPassed = false;
}

// Check writable permissions (if needed)
$writableCheck = is_writable(__DIR__);
$checks[] = [
    'name' => 'Directory Permissions',
    'status' => $writableCheck,
    'message' => $writableCheck ? '✓ Directory is writable' : '⚠ Directory is not writable (may affect some features)'
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation Check - Multimedia Equipment Watcher</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
            padding: 30px;
            min-height: 100vh;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .header {
            background: #4b4b4b;
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin-bottom: 10px;
            font-size: 28px;
        }
        .content {
            padding: 30px;
        }
        .status-badge {
            display: inline-block;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 30px;
            width: 100%;
            font-size: 18px;
            font-weight: bold;
        }
        .status-badge.success {
            background: #d4edda;
            color: #155724;
            border: 2px solid #c3e6cb;
        }
        .status-badge.error {
            background: #f8d7da;
            color: #721c24;
            border: 2px solid #f5c6cb;
        }
        .check-item {
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            border-left: 4px solid #dee2e6;
            background: #f8f9fa;
        }
        .check-item.pass {
            border-left-color: #2ecc71;
            background: #d4edda;
        }
        .check-item.fail {
            border-left-color: #e74c3c;
            background: #f8d7da;
        }
        .check-item strong {
            display: block;
            margin-bottom: 5px;
            color: #4b4b4b;
        }
        .check-item span {
            color: #7f8c8d;
            font-size: 14px;
        }
        .actions {
            margin-top: 30px;
            padding-top: 30px;
            border-top: 2px solid #dee2e6;
            text-align: center;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #2ecc71;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 5px;
            font-weight: bold;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #27ae60;
        }
        .btn-secondary {
            background: #4b4b4b;
        }
        .btn-secondary:hover {
            background: #3a3a3a;
        }
        .instructions {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 5px;
            padding: 20px;
            margin-top: 20px;
        }
        .instructions h3 {
            color: #856404;
            margin-bottom: 15px;
        }
        .instructions ol {
            margin-left: 20px;
            line-height: 1.8;
            color: #856404;
        }
        .instructions code {
            background: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📹 Installation Check</h1>
            <p>Multimedia Equipment Watcher - System Verification</p>
        </div>
        
        <div class="content">
            <div class="status-badge <?php echo $allPassed ? 'success' : 'error'; ?>">
                <?php if ($allPassed): ?>
                    ✓ All checks passed! Your system is ready to use.
                <?php else: ?>
                    ✗ Some checks failed. Please review the issues below.
                <?php endif; ?>
            </div>
            
            <h2 style="color: #4b4b4b; margin-bottom: 20px;">System Checks</h2>
            
            <?php foreach ($checks as $check): ?>
                <div class="check-item <?php echo $check['status'] ? 'pass' : 'fail'; ?>">
                    <strong><?php echo htmlspecialchars($check['name']); ?></strong>
                    <span><?php echo htmlspecialchars($check['message']); ?></span>
                </div>
            <?php endforeach; ?>
            
            <?php if (!$allPassed): ?>
                <div class="instructions">
                    <h3>⚠ Setup Instructions</h3>
                    <ol>
                        <li>Make sure XAMPP/MAMP is installed and running</li>
                        <li>Start Apache and MySQL services</li>
                        <li>Import the database:
                            <ul style="margin-top: 10px;">
                                <li>Open phpMyAdmin: <code>http://localhost/phpmyadmin</code></li>
                                <li>Click "Import" tab</li>
                                <li>Select file: <code>setup/install.sql</code></li>
                                <li>Click "Go"</li>
                            </ul>
                        </li>
                        <li>Verify database credentials in <code>config/database.php</code></li>
                        <li>Refresh this page to re-check</li>
                    </ol>
                    <p style="margin-top: 15px;">
                        <strong>Need more help?</strong> Check <code>SETUP_GUIDE.md</code> for detailed instructions.
                    </p>
                </div>
            <?php endif; ?>
            
            <div class="actions">
                <?php if ($allPassed): ?>
                    <a href="index.php" class="btn">Go to Login Page →</a>
                    <a href="dashboard.php" class="btn btn-secondary">Go to Dashboard →</a>
                <?php else: ?>
                    <a href="check_installation.php" class="btn">Refresh Check</a>
                <?php endif; ?>
                <a href="README.md" class="btn btn-secondary">View Documentation</a>
            </div>
            
            <?php if ($allPassed): ?>
                <div style="margin-top: 30px; padding: 20px; background: #d1ecf1; border-radius: 5px; text-align: center;">
                    <p style="color: #0c5460; margin-bottom: 10px;">
                        <strong>Default Login Credentials:</strong>
                    </p>
                    <p style="color: #0c5460;">
                        Email: <code>admin@example.com</code><br>
                        Password: <code>admin123</code>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
