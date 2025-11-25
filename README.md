# 📹 Multimedia Equipment Watcher

A simple Grade 12 thesis project for managing a multimedia equipment borrowing system.

Built with **native PHP**, **MySQL**, and **HTML/CSS** (no full-stack frameworks). TailwindCSS is loaded via CDN for UI styling.

---

## ✨ Features

- **Equipment inventory** (add, edit, delete, list)
- **Borrowing workflows** with due dates and statuses
- **Overdue tracking** and email log records
- **Damage / lost reports** for equipment
- **Role-based access** for Admin and Staff
- **Admin dashboard** for staff management and approvals

---

## 🧰 Tech Stack

- PHP 8.2+
- MySQL 8.0+
- TailwindCSS (CDN)
- Native HTML + CSS + a bit of JavaScript

---

## ✅ Requirements

Make sure you have:

- XAMPP (or similar stack providing PHP + MySQL)
- PHP 8.2 or higher
- MySQL 8.0 or higher

> You can use XAMPP only for MySQL and still run the app via PHP's built-in server.

---

## 🚀 Quick Start

### 1. Clone or Download the Project

```bash
git clone https://github.com/your-username/multimedia-equipment-watcher.git
cd multimedia-equipment-watcher
```

Or download the ZIP from GitHub and extract it.

### 2. Start MySQL (XAMPP)

1. Open **XAMPP Control Panel**
2. Click **Start** for **MySQL** (Apache is optional if you use PHP's built-in server)

### 3. Create / Import the Database (First Install)

The database schema and sample data live in `setup/install.sql`.

> ⚠️ This script contains `DROP DATABASE IF EXISTS multimedia_equipment_watcher;` and will **recreate the database from scratch**. Do not run on a production database without backups.

#### Option A: Using phpMyAdmin (Easiest)

1. Open your browser and go to `http://localhost/phpmyadmin`
2. Click the **Import** tab
3. Click **Choose File** and select: `setup/install.sql`
4. Click **Go** at the bottom
5. Wait for the success message

#### Option B: Using Command Line

```bash
# Windows (inside XAMPP's MySQL bin directory)
cd C:\xampp\mysql\bin
mysql -u root -p < C:\path\to\multimedia-equipment-watcher\setup\install.sql

# Mac/Linux (from anywhere)
mysql -u root -p < /path/to/multimedia-equipment-watcher/setup/install.sql

or

mysql -u root -p
SOURCE /path/to/multimedia-equipment-watcher/setup/install.sql
```

This will create (or recreate) the `multimedia_equipment_watcher` database with tables and sample data.

### 4. Configure Database Connection (Optional Override)

By default, the app expects:

```php
DB_HOST = 127.0.0.1
DB_PORT = 3306
DB_USER = root
DB_PASS = (empty)
DB_NAME = multimedia_equipment_watcher
```

You can override these via environment variables (see `ENV_SETUP.md`) or by adjusting your local MySQL configuration.

### 5. Run the PHP Built-in Server

From the project root:

```bash
php -S localhost:8000
```

### Step 6: Copy the Example File

Copy `.env.example` to create your local environment file:

```bash
cp .env.example .env.local
```

### 7. Access the Application

Open your browser and navigate to:

```text
http://localhost:8000/
```

---

## 🔄 Reset / Re-import Database (Fresh Install)

If `setup/install.sql` changes (for example, you add new tables or sample data) and you want a **fresh database**, you can simply re-run `install.sql`.

> ⚠️ **WARNING:** This will **delete all existing data** in `multimedia_equipment_watcher` and recreate everything from scratch.

### Option A: Fresh Import via phpMyAdmin

1. (Optional but recommended) **Backup current database**:
   - In phpMyAdmin, select `multimedia_equipment_watcher`
   - Go to the **Export** tab and save a copy
2. Go to `http://localhost/phpmyadmin`
3. Click the **Import** tab
4. Click **Choose File** and select the updated `setup/install.sql`
5. Make sure the correct database or no database is selected (the script itself drops/creates the DB)
6. Click **Go**
7. After it finishes, you now have a **fresh database** with the latest schema and sample data

### Option B: Fresh Import via Command Line

```bash
# Windows
cd C:\xampp\mysql\bin
mysql -u root -p < C:\path\to\multimedia-equipment-watcher\setup\install.sql

# Mac/Linux
mysql -u root -p < /path/to/multimedia-equipment-watcher/setup/install.sql
```

Every time you run this command, MySQL will:

1. Drop the `multimedia_equipment_watcher` database (if it exists)
2. Recreate it
3. Recreate all tables
4. Re-insert the sample data defined in `install.sql`

This is equivalent to a **full database reset**.

---

## 📝 Notes

- For environment variable setup and SMTP configuration, see `ENV_SETUP.md`.
- For more detailed setup and troubleshooting, see `SETUP_GUIDE.md`.

---

**Happy Equipment Tracking! 📹✨**
