# 🚀 Quick Setup Guide - Multimedia Equipment Watcher

This guide will help you set up the Multimedia Equipment Watcher system in just a few minutes.

## ⚡ Quick Start (5 Minutes)

### Step 1: Install Prerequisites

Make sure you have:
- ✅ XAMPP, MAMP, or WAMP installed
- ✅ PHP 7.4 or higher
- ✅ MySQL 5.7 or higher

**Download XAMPP (Recommended):**
- Windows: https://www.apachefriends.org/download.html
- Mac: https://www.apachefriends.org/download.html
- Linux: https://www.apachefriends.org/download.html

### Step 2: Start Your Server

#### Using XAMPP:
1. Open XAMPP Control Panel
2. Click **Start** for Apache
3. Click **Start** for MySQL
4. Verify both are running (green status)

### Step 3: Create Database

**Option A: Using phpMyAdmin (Easiest)**
1. Open browser and go to: `http://localhost/phpmyadmin`
2. Click **Import** tab
3. Click **Choose File** and select: `setup/install.sql`
4. Click **Go** button at the bottom
5. Wait for success message

**Option B: Using Command Line**
```bash
# Windows (in XAMPP directory)
cd C:\xampp\mysql\bin
mysql -u root -p < C:\path\to\multimedia-equipment-watcher\setup\install.sql

# Mac/Linux
mysql -u root -p
SOURCE /Users/johnpatrickbuco/Desktop/projects/multimedia-equipment-watcher/setup/install.sql;
```

### Step 4: Copy Project Files

Copy the entire `multimedia-equipment-watcher` folder to your web server directory:

**XAMPP:**
- Windows: `C:\xampp\htdocs\`
- Mac: `/Applications/XAMPP/htdocs/`
- Linux: `/opt/lampp/htdocs/`

**MAMP:**
- Mac: `/Applications/MAMP/htdocs/`

**WAMP:**
- Windows: `C:\wamp64\www\`

** Run PHP's Built-in server
 - php -S localhost:8000

### Step 5: Access the System

Open your web browser and go to:
```
http://localhost/multimedia-equipment-watcher/
```

### Step 6: Login

Use the default admin account:
```
Email: admin@example.com
Password: admin123
```

**🎉 Congratulations! Your system is now ready to use!**

---

## 📋 Detailed Setup Instructions

### Database Configuration

If your MySQL setup is different from the default, update `config/database.php`:

```php
define('DB_HOST', '127.0.0.1');     // Your MySQL host
define('DB_PORT', '3306');           // Your MySQL port
define('DB_USER', 'root');           // Your MySQL username
define('DB_PASS', '');               // Your MySQL password (empty for XAMPP)
define('DB_NAME', 'multimedia_equipment_watcher');
```

### Verify Installation

After setup, verify everything works:

1. ✅ Can you login with admin@example.com?
2. ✅ Can you see the dashboard with statistics?
3. ✅ Can you navigate to Equipment page?
4. ✅ Can you see sample equipment data?

If any of these fail, check:
- MySQL is running
- Database was imported successfully
- Files are in the correct directory
- PHP is enabled in your web server

---

## 🔧 Troubleshooting

### Issue: "Connection failed" error

**Solution:**
1. Verify MySQL is running in XAMPP/MAMP
2. Check database credentials in `config/database.php`
3. Ensure database `multimedia_equipment_watcher` exists

### Issue: "404 Not Found" when accessing pages

**Solution:**
1. Verify project is in correct directory (htdocs/www)
2. Check the URL: `http://localhost/multimedia-equipment-watcher/`
3. Restart Apache in XAMPP

### Issue: "Blank white page"

**Solution:**
1. Enable error display in PHP:
   - Open XAMPP Control Panel
   - Click **Config** for Apache
   - Select `php.ini`
   - Find `display_errors` and set to `On`
   - Restart Apache
2. Check PHP error logs

### Issue: CSS/Styles not loading

**Solution:**
1. Verify `assets` folder exists in your project
2. Clear browser cache (Ctrl+F5)
3. Check browser console for errors

### Issue: Cannot import database

**Solution:**
1. Open `setup/install.sql` in a text editor
2. Copy all contents
3. In phpMyAdmin, go to SQL tab
4. Paste and click Go
5. If still fails, create database manually first:
   ```sql
   CREATE DATABASE multimedia_equipment_watcher;
   USE multimedia_equipment_watcher;
   ```
   Then run the import again

---

## 📧 Email Setup (Optional)

The email functionality is optional but recommended for production use.

### Using Mailtrap (Testing)

1. Go to https://mailtrap.io/ and sign up (free)
2. Create a new inbox
3. Copy your SMTP credentials
4. Edit `email/send_overdue_notifications.php`
5. Update these lines:
   ```php
   define('SMTP_USERNAME', 'your_username_here');
   define('SMTP_PASSWORD', 'your_password_here');
   ```

### Testing Email Notifications

1. Make sure you have overdue transactions in the database
2. Visit: `http://localhost/multimedia-equipment-watcher/email/send_overdue_notifications.php`
3. Check Mailtrap inbox for emails

---

## 🎓 Default Test Accounts

The system comes with pre-configured test accounts:

### Admin Account
```
Email: admin@example.com
Password: admin123
Access: Full system access including staff management and logs
```

### Staff Accounts
```
Email: john.staff@example.com
Password: admin123
Access: Equipment and borrowing management

Email: jane.staff@example.com
Password: admin123
Access: Equipment and borrowing management
```

### Sample Data Included

- ✅ 5 sample equipment items
- ✅ 3 sample borrowing transactions
- ✅ 1 overdue transaction (for testing)
- ✅ Sample email logs

---

## 🔐 Security Recommendations

### After Installation

1. **Change default passwords immediately:**
   - Login as admin
   - Go to Staff menu
   - Edit each user and change password

2. **Remove or protect setup directory:**
   ```bash
   # Option 1: Delete it
   rm -rf setup/
   
   # Option 2: Rename it
   mv setup/ setup_backup/
   ```

3. **Update database credentials:**
   - Use a strong password for MySQL root user
   - Create a dedicated database user with limited privileges

### For Production Use

1. **Set PHP error display to off:**
   ```php
   // In php.ini
   display_errors = Off
   log_errors = On
   ```

2. **Enable HTTPS:**
   - Get SSL certificate (Let's Encrypt is free)
   - Force HTTPS redirects

3. **Regular backups:**
   - Backup database daily
   - Keep multiple backup copies

---

## 📱 Mobile Testing

The system is responsive and works on mobile devices:

1. Find your computer's IP address:
   ```bash
   # Windows
   ipconfig
   
   # Mac/Linux
   ifconfig
   ```

2. On your mobile device, connect to same WiFi
3. Access: `http://YOUR_IP_ADDRESS/multimedia-equipment-watcher/`

---

## 🎯 Next Steps

After successful installation:

1. ✅ Login and explore the dashboard
2. ✅ Add your own equipment items
3. ✅ Create test borrowing transactions
4. ✅ Test the return functionality
5. ✅ Try the overdue notifications
6. ✅ Add more staff accounts if needed
7. ✅ Customize the system colors (edit `assets/css/style.css`)

---

## 💡 Tips for Success

- **Start simple**: Begin with a few equipment items
- **Test everything**: Try all features before going live
- **Train users**: Show staff how to use the system
- **Regular maintenance**: Check overdue items daily
- **Keep backups**: Export database regularly

---

## 📞 Need Help?

If you encounter any issues:

1. Check this setup guide first
2. Review the main README.md file
3. Check PHP and MySQL error logs
4. Verify all files are in place
5. Test with different browsers

---

**Happy Equipment Tracking! 📹✨**
