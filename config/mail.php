<?php
/**
 * Email/SMTP Configuration Helper
 * Provides configured PHPMailer instance with credentials from environment
 */

require_once __DIR__ . '/env.php';

use PHPMailer\PHPMailer\PHPMailer;

/**
 * Get a configured PHPMailer instance
 * @return PHPMailer
 */
function getMailer() {
    $mail = new PHPMailer();
    
    // SMTP Configuration from environment
    $mail->isSMTP();
    $mail->Host = env('SMTP_HOST', 'sandbox.smtp.mailtrap.io');
    $mail->SMTPAuth = true;
    $mail->Port = env('SMTP_PORT', 2525);
    $mail->Username = env('SMTP_USERNAME', '');
    $mail->Password = env('SMTP_PASSWORD', '');
    
    // From address
    $mail->setFrom(
        env('SMTP_FROM_EMAIL', 'noreply@oct.edu.ph'),
        env('SMTP_FROM_NAME', 'Multimedia Equipment Watcher')
    );
    
    // Enable HTML
    $mail->isHTML(true);
    
    return $mail;
}
