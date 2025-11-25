<?php
/**
 * Send Overdue Equipment Email Notifications
 * 
 * This script can be run manually or via cron job to send email notifications
 * to borrowers with overdue equipment.
 */

require_once '../config/database.php';
require_once '../vendor/autoload.php';
require_once '../includes/email_template.php';
require_once '../config/mail.php';

use PHPMailer\PHPMailer\PHPMailer;

$pdo = getDBConnection();

// Get overdue transactions with user info
$stmt = $pdo->query("
    SELECT bt.*, 
           e.name as equipment_name,
           u.name as borrower_name,
           u.email as borrower_email,
           DATEDIFF(CURDATE(), bt.due_date) as days_overdue
    FROM borrowing_transactions bt
    JOIN equipment e ON bt.equipment_id = e.id
    JOIN users u ON bt.user_id = u.id
    WHERE bt.status IN ('borrowed', 'partially_returned') AND bt.due_date < CURDATE()
    ORDER BY bt.due_date ASC
");
$overdueTransactions = $stmt->fetchAll();

$emailsSent = 0;
$emailsFailed = 0;
$errors = [];

foreach ($overdueTransactions as $transaction) {
    try {
        // Build email content
        $content = '<h2 style="margin: 0 0 20px 0; color: #212529; font-size: 20px;">&#9888;&#65039; Overdue Equipment Reminder</h2>
        <p style="margin: 0 0 20px 0; color: #495057; font-size: 14px; line-height: 1.6;">
            Dear ' . htmlspecialchars($transaction['borrower_name']) . ',
        </p>
        <p style="margin: 0 0 20px 0; color: #495057; font-size: 14px; line-height: 1.6;">
            This is an important reminder that the equipment you borrowed is now <strong>overdue</strong>.
        </p>';
        
        $content .= getAlertBox('&#128680; <strong>OVERDUE: ' . $transaction['days_overdue'] . ' day(s)</strong> - Please return this equipment immediately.', 'danger');
        
        $content .= getInfoBox([
            'Equipment' => $transaction['equipment_name'],
            'Quantity' => $transaction['quantity'],
            'Date Borrowed' => date('F d, Y', strtotime($transaction['date_borrowed'])),
            'Due Date' => date('F d, Y', strtotime($transaction['due_date'])),
            'Days Overdue' => $transaction['days_overdue'] . ' day(s)'
        ]);
        
        $content .= '<p style="margin: 20px 0 0 0; color: #495057; font-size: 14px; line-height: 1.6;">
            Please return the equipment as soon as possible to avoid further penalties. If you have any questions or concerns, please contact the administrator immediately.
        </p>
        <p style="margin: 10px 0 0 0; color: #495057; font-size: 14px; line-height: 1.6;">
            Thank you for your cooperation.
        </p>';
        
        $htmlBody = getEmailTemplate('Overdue Equipment Reminder', $content);
        
        // Send email
        $phpmailer = getMailer();
        $phpmailer->addAddress($transaction['borrower_email']);
        
        $phpmailer->Subject = 'Overdue Equipment Reminder - ' . $transaction['equipment_name'];
        $phpmailer->Body = $htmlBody;
        $phpmailer->AltBody = "OVERDUE REMINDER\n\nDear " . $transaction['borrower_name'] . ",\n\nEquipment: " . $transaction['equipment_name'] . "\nDays Overdue: " . $transaction['days_overdue'] . " day(s)\n\nPlease return immediately.";
        
        if ($phpmailer->send()) {
            // Log successful email
            $stmt = $pdo->prepare("INSERT INTO email_logs (transaction_id, email_to, subject, message, status) VALUES (?, ?, ?, ?, 'success')");
            $stmt->execute([$transaction['id'], $transaction['borrower_email'], $phpmailer->Subject, $phpmailer->AltBody]);
            $emailsSent++;
        } else {
            throw new Exception($phpmailer->ErrorInfo);
        }
    } catch (Exception $e) {
        // Log failed email
        try {
            $stmt = $pdo->prepare("INSERT INTO email_logs (transaction_id, email_to, subject, message, status) VALUES (?, ?, ?, ?, 'failed')");
            $stmt->execute([$transaction['id'], $transaction['borrower_email'], 'Overdue Equipment Reminder', 'Failed: ' . $e->getMessage()]);
            $emailsFailed++;
            $errors[] = "Failed to send email to " . $transaction['borrower_name'] . ": " . $e->getMessage();
        } catch (Exception $logError) {
            $errors[] = "Failed to log error for " . $transaction['borrower_email'];
        }
    }
}

// Output results
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Notification Results</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-3xl font-bold text-gray-800 mb-6">📧 Email Notification Results</h2>
            
            <?php if (count($overdueTransactions) === 0): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <strong>✓ No overdue items found!</strong><br>
                    All borrowed equipment is within the due date.
                </div>
            <?php else: ?>
                <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-4">
                    <strong>📊 Email Notification Summary:</strong><br>
                    <ul class="mt-2 ml-4 list-disc">
                        <li>Total overdue items: <?php echo count($overdueTransactions); ?></li>
                        <li>Emails sent successfully: <?php echo $emailsSent; ?></li>
                        <li>Emails failed: <?php echo $emailsFailed; ?></li>
                    </ul>
                </div>
                
                <?php if ($emailsSent > 0): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        ✅ Successfully sent <?php echo $emailsSent; ?> email notification(s).
                    </div>
                <?php endif; ?>
                
                <?php if ($emailsFailed > 0): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        ❌ Failed to send <?php echo $emailsFailed; ?> email notification(s).
                    </div>
                <?php endif; ?>
                
                <?php if (count($errors) > 0): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <strong>Errors:</strong>
                        <ul class="mt-2 ml-4 list-disc">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <div class="mt-6 pt-6 border-t border-gray-200">
                <a href="/overdue/list.php" class="inline-block px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg transition mr-3">← Back to Overdue List</a>
                <a href="/dashboard.php" class="inline-block px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg transition">Go to Dashboard</a>
            </div>
        </div>
    </div>
</body>
</html>
