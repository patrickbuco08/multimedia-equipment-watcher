<?php
$pageTitle = 'Overdue Items - Multimedia Equipment Watcher';
require_once '../includes/header.php';
requireAdmin();

use PHPMailer\PHPMailer\PHPMailer;

$pdo = getDBConnection();
$success = '';
$error = '';

// Handle send notification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['notify_id'])) {
    $transaction_id = $_POST['notify_id'];
    
    try {
        // Get transaction details
        $stmt = $pdo->prepare("
            SELECT bt.*, e.name as equipment_name, u.name as borrower_name, u.email as borrower_email
            FROM borrowing_transactions bt
            JOIN equipment e ON bt.equipment_id = e.id
            JOIN users u ON bt.user_id = u.id
            WHERE bt.id = ?
        ");
        $stmt->execute([$transaction_id]);
        $transaction = $stmt->fetch();
        
        if ($transaction) {
            require_once '../vendor/autoload.php';
            require_once '../includes/email_template.php';
            
            $days_overdue = (new DateTime())->diff(new DateTime($transaction['due_date']))->days;
            
            // Build email content
            $content = '<h2 style="margin: 0 0 20px 0; color: #212529; font-size: 20px;">&#9888;&#65039; Overdue Equipment Reminder</h2>
            <p style="margin: 0 0 20px 0; color: #495057; font-size: 14px; line-height: 1.6;">
                Dear ' . htmlspecialchars($transaction['borrower_name']) . ',
            </p>
            <p style="margin: 0 0 20px 0; color: #495057; font-size: 14px; line-height: 1.6;">
                This is an important reminder that the equipment you borrowed is now <strong>overdue</strong>.
            </p>';
            
            $content .= getAlertBox('&#128680; <strong>OVERDUE: ' . $days_overdue . ' day(s)</strong> - Please return this equipment immediately.', 'danger');
            
            $content .= getInfoBox([
                'Equipment' => $transaction['equipment_name'],
                'Quantity' => $transaction['quantity'],
                'Date Borrowed' => date('F d, Y', strtotime($transaction['date_borrowed'])),
                'Due Date' => date('F d, Y', strtotime($transaction['due_date'])),
                'Days Overdue' => $days_overdue . ' day(s)'
            ]);
            
            $content .= '<p style="margin: 20px 0 0 0; color: #495057; font-size: 14px; line-height: 1.6;">
                Please return the equipment as soon as possible to avoid further penalties. If you have any questions or concerns, please contact the administrator immediately.
            </p>
            <p style="margin: 10px 0 0 0; color: #495057; font-size: 14px; line-height: 1.6;">
                Thank you for your cooperation.
            </p>';
            
            $htmlBody = getEmailTemplate('Overdue Equipment Reminder', $content);
            
            $phpmailer = new PHPMailer();
            $phpmailer->isSMTP();
            $phpmailer->Host = 'sandbox.smtp.mailtrap.io';
            $phpmailer->SMTPAuth = true;
            $phpmailer->Port = 2525;
            $phpmailer->Username = '200d7dede3dd55';
            $phpmailer->Password = '6283e0434900b9';
            
            $phpmailer->setFrom('noreply@oct.edu.ph', 'Multimedia Equipment Watcher');
            $phpmailer->addAddress($transaction['borrower_email']);
            $phpmailer->isHTML(true);
            
            $phpmailer->Subject = 'Overdue Equipment Reminder - ' . $transaction['equipment_name'];
            $phpmailer->Body = $htmlBody;
            $phpmailer->AltBody = "OVERDUE REMINDER\n\nDear " . $transaction['borrower_name'] . ",\n\nEquipment: " . $transaction['equipment_name'] . "\nDays Overdue: " . $days_overdue . " day(s)\n\nPlease return immediately.";
            
            $phpmailer->send();
            
            // Log email
            $stmt = $pdo->prepare("INSERT INTO email_logs (transaction_id, email_to, subject, message, status) VALUES (?, ?, ?, ?, 'success')");
            $stmt->execute([$transaction_id, $transaction['borrower_email'], $phpmailer->Subject, $phpmailer->AltBody]);
            
            $success = 'Overdue notification sent to ' . $transaction['borrower_name'] . ' successfully!';
        }
    } catch (Exception $e) {
        $error = 'Error sending notification: ' . $e->getMessage();
    }
}

// Get overdue transactions
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
?>

<!-- Page Header -->
<div class="flex justify-between items-center mb-6">
    <h2 class="text-3xl font-bold text-gray-800">Overdue Items</h2>
</div>

<!-- Content Box -->
<div class="bg-white rounded-lg shadow-sm p-6">
    <?php if ($success): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if (count($overdueTransactions) > 0): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <strong>⚠ <?php echo count($overdueTransactions); ?> overdue item(s) found!</strong>
            Please contact the borrowers to return the equipment.
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Equipment</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Borrower</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Borrowed</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Days Overdue</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($overdueTransactions as $transaction): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($transaction['equipment_name']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo htmlspecialchars($transaction['borrower_name']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <a href="mailto:<?php echo htmlspecialchars($transaction['borrower_email']); ?>" class="text-blue-600 hover:text-blue-800">
                                    <?php echo htmlspecialchars($transaction['borrower_email']); ?>
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo date('M d, Y', strtotime($transaction['date_borrowed'])); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo date('M d, Y', strtotime($transaction['due_date'])); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    <?php echo $transaction['days_overdue']; ?> day(s)
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $transaction['status'] === 'partially_returned' ? 'bg-orange-100 text-orange-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $transaction['status'])); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <form method="POST" class="inline" onsubmit="return confirm('Send overdue notification to <?php echo htmlspecialchars($transaction['borrower_name']); ?>?');">
                                    <input type="hidden" name="notify_id" value="<?php echo $transaction['id']; ?>">
                                    <button type="submit" class="text-blue-600 hover:text-blue-800 font-medium mr-3">Notify</button>
                                </form>
                                <a href="/borrowing/return.php?id=<?php echo $transaction['id']; ?>" class="text-primary hover:text-primary-dark font-medium">Return</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h3 class="mt-2 text-lg font-medium text-gray-900">✓ No Overdue Items</h3>
            <p class="mt-1 text-sm text-gray-500">All borrowed equipment is within the due date or has been returned.</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
