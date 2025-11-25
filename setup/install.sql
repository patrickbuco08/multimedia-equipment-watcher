-- Multimedia Equipment Watcher Database Setup
-- Drop database if exists and create fresh
DROP DATABASE IF EXISTS multimedia_equipment_watcher;
CREATE DATABASE multimedia_equipment_watcher;
USE multimedia_equipment_watcher;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    is_active TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Equipment table
CREATE TABLE equipment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    category VARCHAR(50),
    total_quantity INT NOT NULL DEFAULT 1,
    available_quantity INT NOT NULL DEFAULT 1,
    status ENUM('available', 'borrowed', 'damaged', 'lost') NOT NULL DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Borrowing transactions table
CREATE TABLE borrowing_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    equipment_id INT NOT NULL,
    user_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    quantity_returned INT NOT NULL DEFAULT 0,
    date_borrowed DATE NOT NULL,
    due_date DATE NOT NULL,
    date_returned DATE NULL,
    status ENUM('pending', 'borrowed', 'returned', 'partially_returned', 'lost') NOT NULL DEFAULT 'pending',
    remarks TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (equipment_id) REFERENCES equipment(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Email logs table
CREATE TABLE email_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id INT NOT NULL,
    email_to VARCHAR(100) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('success', 'failed') NOT NULL DEFAULT 'success',
    FOREIGN KEY (transaction_id) REFERENCES borrowing_transactions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Equipment reports table (damage/lost)
CREATE TABLE equipment_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    equipment_id INT NOT NULL,
    report_type ENUM('damage', 'lost') NOT NULL,
    reported_by INT NOT NULL,
    report_date DATE NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    description TEXT NOT NULL,
    image_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (equipment_id) REFERENCES equipment(id) ON DELETE CASCADE,
    FOREIGN KEY (reported_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert initial admin account (password: admin123)
INSERT INTO users (name, email, password, role, is_active) VALUES
('Admin User', 'admin@oct.edu.ph', '$2y$10$4nno1mmWK/ilCl9lJjVpaOWW2ZHnNMomU6m4zp0f4POinzYUxnhS2', 'admin', 1),
('Chantrice Dilla', 'user1@oct.edu.ph', '$2y$10$4nno1mmWK/ilCl9lJjVpaOWW2ZHnNMomU6m4zp0f4POinzYUxnhS2', 'user', 1),
('Clarence User', 'user2@oct.edu.ph', '$2y$10$4nno1mmWK/ilCl9lJjVpaOWW2ZHnNMomU6m4zp0f4POinzYUxnhS2', 'user', 1);

-- Insert sample equipment
INSERT INTO equipment (name, description, category, total_quantity, available_quantity, status) VALUES
('Canon EOS R6', 'Professional mirrorless camera with 4K video recording', 'Camera', 12, 12, 'available'),
('Sony A7 III', 'Full-frame mirrorless camera', 'Camera', 8, 5, 'available'),
('Rode VideoMic Pro', 'Professional shotgun microphone', 'Audio', 15, 15, 'available'),
('Manfrotto MT055 Tripod', 'Professional aluminum tripod', 'Support', 10, 7, 'available'),
('Godox SL-60W', 'LED video light 60W', 'Lighting', 20, 20, 'available');

-- Insert sample borrowing transactions (including various statuses)
INSERT INTO borrowing_transactions (equipment_id, user_id, quantity, quantity_returned, date_borrowed, due_date, date_returned, status, remarks) VALUES
(2, 2, 2, 0, '2025-11-10', '2025-11-20', NULL, 'borrowed', 'For documentary project'),
(4, 3, 3, 0, '2025-11-15', '2025-11-22', NULL, 'borrowed', 'For video shoot'),
(2, 2, 1, 1, '2025-11-01', '2025-11-10', '2025-11-09', 'returned', 'Returned in good condition'),
(1, 2, 4, 0, '2025-11-21', '2025-11-28', NULL, 'pending', 'Mock borrow - John Staff - Pending approval'),
(1, 3, 4, 0, '2025-11-21', '2025-11-28', NULL, 'borrowed', 'Mock borrow - Jane Staff'),
(2, 2, 2, 0, '2025-11-21', '2025-11-28', NULL, 'pending', 'Mock borrow - John Staff - Pending approval'),
(2, 3, 2, 1, '2025-11-21', '2025-11-28', NULL, 'partially_returned', 'Mock borrow - Jane Staff - 1 of 2 returned'),
(3, 2, 5, 0, '2025-11-21', '2025-11-28', NULL, 'borrowed', 'Mock borrow - John Staff'),
(3, 3, 5, 0, '2025-11-21', '2025-11-28', NULL, 'borrowed', 'Mock borrow - Jane Staff'),
(4, 2, 4, 0, '2025-11-21', '2025-11-28', NULL, 'borrowed', 'Mock borrow - John Staff'),
(4, 3, 2, 0, '2025-11-21', '2025-11-28', NULL, 'borrowed', 'Mock borrow - Jane Staff'),
(5, 2, 7, 0, '2025-11-21', '2025-11-28', NULL, 'borrowed', 'Mock borrow - John Staff'),
(5, 3, 7, 0, '2025-11-21', '2025-11-28', NULL, 'borrowed', 'Mock borrow - Jane Staff');

-- Insert sample email log for overdue notification
INSERT INTO email_logs (transaction_id, email_to, subject, message, status) VALUES
(1, 'maria.santos@student.edu', 'Overdue Equipment Notice', 'Dear Maria Santos,\n\nThis is a reminder that the equipment "Sony A7 III" you borrowed on 2025-11-10 is now overdue. The due date was 2025-11-20.\n\nPlease return the equipment as soon as possible.\n\nThank you.', 'success');
