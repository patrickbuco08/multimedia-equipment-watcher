# ⚡ Quick Reference Guide

## Common Tasks & How-To

### 🔑 Login Credentials

**Default Admin:**
```
Email: admin@example.com
Password: admin123
```

**Staff Accounts:**
```
Email: john.staff@example.com
Password: admin123

Email: jane.staff@example.com
Password: admin123
```

---

## 📋 Frequently Needed Operations

### Register New Staff Account

1. Go to login page
2. Click **Register here** link
3. Fill in:
   - Full Name
   - Email Address
   - Password (minimum 6 characters)
   - Confirm Password
4. Click **Register**
5. Login with your new credentials

**Note:** All registrations are created as Staff accounts by default.

### Change Password for a User

1. Login as Admin
2. Go to **Staff** menu
3. Click **Edit** on the user
4. Enter new password in "New Password" field
5. Confirm password
6. Click **Update Staff**

### Add New Equipment

1. Navigate to **Equipment** menu
2. Click **+ Add Equipment** button
3. Fill in:
   - Equipment Name (required)
   - Category (e.g., Camera, Audio, Lighting)
   - Description
   - Status (usually "Available")
4. Click **Add Equipment**

### Create Borrow Transaction

1. Go to **Borrowing** menu
2. Click **+ New Borrow** button
3. Select equipment from dropdown
4. Enter borrower name (typed manually)
5. Enter borrower email
6. Set borrow date (defaults to today)
7. Set due date (required)
8. Add optional remarks
9. Click **Create Transaction**

### Return Equipment

**Method 1: From Borrowing List**
1. Go to **Borrowing** menu
2. Find the transaction
3. Click **Return** button

**Method 2: From Overdue List**
1. Go to **Overdue** menu
2. Find the item
3. Click **Mark Returned** button

### Send Overdue Notifications

1. Go to **Overdue** menu
2. Click **Send Email Notifications** button
3. Wait for confirmation
4. Check results page

---

## 🛠 Technical Tasks

### Database Backup

**Via Web Interface:**
```
http://localhost/multimedia-equipment-watcher/backup_database.php
```

**Via phpMyAdmin:**
1. Open phpMyAdmin
2. Select `multimedia_equipment_watcher` database
3. Click **Export** tab
4. Click **Go** button
5. Save the .sql file

**Via Command Line:**
```bash
mysqldump -u root -p multimedia_equipment_watcher > backup.sql
```

### Database Restore

**Via phpMyAdmin:**
1. Open phpMyAdmin
2. Select `multimedia_equipment_watcher` database
3. Click **Import** tab
4. Choose your .sql file
5. Click **Go**

**Via Command Line:**
```bash
mysql -u root -p multimedia_equipment_watcher < backup.sql
```

### Check System Status

Access the installation checker:
```
http://localhost/multimedia-equipment-watcher/check_installation.php
```

---

## 🔍 Finding Information

### View Email Logs (Admin Only)

1. Login as Admin
2. Navigate to **Logs** menu
3. View all sent emails
4. Click **View Message** to see email content

### Search Equipment

1. Go to **Equipment** menu
2. Use search box at top
3. Or filter by status dropdown
4. Click **Filter** button

### Search Transactions

1. Go to **Borrowing** menu
2. Use search box (searches equipment, borrower name, email)
3. Or filter by status
4. Click **Filter** button

---

## 📊 Dashboard Information

The Dashboard shows:
- Total Equipment count
- Available Equipment count
- Currently Borrowed count
- Overdue Items count (red alert)
- Recent 5 transactions

---

## 🎨 Customization

### Change Colors

Edit `assets/css/style.css`:

```css
:root {
    --primary-green: #2ecc71;  /* Change primary color */
    --gray: #4b4b4b;            /* Change gray color */
}
```

### Change Logo/Title

Edit `includes/header.php`:

```php
<h1><span>📹</span> Equipment Watcher</h1>
```

---

## 🔐 Security Tips

### After First Login

1. **Change all default passwords immediately**
2. **Delete or rename setup directory:**
   ```bash
   rm -rf setup/
   # or
   mv setup/ setup_backup/
   ```

3. **Restrict access to backup script** (production only)

### Regular Maintenance

- [ ] Change passwords every 90 days
- [ ] Backup database weekly
- [ ] Check overdue items daily
- [ ] Review email logs monthly
- [ ] Update PHP/MySQL regularly

---

## 📧 Email Configuration

### Mailtrap Setup (Testing)

1. Sign up at: https://mailtrap.io/
2. Create inbox
3. Get SMTP credentials
4. Edit: `email/send_overdue_notifications.php`
5. Update lines 13-14:
   ```php
   define('SMTP_USERNAME', 'your_username');
   define('SMTP_PASSWORD', 'your_password');
   ```

### Gmail Setup (Production)

For production use with Gmail:

1. Enable 2-factor authentication
2. Generate App Password
3. Use these settings:
   ```
   Host: smtp.gmail.com
   Port: 587
   Username: your-email@gmail.com
   Password: your-app-password
   ```

---

## 🐛 Troubleshooting

### "Connection failed" Error

**Solutions:**
1. Check MySQL is running
2. Verify credentials in `config/database.php`
3. Test connection in phpMyAdmin

### Blank White Page

**Solutions:**
1. Enable PHP error display
2. Check PHP error logs
3. Verify file permissions
4. Check PHP version (needs 7.4+)

### CSS Not Loading

**Solutions:**
1. Clear browser cache (Ctrl+F5)
2. Check file path: `assets/css/style.css`
3. Verify web server is serving static files
4. Check browser console for 404 errors

### Cannot Login

**Solutions:**
1. Verify database is imported
2. Check users table has data
3. Clear browser cookies
4. Try password reset from database

---

## 🗂 Important File Locations

| File/Folder | Purpose |
|------------|---------|
| `config/database.php` | Database credentials |
| `assets/css/style.css` | All styling |
| `includes/header.php` | Header + navigation |
| `includes/footer.php` | Footer |
| `setup/install.sql` | Database schema |
| `email/send_overdue_notifications.php` | Email script |

---

## ⌨️ Keyboard Shortcuts (Browser)

- `Ctrl + F` / `Cmd + F` - Search in page
- `Ctrl + R` / `Cmd + R` - Refresh page
- `Ctrl + Shift + R` - Hard refresh (clear cache)
- `F5` - Refresh
- `Ctrl + W` - Close tab

---

## 📱 Mobile Access

1. Find your computer's IP:
   ```bash
   # Windows
   ipconfig
   
   # Mac/Linux
   ifconfig
   ```

2. On mobile (same WiFi):
   ```
   http://YOUR_IP/multimedia-equipment-watcher/
   ```

---

## 🔄 Update Process

To update the system:

1. **Backup database first!**
2. Backup current files
3. Replace files with new version
4. Check for database changes
5. Test thoroughly

---

## 📞 Getting Help

1. Check `SETUP_GUIDE.md`
2. Review `README.md`
3. Run `check_installation.php`
4. Check PHP error logs
5. Review browser console

---

## 💡 Pro Tips

- Use Chrome DevTools for debugging (F12)
- Keep multiple database backups
- Test changes on a copy first
- Document any customizations
- Take screenshots of errors
- Keep a change log

---

## 🎯 Best Practices

### For Admins
- Backup database weekly
- Monitor overdue items daily
- Review email logs regularly
- Keep software updated
- Train staff properly

### For Staff
- Double-check borrower emails
- Set realistic due dates
- Add detailed remarks
- Verify equipment before lending
- Process returns promptly

---

**Last Updated:** November 2024  
**Version:** 1.0.0

---

**Need more help?** Check the full documentation in README.md and SETUP_GUIDE.md
