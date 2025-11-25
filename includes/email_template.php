<?php
/**
 * Generate HTML email template
 */
function getEmailTemplate($title, $content, $footerText = '') {
    return '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . htmlspecialchars($title) . '</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 20px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%); padding: 30px 40px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 24px; font-weight: bold;">
                                &#127909; Multimedia Equipment Watcher
                            </h1>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px;">
                            ' . $content . '
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 20px 40px; text-align: center; border-top: 1px solid #e9ecef;">
                            <p style="margin: 0 0 10px 0; color: #6c757d; font-size: 14px;">
                                ' . ($footerText ?: 'This is an automated message from Multimedia Equipment Watcher System.') . '
                            </p>
                            <p style="margin: 0; color: #adb5bd; font-size: 12px;">
                                © ' . date('Y') . ' Multimedia Equipment Watcher. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';
}

/**
 * Generate info box for email
 */
function getInfoBox($items) {
    $html = '<table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f8f9fa; border-radius: 6px; margin: 20px 0; border: 1px solid #e9ecef;">
        <tr>
            <td style="padding: 20px;">';
    
    foreach ($items as $label => $value) {
        $html .= '<p style="margin: 0 0 12px 0; font-size: 14px; line-height: 1.6;">
            <strong style="color: #495057;">' . htmlspecialchars($label) . ':</strong>
            <span style="color: #6c757d;">' . htmlspecialchars($value) . '</span>
        </p>';
    }
    
    $html .= '</td>
        </tr>
    </table>';
    
    return $html;
}

/**
 * Generate alert box for email
 */
function getAlertBox($message, $type = 'info') {
    $colors = [
        'info' => ['bg' => '#cfe2ff', 'border' => '#9ec5fe', 'text' => '#084298'],
        'warning' => ['bg' => '#fff3cd', 'border' => '#ffecb5', 'text' => '#664d03'],
        'danger' => ['bg' => '#f8d7da', 'border' => '#f5c2c7', 'text' => '#842029'],
        'success' => ['bg' => '#d1e7dd', 'border' => '#badbcc', 'text' => '#0f5132']
    ];
    
    $color = $colors[$type] ?? $colors['info'];
    
    return '<div style="background-color: ' . $color['bg'] . '; border: 1px solid ' . $color['border'] . '; border-radius: 6px; padding: 15px; margin: 20px 0;">
        <p style="margin: 0; color: ' . $color['text'] . '; font-size: 14px; line-height: 1.6;">
            ' . $message . '
        </p>
    </div>';
}

/**
 * Generate button for email
 */
function getButton($text, $url, $color = '#667eea') {
    return '<table width="100%" cellpadding="0" cellspacing="0" style="margin: 20px 0;">
        <tr>
            <td align="center">
                <a href="' . htmlspecialchars($url) . '" style="display: inline-block; padding: 12px 30px; background-color: ' . $color . '; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 14px;">
                    ' . htmlspecialchars($text) . '
                </a>
            </td>
        </tr>
    </table>';
}
