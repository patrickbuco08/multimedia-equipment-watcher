# 📹 Multimedia Equipment Watcher

A simple Grade 12 thesis project for managing multimedia equipment borrowing system. Built with Native PHP, HTML, and CSS (no frameworks).

## 🎯 Features

### User Roles
- **Admin**: Full access to all features including staff management and logs
- **Staff**: Can manage equipment, create/return borrowing transactions, and view overdue items

### Core Functionality
1. **Equipment Management**
   - Add, edit, and delete equipment
   - Track equipment status (available, borrowed, damaged, lost)
   - Search and filter equipment

2. **Borrowing System**
   - Create borrow transactions with borrower details
   - Automatic equipment status updates
   - Track due dates and return dates
   - Manual borrower name and email entry (no user accounts for borrowers)

3. **Overdue Detection**
   - Automatic detection of overdue items
   - Days overdue calculation
   - Email notification system

4. **Staff Management** (Admin Only)
   - Add, edit, and delete staff accounts
   - Manage user roles

5. **Email Logs** (Admin Only)
   - View all sent email notifications
   - Track success/failure status

## 🛠 Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- XAMPP/MAMP/WAMP (recommended for local development)

### Step 1: Setup Database

1. Start your MySQL server
2. Import the database schema:
   ```bash
   mysql -u root -p < setup/install.sql
   ```
   Or manually execute the SQL file in phpMyAdmin/MySQL Workbench

### Step 2: Configure Database Connection

The database configuration is already set in `config/database.php` with the following credentials:
```php
Host: 127.0.0.1
Port: 3306
Username: root
Password: (empty)
Database: multimedia_equipment_watcher
```

If your MySQL setup is different, update the constants in `config/database.php`.

### Step 3: Setup Web Server

#### Using XAMPP/MAMP/WAMP
1. Copy the project folder to your web server directory:
   - XAMPP: `C:\xampp\htdocs\` (Windows) or `/Applications/XAMPP/htdocs/` (Mac)
   - MAMP: `/Applications/MAMP/htdocs/`
   - WAMP: `C:\wamp64\www\`

2. Access the application:
   ```
   http://localhost/multimedia-equipment-watcher/
   ```

#### Using PHP Built-in Server
```bash
cd /path/to/multimedia-equipment-watcher
php -S localhost:8000
```
Then access: `http://localhost:8000/`

### Step 4: Login

Default accounts are already created in the database:

**Admin Account:**
- Email: `admin@example.com`
- Password: `admin123`

**Staff Accounts:**
- Email: `john.staff@example.com` / Password: `admin123`
- Email: `jane.staff@example.com` / Password: `admin123`

## 📧 Email Configuration (Mailtrap)

The system includes automated email notifications for overdue equipment.

### Setup Mailtrap

1. Sign up for free at [https://mailtrap.io/](https://mailtrap.io/)
2. Create a new inbox
3. Get your SMTP credentials
4. Update the configuration in `/email/send_overdue_notifications.php`:
   ```php
   define('SMTP_HOST', 'sandbox.smtp.mailtrap.io');
   define('SMTP_PORT', 2525);
   define('SMTP_USERNAME', 'your_mailtrap_username');
   define('SMTP_PASSWORD', 'your_mailtrap_password');
   ```

### Sending Email Notifications

**Manual:**
Visit: `http://localhost/multimedia-equipment-watcher/email/send_overdue_notifications.php`

**Via Cron (Linux/Mac):**
```bash
# Run daily at 9 AM
0 9 * * * /usr/bin/php /path/to/multimedia-equipment-watcher/email/send_overdue_notifications.php
```

**Via Task Scheduler (Windows):**
1. Open Task Scheduler
2. Create Basic Task
3. Set trigger (e.g., Daily at 9:00 AM)
4. Action: Start a program
5. Program: `C:\xampp\php\php.exe`
6. Arguments: `C:\xampp\htdocs\multimedia-equipment-watcher\email\send_overdue_notifications.php`

### Production Email Setup

For production use with real email delivery:

1. Install PHPMailer:
   ```bash
   composer require phpmailer/phpmailer
   ```

2. Uncomment and configure the PHPMailer code in `/email/send_overdue_notifications.php`

3. Use a real SMTP service (Gmail, SendGrid, Mailgun, etc.)

## 🎨 Design Theme

- **Primary Colors**: White + Green (#2ecc71)
- **Secondary Color**: Gray (#4b4b4b)
- **Design Style**: Clean, modern, minimalistic
- **Optimized for**: 1366×768 laptop screens (responsive)

## 📁 Project Structure

```
multimedia-equipment-watcher/
├── assets/
│   └── css/
│       └── style.css          # Main stylesheet
├── borrowing/
│   ├── add.php                # Create borrow transaction
│   ├── list.php               # View all transactions
│   └── return.php             # Mark as returned
├── config/
│   └── database.php           # Database configuration
├── email/
│   └── send_overdue_notifications.php  # Email script
├── equipment/
│   ├── add.php                # Add equipment
│   ├── delete.php             # Delete equipment
│   ├── edit.php               # Edit equipment
│   └── list.php               # View all equipment
├── includes/
│   ├── footer.php             # Footer template
│   └── header.php             # Header with navigation
├── logs/
│   └── view.php               # View email logs (admin)
├── overdue/
│   └── list.php               # View overdue items
├── setup/
│   └── install.sql            # Database schema and sample data
├── staff/
│   ├── add.php                # Add staff (admin)
│   ├── delete.php             # Delete staff (admin)
│   ├── edit.php               # Edit staff (admin)
│   └── list.php               # View all staff (admin)
├── dashboard.php              # Main dashboard
├── index.php                  # Login page
├── logout.php                 # Logout handler
└── README.md                  # This file
```

## 🗄 Database Schema

### users
- User accounts (admin and staff)
- Hashed passwords using bcrypt

### equipment
- Equipment inventory
- Status tracking (available, borrowed, damaged, lost)

### borrowing_transactions
- Borrow and return records
- Links equipment to borrower
- Tracks dates and status

### email_logs
- Email notification history
- Success/failure tracking

## 🔒 Security Features

- Password hashing with PHP's `password_hash()`
- SQL injection prevention using prepared statements
- Session-based authentication
- Role-based access control (RBAC)
- CSRF protection ready (can be enhanced)

## 📝 Usage Guide

### Adding Equipment
1. Navigate to **Equipment** menu
2. Click **+ Add Equipment**
3. Fill in equipment details
4. Click **Add Equipment**

### Creating Borrow Transaction
1. Navigate to **Borrowing** menu
2. Click **+ New Borrow**
3. Select available equipment
4. Enter borrower name and email (typed manually)
5. Set borrow date (defaults to today)
6. Set due date
7. Click **Create Transaction**

### Returning Equipment
1. Navigate to **Borrowing** menu
2. Find the transaction
3. Click **Return** button
4. Equipment status automatically updates to "available"

### Viewing Overdue Items
1. Navigate to **Overdue** menu
2. View all overdue transactions
3. Click **Send Email Notifications** to notify borrowers

### Managing Staff (Admin Only)
1. Navigate to **Staff** menu
2. Add, edit, or delete staff accounts
3. Assign roles (admin or staff)

### Viewing Email Logs (Admin Only)
1. Navigate to **Logs** menu
2. View all sent email notifications
3. Check success/failure status

## 🚀 Future Enhancements

- QR code generation for equipment
- Barcode scanning for quick checkout
- Advanced reporting and analytics
- Mobile app integration
- Real-time notifications
- Equipment maintenance tracking
- Multi-branch support

## 📄 License

This is a Grade 12 thesis project for educational purposes.

## 👨‍💻 Support

For questions or issues, please contact the development team.

---

**Multimedia Equipment Watcher** - Making equipment management simple and efficient! 📹✨
