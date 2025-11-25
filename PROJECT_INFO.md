# 📋 Project Information

## Multimedia Equipment Watcher - Technical Specifications

### 📌 Project Type
Grade 12 Thesis Project - Equipment Management System

### 🛠 Technology Stack

**Frontend:**
- HTML5
- CSS3 (Custom styling, no frameworks)
- Vanilla JavaScript (minimal, progressive enhancement)

**Backend:**
- Native PHP 7.4+ (No frameworks)
- MySQL 5.7+
- PDO for database operations

**Design:**
- Custom responsive CSS
- Green (#2ecc71) + White theme
- Gray accent color (#4b4b4b)
- Mobile-friendly layout

### 🎯 Core Features

#### 1. Authentication System
- Role-based access control (Admin, Staff)
- Secure password hashing (bcrypt)
- Session management
- Login/logout functionality

#### 2. Equipment Management
- CRUD operations (Create, Read, Update, Delete)
- Equipment status tracking:
  - Available
  - Borrowed
  - Damaged
  - Lost
- Search and filter capabilities
- Category organization

#### 3. Borrowing System
- Create borrow transactions
- Manual borrower entry (no borrower accounts)
- Required fields:
  - Equipment selection
  - Borrower name
  - Borrower email
  - Date borrowed (auto-filled, editable)
  - Due date (required)
  - Remarks (optional)
- Return functionality
- Automatic status updates

#### 4. Overdue Detection
- Automatic overdue calculation
- Days overdue tracking
- Visual indicators
- Email notification capability

#### 5. Staff Management (Admin Only)
- Add/Edit/Delete staff accounts
- Role assignment
- Password management
- Account listing

#### 6. Email Notifications
- Mailtrap integration for testing
- Automated overdue reminders
- Email logging system
- Success/failure tracking

#### 7. Audit Logging
- Email notification logs
- Transaction history
- Timestamp tracking

### 🗄 Database Schema

**Table: users**
```sql
- id (INT, PK, AUTO_INCREMENT)
- name (VARCHAR 100)
- email (VARCHAR 100, UNIQUE)
- password (VARCHAR 255, HASHED)
- role (ENUM: 'admin', 'staff')
- created_at (TIMESTAMP)
```

**Table: equipment**
```sql
- id (INT, PK, AUTO_INCREMENT)
- name (VARCHAR 100)
- description (TEXT)
- category (VARCHAR 50)
- status (ENUM: 'available', 'borrowed', 'damaged', 'lost')
- created_at (TIMESTAMP)
```

**Table: borrowing_transactions**
```sql
- id (INT, PK, AUTO_INCREMENT)
- equipment_id (INT, FK -> equipment.id)
- borrower_name (VARCHAR 100)
- borrower_email (VARCHAR 100)
- date_borrowed (DATE)
- due_date (DATE)
- date_returned (DATE, NULLABLE)
- status (ENUM: 'borrowed', 'returned', 'overdue')
- remarks (TEXT, NULLABLE)
- created_at (TIMESTAMP)
```

**Table: email_logs**
```sql
- id (INT, PK, AUTO_INCREMENT)
- transaction_id (INT, FK -> borrowing_transactions.id)
- email_to (VARCHAR 100)
- subject (VARCHAR 255)
- message (TEXT)
- sent_at (TIMESTAMP)
- status (ENUM: 'success', 'failed')
```

### 🔐 Security Features

1. **Authentication**
   - Bcrypt password hashing
   - Session-based authentication
   - Login required for all pages
   - Role-based access control

2. **SQL Injection Prevention**
   - PDO prepared statements
   - Parameter binding
   - Input sanitization

3. **XSS Prevention**
   - `htmlspecialchars()` output escaping
   - Content Security headers

4. **CSRF Protection**
   - Session validation
   - Can be enhanced with tokens

5. **File Security**
   - `.htaccess` configuration
   - Setup directory protection
   - Configuration file protection

### 📊 System Capabilities

**User Management:**
- 2 user roles (Admin, Staff)
- Unlimited user accounts
- Self-service password changes

**Equipment Tracking:**
- Unlimited equipment items
- Real-time status updates
- Category-based organization
- Search and filter functionality

**Transaction Management:**
- Complete borrow/return workflow
- Historical transaction records
- Overdue detection and alerts
- Email notification system

### 🎨 Design Specifications

**Color Palette:**
- Primary Green: #2ecc71
- Dark Green: #27ae60
- Main Gray: #4b4b4b
- Light Gray: #f8f9fa
- White: #ffffff

**Typography:**
- Font Family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif
- Base Font Size: 14px
- Headings: Scaled appropriately

**Layout:**
- Maximum Container Width: 1400px
- Responsive Breakpoint: 768px
- Optimized for: 1366×768 screens

### 📦 Included Components

**Scripts:**
- `check_installation.php` - System verification
- `backup_database.php` - Database backup utility
- `send_overdue_notifications.php` - Email automation

**Documentation:**
- `README.md` - Main documentation
- `SETUP_GUIDE.md` - Installation guide
- `PROJECT_INFO.md` - This file
- `LICENSE.txt` - License information

**Sample Data:**
- 1 Admin account
- 2 Staff accounts
- 5 Equipment items
- 3 Borrowing transactions
- 1 Overdue transaction (for testing)

### 🚀 Performance Considerations

- Lightweight (no frameworks)
- Minimal JavaScript
- Optimized SQL queries
- Indexed database columns
- Efficient session management

### 📱 Browser Compatibility

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+
- Mobile browsers (iOS Safari, Chrome Mobile)

### 🔄 System Requirements

**Server:**
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- 50MB disk space minimum
- 128MB RAM minimum

**Client:**
- Modern web browser
- JavaScript enabled (optional, for enhancements)
- Minimum screen resolution: 1024×768

### 📈 Scalability

**Current Capacity:**
- Designed for small to medium organizations
- Can handle hundreds of equipment items
- Thousands of transactions
- Multiple concurrent users

**Extension Options:**
- Can be extended with API endpoints
- Database can be optimized for larger datasets
- Caching can be implemented
- Load balancing for high traffic

### 🎓 Educational Value

This project demonstrates:
- ✅ PHP fundamentals
- ✅ MySQL database design
- ✅ CRUD operations
- ✅ Authentication & authorization
- ✅ Security best practices
- ✅ Responsive web design
- ✅ Email integration
- ✅ File organization
- ✅ Documentation skills

### 📞 Support & Maintenance

**Backup:**
- Built-in database backup utility
- Exportable SQL dumps
- Restore capability

**Updates:**
- Can be updated via file replacement
- Database migrations can be added
- Modular structure for easy updates

**Troubleshooting:**
- Built-in installation checker
- Detailed error messages
- Comprehensive documentation

---

**Version:** 1.0.0  
**Release Date:** November 2024  
**Project Type:** Educational - Grade 12 Thesis  
**License:** MIT License  
**Status:** Production Ready ✓

---

### 🎯 Future Enhancement Ideas

- [ ] QR code generation for equipment
- [ ] Barcode scanning
- [ ] Advanced reporting (PDF export)
- [ ] Dashboard charts and graphs
- [ ] SMS notifications
- [ ] Mobile app (PWA)
- [ ] Multi-language support
- [ ] Equipment maintenance tracking
- [ ] Reservation system
- [ ] Calendar integration
- [ ] Export to Excel/CSV
- [ ] Dark mode theme
- [ ] Equipment photos/gallery
- [ ] User activity logs
- [ ] API endpoints for integration
- [ ] Automated backups to cloud

---

**Built with ❤️ for education and learning**
