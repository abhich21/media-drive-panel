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
    role ENUM('superadmin', 'client', 'promoter', 'cleaning_staff') NOT NULL DEFAULT 'promoter',
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
    car_code VARCHAR(10) NOT NULL,
    engine_number VARCHAR(50),
    name VARCHAR(100) NOT NULL,
    model VARCHAR(100),

    color VARCHAR(50),
    image_url VARCHAR(255),
    initial_km DECIMAL(10,1) DEFAULT 0,
    initial_fuel INT DEFAULT 100,
    status ENUM('standby', 'cleaning', 'cleaned', 'pod_lineup', 'on_drive', 'returned', 'hotel', 'out_of_service', 'under_inspection') DEFAULT 'standby',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    UNIQUE KEY unique_car_code_per_event (event_id, car_code)
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
-- PR Firms Table
-- ================================================
CREATE TABLE IF NOT EXISTS pr_firms (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(150) NOT NULL,
    contact_person VARCHAR(150),
    phone VARCHAR(20),
    email VARCHAR(150),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ================================================
-- Influencers/Journalists Table (Pre-mapped per event)
-- ================================================
CREATE TABLE IF NOT EXISTS influencers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT NOT NULL,
    pr_firm_id INT,
    name VARCHAR(150) NOT NULL,
    outlet VARCHAR(150),
    phone VARCHAR(20),
    email VARCHAR(150),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (pr_firm_id) REFERENCES pr_firms(id) ON DELETE SET NULL
);

-- ================================================
-- Car Assignments (Pre-map car → influencer → promoter)
-- Note: With new workflow, real-time mapping happens via car_logs
-- ================================================
CREATE TABLE IF NOT EXISTS car_assignments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT NOT NULL,
    car_id INT NOT NULL,
    influencer_id INT,
    promoter_id INT,
    scheduled_date DATE,
    scheduled_time TIME,
    is_completed TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE,
    FOREIGN KEY (influencer_id) REFERENCES influencers(id) ON DELETE SET NULL,
    FOREIGN KEY (promoter_id) REFERENCES users(id) ON DELETE SET NULL
);

-- ================================================
-- Promoter ↔ PR Firm Mapping (Which PR firms each promoter handles)
-- ================================================
CREATE TABLE IF NOT EXISTS promoter_pr_firms (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT NOT NULL,
    promoter_id INT NOT NULL,
    pr_firm_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (promoter_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (pr_firm_id) REFERENCES pr_firms(id) ON DELETE CASCADE,
    UNIQUE KEY unique_promoter_pr_firm (event_id, promoter_id, pr_firm_id)
);

-- ================================================
-- PR Firm ↔ Car Mapping (Which cars are assigned to each PR firm)
-- ================================================
CREATE TABLE IF NOT EXISTS pr_firm_cars (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT NOT NULL,
    pr_firm_id INT NOT NULL,
    car_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (pr_firm_id) REFERENCES pr_firms(id) ON DELETE CASCADE,
    FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE,
    UNIQUE KEY unique_pr_firm_car (event_id, car_id)
);

-- ================================================
-- Car Logs (Status changes, drives, km/fuel logs)
-- ================================================
CREATE TABLE IF NOT EXISTS car_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    car_id INT NOT NULL,
    event_id INT NOT NULL,
    promoter_id INT,
    influencer_id INT,
    pr_firm_id INT,
    log_type ENUM('status_change', 'exit', 'return', 'emergency', 'damage', 'note') NOT NULL,
    previous_status VARCHAR(50),
    new_status VARCHAR(50),
    journalist_name VARCHAR(150),
    journalist_outlet VARCHAR(150),
    journalist_phone VARCHAR(20),
    km_reading DECIMAL(10,1),
    fuel_level INT,
    exit_time DATETIME,
    return_time DATETIME,
    has_damage TINYINT(1) DEFAULT 0,
    damage_description TEXT,
    photo_urls TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (promoter_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (influencer_id) REFERENCES influencers(id) ON DELETE SET NULL,
    FOREIGN KEY (pr_firm_id) REFERENCES pr_firms(id) ON DELETE SET NULL
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
CREATE INDEX idx_cars_code ON cars(car_code);
CREATE INDEX idx_car_logs_car ON car_logs(car_id);
CREATE INDEX idx_car_logs_event ON car_logs(event_id);
CREATE INDEX idx_car_logs_type ON car_logs(log_type);
CREATE INDEX idx_attendance_event ON promoter_attendance(event_id);
CREATE INDEX idx_attendance_date ON promoter_attendance(date);
CREATE INDEX idx_feedback_event ON feedback(event_id);
CREATE INDEX idx_influencers_event ON influencers(event_id);
CREATE INDEX idx_influencers_pr_firm ON influencers(pr_firm_id);
CREATE INDEX idx_car_assignments_event ON car_assignments(event_id);
CREATE INDEX idx_car_assignments_car ON car_assignments(car_id);
CREATE INDEX idx_promoter_pr_firms_event ON promoter_pr_firms(event_id);
CREATE INDEX idx_promoter_pr_firms_promoter ON promoter_pr_firms(promoter_id);
CREATE INDEX idx_pr_firm_cars_event ON pr_firm_cars(event_id);
CREATE INDEX idx_pr_firm_cars_pr_firm ON pr_firm_cars(pr_firm_id);

-- ================================================
-- Sample Users (PLAIN TEXT PASSWORDS - TESTING ONLY!)
-- ================================================
INSERT INTO users (name, email, password, role) VALUES 
('Admin', 'admin@cloudplay.com', 'admin123', 'superadmin'),
('Demo Client', 'client@demo.com', 'client123', 'client'),
('Demo Promoter', 'promoter@demo.com', 'promoter123', 'promoter'),
('John Promoter', 'john@demo.com', 'john123', 'promoter'),
('Sarah Promoter', 'sarah@demo.com', 'sarah123', 'promoter'),
('Cleaning Staff', 'cleaning@demo.com', 'cleaning123', 'cleaning_staff');

-- ================================================
-- Sample Event
-- ================================================
INSERT INTO events (name, client_name, location, start_date, end_date, status) VALUES
('Tata Punch Launch', 'Tata Motors', 'Mumbai', '2024-12-20', '2024-12-25', 'active');

-- ================================================
-- Sample Cars with Car Codes
-- ================================================
INSERT INTO cars (event_id, car_code, engine_number, name, model, color, status) VALUES
(1, 'A1', 'ENG001234', 'Tata Punch', 'Creative 1.2', 'White', 'standby'),
(1, 'A2', 'ENG001235', 'Tata Punch', 'Creative 1.2', 'Red', 'standby'),
(1, 'B1', 'ENG002001', 'Tata Nexon', 'EV Max', 'Blue', 'standby'),
(1, 'B2', 'ENG002002', 'Tata Nexon', 'EV Max', 'White', 'standby'),
(1, 'C1', 'ENG003001', 'Tata Harrier', 'XZ+', 'Black', 'standby'),
(1, 'C2', 'ENG003002', 'Tata Harrier', 'XZ+', 'Grey', 'standby');

-- ================================================
-- Sample PR Firms
-- ================================================
INSERT INTO pr_firms (name, contact_person, phone, email) VALUES
('MediaWorks PR', 'Rahul Sharma', '9876543210', 'rahul@mediaworks.com'),
('AutoPR Agency', 'Priya Patel', '9876543211', 'priya@autopr.com'),
('Velocity Communications', 'Amit Kumar', '9876543212', 'amit@velocity.com');

-- ================================================
-- Sample Influencers (Pre-mapped to Event)
-- ================================================
INSERT INTO influencers (event_id, pr_firm_id, name, outlet, phone) VALUES
(1, 1, 'Ravi Auto Review', 'YouTube', '9800000001'),
(1, 1, 'Neha Car Tips', 'Instagram', '9800000002'),
(1, 2, 'AutoCar India', 'Magazine', '9800000003'),
(1, 2, 'Car Dekho Team', 'Website', '9800000004'),
(1, 3, 'MotorOctane', 'YouTube', '9800000005'),
(1, 3, 'ZigWheels Reporter', 'Website', '9800000006');

-- ================================================
-- Sample Car Assignments (Legacy - kept for reference)
-- ================================================
INSERT INTO car_assignments (event_id, car_id, influencer_id, promoter_id, scheduled_date) VALUES
(1, 1, 1, 3, '2024-12-21'),
(1, 2, 2, 3, '2024-12-21'),
(1, 3, 3, 4, '2024-12-22'),
(1, 4, 4, 4, '2024-12-22'),
(1, 5, 5, 5, '2024-12-23'),
(1, 6, 6, 5, '2024-12-23');

-- ================================================
-- Sample Promoter ↔ PR Firm Mappings (NEW WORKFLOW)
-- Promoter 3 handles MediaWorks PR
-- Promoter 4 handles AutoPR Agency  
-- Promoter 5 handles Velocity Communications
-- ================================================
INSERT INTO promoter_pr_firms (event_id, promoter_id, pr_firm_id) VALUES
(1, 3, 1),  -- Demo Promoter handles MediaWorks PR
(1, 4, 2),  -- John Promoter handles AutoPR Agency
(1, 5, 3);  -- Sarah Promoter handles Velocity Communications

-- ================================================
-- Sample PR Firm ↔ Car Mappings (NEW WORKFLOW)
-- MediaWorks PR gets cars A1, A2
-- AutoPR Agency gets cars B1, B2
-- Velocity Communications gets cars C1, C2
-- ================================================
INSERT INTO pr_firm_cars (event_id, pr_firm_id, car_id) VALUES
(1, 1, 1),  -- MediaWorks PR → A1
(1, 1, 2),  -- MediaWorks PR → A2
(1, 2, 3),  -- AutoPR Agency → B1
(1, 2, 4),  -- AutoPR Agency → B2
(1, 3, 5),  -- Velocity Communications → C1
(1, 3, 6);  -- Velocity Communications → C2

