# Environment Variables Setup Guide

This project uses environment variables to protect sensitive credentials like database passwords and SMTP credentials.

## 🔐 Security Benefits

- ✅ Credentials are NOT committed to Git
- ✅ Each developer/server can have different credentials
- ✅ Easy to change credentials without modifying code
- ✅ Follows security best practices

## 📝 Setup Instructions

### Step 1: Copy the Example File

Copy `.env.example` to create your local environment file:

```bash
cp .env.example .env.local
```

### Step 2: Edit Your Credentials

Open `.env.local` and update with your actual credentials:

```bash
# Database Configuration
DB_HOST=127.0.0.1
DB_PORT=3306
DB_USER=root
DB_PASS=your_mysql_password_here
DB_NAME=multimedia_equipment_watcher

# SMTP/Email Configuration
SMTP_HOST=sandbox.smtp.mailtrap.io
SMTP_PORT=2525
SMTP_USERNAME=your_mailtrap_username
SMTP_PASSWORD=your_mailtrap_password
SMTP_FROM_EMAIL=noreply@oct.edu.ph
SMTP_FROM_NAME=Multimedia Equipment Watcher

# Application Settings
APP_NAME=Multimedia Equipment Watcher
APP_ENV=development
```

### Step 3: Verify .gitignore

Make sure `.env` and `.env.local` are in your `.gitignore` file (they already are):

```
.env
.env.local
```

## 📁 File Structure

```
multimedia-equipment-watcher/
├── .env.example          # Template file (committed to Git)
├── .env.local            # Your local credentials (NOT in Git)
├── .gitignore            # Protects .env files
├── config/
│   ├── env.php           # Environment loader
│   ├── database.php      # Uses env variables
│   └── mail.php          # PHPMailer helper with env
```

## 🔧 How It Works

### 1. Environment Loader (`config/env.php`)

Automatically loads variables from `.env.local` or `.env` file and makes them available via the `env()` function.

### 2. Database Config (`config/database.php`)

```php
define('DB_HOST', env('DB_HOST', '127.0.0.1'));
define('DB_USER', env('DB_USER', 'root'));
```

### 3. Mail Helper (`config/mail.php`)

```php
function getMailer() {
    $mail = new PHPMailer();
    $mail->Host = env('SMTP_HOST', 'sandbox.smtp.mailtrap.io');
    $mail->Username = env('SMTP_USERNAME', '');
    // ...
}
```

## 🚀 Usage in Code

### Getting Environment Variables

```php
// Get with default value
$host = env('DB_HOST', '127.0.0.1');

// Get without default
$apiKey = env('API_KEY');
```

### Using Mail Helper

```php
require_once 'config/mail.php';

$mail = getMailer();
$mail->addAddress('user@example.com');
$mail->Subject = 'Test Email';
$mail->Body = 'Hello!';
$mail->send();
```

## 🌍 Different Environments

### Development (Local)

Use `.env.local` with Mailtrap credentials:

```bash
APP_ENV=development
SMTP_HOST=sandbox.smtp.mailtrap.io
SMTP_USERNAME=your_mailtrap_username
```

### Production (Live Server)

Use `.env` with real SMTP credentials:

```bash
APP_ENV=production
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=your_email@gmail.com
SMTP_PASSWORD=your_app_password
```

## ⚠️ Important Security Notes

### DO NOT:
- ❌ Commit `.env` or `.env.local` to Git
- ❌ Share your credentials in chat/email
- ❌ Use production credentials in development
- ❌ Hardcode credentials in PHP files

### DO:
- ✅ Keep `.env.example` updated (without real credentials)
- ✅ Use different credentials for dev/prod
- ✅ Rotate credentials regularly
- ✅ Use strong passwords

## 🔄 Updating Credentials

If you need to change credentials:

1. Update `.env.local` file
2. No code changes needed!
3. Restart your web server (if using PHP-FPM)

## 🐛 Troubleshooting

### "Connection failed" Error

**Check:**
1. Is `.env.local` file present?
2. Are credentials correct in `.env.local`?
3. Is MySQL running?

### Emails Not Sending

**Check:**
1. SMTP credentials in `.env.local`
2. Test with Mailtrap first
3. Check `email_logs` table for errors

### "env() function not found"

**Solution:**
Make sure `config/env.php` is loaded:
```php
require_once __DIR__ . '/config/env.php';
```

## 📚 Additional Resources

- [Mailtrap Setup](https://mailtrap.io/)
- [PHP Environment Variables](https://www.php.net/manual/en/function.getenv.php)
- [Security Best Practices](https://owasp.org/www-project-top-ten/)

## 🎓 For Team Members

When cloning this project:

1. Clone the repository
2. Copy `.env.example` to `.env.local`
3. Ask team lead for development credentials
4. Update `.env.local` with provided credentials
5. Never commit your `.env.local` file!

---

**Remember: Keep your credentials secret, keep them safe! 🔐**
