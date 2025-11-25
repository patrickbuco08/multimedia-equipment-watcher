<?php
/**
 * Database Backup Utility
 * 
 * This script creates a backup of your database
 * Access: http://localhost/multimedia-equipment-watcher/backup_database.php
 * 
 * For security, this should be:
 * 1. Protected with authentication
 * 2. Removed from production server
 * 3. Run via command line instead
 */

require_once 'config/database.php';

// Security: Require login
requireLogin();
require_once '../config/database.php';
requireAdmin(); // Only admins can backup

$backupDir = __DIR__ . '/backups/';

// Create backups directory if it doesn't exist
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

$filename = 'backup_' . date('Y-m-d_His') . '.sql';
$filepath = $backupDir . $filename;

try {
    $pdo = getDBConnection();
    
    // Get all tables
    $tables = [];
    $result = $pdo->query("SHOW TABLES");
    while ($row = $result->fetch(PDO::FETCH_NUM)) {
        $tables[] = $row[0];
    }
    
    // Start SQL dump
    $sqlDump = "-- Multimedia Equipment Watcher Database Backup\n";
    $sqlDump .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
    $sqlDump .= "-- Database: " . DB_NAME . "\n\n";
    $sqlDump .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
    
    foreach ($tables as $table) {
        // Drop table statement
        $sqlDump .= "DROP TABLE IF EXISTS `$table`;\n";
        
        // Create table statement
        $createTable = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM);
        $sqlDump .= $createTable[1] . ";\n\n";
        
        // Insert data
        $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($rows) > 0) {
            foreach ($rows as $row) {
                $values = array_map(function($value) use ($pdo) {
                    return $value === null ? 'NULL' : $pdo->quote($value);
                }, array_values($row));
                
                $sqlDump .= "INSERT INTO `$table` VALUES (" . implode(', ', $values) . ");\n";
            }
            $sqlDump .= "\n";
        }
    }
    
    $sqlDump .= "SET FOREIGN_KEY_CHECKS=1;\n";
    
    // Write to file
    file_put_contents($filepath, $sqlDump);
    
    $success = true;
    $message = "Backup created successfully!";
    $filesize = filesize($filepath);
    
} catch (Exception $e) {
    $success = false;
    $message = "Backup failed: " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Backup</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container" style="margin-top: 50px;">
        <div class="content-box" style="max-width: 800px; margin: 0 auto;">
            <h2 style="color: var(--gray); margin-bottom: 20px;">Database Backup</h2>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <strong>✓ <?php echo $message; ?></strong><br>
                    <p style="margin-top: 10px;">
                        File: <code><?php echo htmlspecialchars($filename); ?></code><br>
                        Size: <?php echo number_format($filesize / 1024, 2); ?> KB<br>
                        Location: <code>backups/<?php echo htmlspecialchars($filename); ?></code>
                    </p>
                </div>
                
                <div style="margin-top: 20px;">
                    <a href="backups/<?php echo $filename; ?>" download class="btn btn-primary">Download Backup File</a>
                    <a href="backup_database.php" class="btn btn-secondary">Create New Backup</a>
                    <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                </div>
                
                <div style="margin-top: 30px; padding: 20px; background: #d1ecf1; border-radius: 5px;">
                    <h3 style="color: #0c5460; margin-bottom: 10px;">ℹ Backup Information</h3>
                    <p style="color: #0c5460; line-height: 1.6;">
                        This backup includes all tables and data from your database. 
                        Store this file securely and keep multiple backup copies in different locations.
                    </p>
                </div>
            <?php else: ?>
                <div class="alert alert-danger">
                    <strong>✗ <?php echo htmlspecialchars($message); ?></strong>
                </div>
                
                <div style="margin-top: 20px;">
                    <a href="backup_database.php" class="btn btn-primary">Try Again</a>
                    <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                </div>
            <?php endif; ?>
            
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #dee2e6;">
                <h3 style="color: var(--gray); margin-bottom: 15px;">Backup Recommendations:</h3>
                <ul style="line-height: 1.8; color: #4b4b4b;">
                    <li>Create backups regularly (daily or weekly)</li>
                    <li>Store backups in multiple secure locations</li>
                    <li>Test backup restoration periodically</li>
                    <li>Keep at least 3-5 recent backups</li>
                    <li>Protect backup files from unauthorized access</li>
                </ul>
            </div>
            
            <div style="margin-top: 30px; padding: 15px; background: #fff3cd; border-radius: 5px;">
                <strong style="color: #856404;">⚠ Security Note:</strong>
                <p style="color: #856404; margin-top: 10px;">
                    For production environments, remove or restrict access to this backup script. 
                    Use command-line tools or automated backup services instead.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
