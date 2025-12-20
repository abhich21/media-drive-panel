-- MDM Database Schema
-- Media Drive Management System

-- ================================================
-- Users Table (All roles: superadmin, client, promoter)
-- ================================================
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('superadmin', 'client', 'promoter') NOT NULL DEFAULT 'promoter',
    phone VARCHAR(20),
    avatar VARCHAR(255),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ================================================
-- Events Table (Media Drive Events)
-- ================================================
CREATE TABLE IF NOT EXISTS events (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL,
    client_name VARCHAR(150),
    location VARCHAR(255),
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('upcoming', 'active', 'completed') DEFAULT 'upcoming',
    logo_url VARCHAR(255),
    theme_color VARCHAR(7) DEFAULT '#080808',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ================================================
-- Cars Table
-- ================================================
CREATE TABLE IF NOT EXISTS cars (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    model VARCHAR(100),
    registration_number VARCHAR(50),
    color VARCHAR(50),
    image_url VARCHAR(255),
    initial_km DECIMAL(10,1) DEFAULT 0,
    initial_fuel INT DEFAULT 100,
    status ENUM('standby', 'cleaning', 'cleaned', 'on_drive', 'returned', 'hotel', 'pod_lineup') DEFAULT 'standby',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
);

-- ================================================
-- Promoters Assignment (Link promoters to events)
-- ================================================
CREATE TABLE IF NOT EXISTS event_promoters (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT NOT NULL,
    promoter_id INT NOT NULL,
    assigned_cars TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (promoter_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_assignment (event_id, promoter_id)
);

-- ================================================
-- Car Logs (Status changes, drives, km/fuel logs)
-- ================================================
CREATE TABLE IF NOT EXISTS car_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    car_id INT NOT NULL,
    event_id INT NOT NULL,
    promoter_id INT,
    log_type ENUM('status_change', 'exit', 'return', 'damage', 'note') NOT NULL,
    previous_status VARCHAR(50),
    new_status VARCHAR(50),
    journalist_name VARCHAR(150),
    journalist_outlet VARCHAR(150),
    journalist_phone VARCHAR(20),
    km_reading DECIMAL(10,1),
    fuel_level INT,
    exit_time DATETIME,
    return_time DATETIME,
    photo_urls TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (promoter_id) REFERENCES users(id) ON DELETE SET NULL
);

-- ================================================
-- Promoter Attendance
-- ================================================
CREATE TABLE IF NOT EXISTS promoter_attendance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT NOT NULL,
    promoter_id INT NOT NULL,
    date DATE NOT NULL,
    check_in_time TIME,
    check_out_time TIME,
    status ENUM('present', 'absent', 'late', 'half_day') DEFAULT 'present',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (promoter_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_attendance (event_id, promoter_id, date)
);

-- ================================================
-- Feedback (Post-drive mini feedback)
-- ================================================
CREATE TABLE IF NOT EXISTS feedback (
    id INT PRIMARY KEY AUTO_INCREMENT,
    car_log_id INT,
    event_id INT NOT NULL,
    promoter_id INT,
    journalist_name VARCHAR(150),
    rating INT CHECK (rating >= 1 AND rating <= 5),
    experience TEXT,
    strong_points TEXT,
    weak_points TEXT,
    concerns TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (car_log_id) REFERENCES car_logs(id) ON DELETE SET NULL,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (promoter_id) REFERENCES users(id) ON DELETE SET NULL
);

-- ================================================
-- Feedback Forms (Custom forms by superadmin)
-- ================================================
CREATE TABLE IF NOT EXISTS feedback_forms (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT NOT NULL,
    form_name VARCHAR(150) NOT NULL,
    fields JSON,
    is_active TINYINT(1) DEFAULT 1,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ================================================
-- Indexes for Performance
-- ================================================
CREATE INDEX idx_cars_event ON cars(event_id);
CREATE INDEX idx_cars_status ON cars(status);
CREATE INDEX idx_car_logs_car ON car_logs(car_id);
CREATE INDEX idx_car_logs_event ON car_logs(event_id);
CREATE INDEX idx_car_logs_type ON car_logs(log_type);
CREATE INDEX idx_attendance_event ON promoter_attendance(event_id);
CREATE INDEX idx_attendance_date ON promoter_attendance(date);
CREATE INDEX idx_feedback_event ON feedback(event_id);

-- ================================================
-- Sample Superadmin User (password: admin123)
-- ================================================
INSERT INTO users (name, email, password, role) VALUES 
('Admin', 'admin@cloudplay.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'superadmin'),
('Demo Client', 'client@demo.com', '$2y$10$rHcFp.K8KQkJqJQKgL9HQuqBcTMVU0mj2VqhMFJ4VqBhEyJQu3Ey6', 'client'),
('Demo Promoter', 'promoter@demo.com', '$2y$10$H.3iJKQS8hKx5OJlNqO.ROk.NQrJDpK8LqVXYBxKK.V9UpZXfLkYy', 'promoter');
