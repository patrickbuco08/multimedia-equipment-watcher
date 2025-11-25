<?php
/**
 * Migration Script: Add is_active column to users table
 * Run this once if you already have an existing database
 */

require_once 'config/database.php';

try {
    $pdo = getDBConnection();
    
    // Check if column already exists
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'is_active'");
    $columnExists = $stmt->fetch();
    
    if (!$columnExists) {
        // Add is_active column
        $pdo->exec("ALTER TABLE users ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 0 AFTER role");
        
        // Set all existing users to active (1)
        $pdo->exec("UPDATE users SET is_active = 1");
        
        echo "✓ Successfully added is_active column to users table<br>";
        echo "✓ All existing users have been set to active<br>";
        echo "<br><strong>Migration completed successfully!</strong><br>";
        echo "<a href='index.php'>Go to Login</a>";
    } else {
        echo "⚠ Column 'is_active' already exists in users table<br>";
        echo "No migration needed.<br>";
        echo "<a href='index.php'>Go to Login</a>";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
