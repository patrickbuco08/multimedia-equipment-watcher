<?php
/**
 * Quick Password Update Script
 * Run this once to fix the password hashes in the database
 * Access: http://localhost:8000/update_passwords.php
 */

require_once 'config/database.php';

try {
    $pdo = getDBConnection();
    
    // Generate correct hash for 'admin123'
    $correctHash = password_hash('admin123', PASSWORD_DEFAULT);
    
    // Update all users with the correct password
    $stmt = $pdo->prepare("UPDATE users SET password = ?");
    $stmt->execute([$correctHash]);
    
    $count = $stmt->rowCount();
    
    echo "<!DOCTYPE html>
<html>
<head>
    <title>Password Update</title>
    <style>
        body { font-family: Arial; padding: 50px; background: #f8f9fa; }
        .box { background: white; padding: 30px; border-radius: 10px; max-width: 600px; margin: 0 auto; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: #155724; background: #d4edda; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .btn { display: inline-block; padding: 10px 20px; background: #2ecc71; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class='box'>
        <h2>✓ Password Update Successful</h2>
        <div class='success'>
            Updated $count user account(s) with correct password hash.
        </div>
        <p><strong>You can now login with:</strong></p>
        <ul>
            <li>Email: admin@example.com</li>
            <li>Password: admin123</li>
        </ul>
        <a href='index.php' class='btn'>Go to Login Page</a>
        <p style='margin-top: 30px; color: #7f8c8d; font-size: 14px;'>
            <strong>Note:</strong> You can delete this file (update_passwords.php) after successful login.
        </p>
    </div>
</body>
</html>";
    
} catch (Exception $e) {
    echo "<!DOCTYPE html>
<html>
<head>
    <title>Password Update Error</title>
    <style>
        body { font-family: Arial; padding: 50px; background: #f8f9fa; }
        .box { background: white; padding: 30px; border-radius: 10px; max-width: 600px; margin: 0 auto; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .error { color: #721c24; background: #f8d7da; padding: 15px; border-radius: 5px; }
    </style>
</head>
<body>
    <div class='box'>
        <h2>✗ Update Failed</h2>
        <div class='error'>
            Error: " . htmlspecialchars($e->getMessage()) . "
        </div>
        <p>Make sure the database is properly set up and accessible.</p>
    </div>
</body>
</html>";
}
