-- MDM Migration for your existing database
-- Based on your actual phpMyAdmin export
-- Safe to run - only adds missing items

-- ================================================
-- 1. Add missing columns to cars table
-- ================================================
ALTER TABLE cars ADD COLUMN car_code VARCHAR(10) NULL AFTER event_id;
ALTER TABLE cars ADD COLUMN engine_number VARCHAR(50) NULL AFTER car_code;

-- ================================================
-- 2. Create pr_firms table (MISSING)
-- ================================================
CREATE TABLE IF NOT EXISTS pr_firms (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(150) NOT NULL,
    contact_person VARCHAR(150) DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    email VARCHAR(150) DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ================================================
-- 3. Create influencers table (MISSING)
-- ================================================
CREATE TABLE IF NOT EXISTS influencers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT NOT NULL,
    pr_firm_id INT DEFAULT NULL,
    name VARCHAR(150) NOT NULL,
    outlet VARCHAR(150) DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    email VARCHAR(150) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (pr_firm_id) REFERENCES pr_firms(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ================================================
-- 4. Create car_assignments table (MISSING)
-- ================================================
CREATE TABLE IF NOT EXISTS car_assignments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT NOT NULL,
    car_id INT NOT NULL,
    influencer_id INT DEFAULT NULL,
    promoter_id INT DEFAULT NULL,
    scheduled_date DATE DEFAULT NULL,
    scheduled_time TIME DEFAULT NULL,
    is_completed TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE,
    FOREIGN KEY (influencer_id) REFERENCES influencers(id) ON DELETE SET NULL,
    FOREIGN KEY (promoter_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ================================================
-- 5. Create promoter_pr_firms table (MISSING)
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ================================================
-- 6. Create pr_firm_cars table (MISSING)
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ================================================
-- 7. Add indexes
-- ================================================
CREATE INDEX idx_cars_code ON cars(car_code);
CREATE INDEX idx_influencers_event ON influencers(event_id);
CREATE INDEX idx_influencers_pr_firm ON influencers(pr_firm_id);

-- ================================================
-- 8. Add more promoter users + cleaning staff
-- ================================================
INSERT INTO users (name, email, password, role) VALUES
('John Promoter', 'john@demo.com', 'john123', 'promoter'),
('Sarah Promoter', 'sarah@demo.com', 'sarah123', 'promoter'),
('Cleaning Staff', 'cleaning@demo.com', 'cleaning123', 'cleaning_staff');

-- ================================================
-- 9. Add sample Event
-- ================================================
INSERT INTO events (name, client_name, location, start_date, end_date, status) VALUES
('Tata Punch Launch', 'Tata Motors', 'Mumbai', '2024-12-20', '2024-12-25', 'active');

-- ================================================
-- 10. Add sample Cars with car_codes
-- ================================================
INSERT INTO cars (event_id, car_code, engine_number, name, model, color, status) VALUES
(1, 'A1', 'ENG001234', 'Tata Punch', 'Creative 1.2', 'White', 'standby'),
(1, 'A2', 'ENG001235', 'Tata Punch', 'Creative 1.2', 'Red', 'standby'),
(1, 'B1', 'ENG002001', 'Tata Nexon', 'EV Max', 'Blue', 'standby'),
(1, 'B2', 'ENG002002', 'Tata Nexon', 'EV Max', 'White', 'standby'),
(1, 'C1', 'ENG003001', 'Tata Harrier', 'XZ+', 'Black', 'standby'),
(1, 'C2', 'ENG003002', 'Tata Harrier', 'XZ+', 'Grey', 'standby');

-- ================================================
-- 11. Add sample PR Firms
-- ================================================
INSERT INTO pr_firms (name, contact_person, phone, email) VALUES
('MediaWorks PR', 'Rahul Sharma', '9876543210', 'rahul@mediaworks.com'),
('AutoPR Agency', 'Priya Patel', '9876543211', 'priya@autopr.com'),
('Velocity Communications', 'Amit Kumar', '9876543212', 'amit@velocity.com');

-- ================================================
-- 12. Add sample Influencers
-- ================================================
INSERT INTO influencers (event_id, pr_firm_id, name, outlet, phone) VALUES
(1, 1, 'Ravi Auto Review', 'YouTube', '9800000001'),
(1, 1, 'Neha Car Tips', 'Instagram', '9800000002'),
(1, 2, 'AutoCar India', 'Magazine', '9800000003'),
(1, 2, 'Car Dekho Team', 'Website', '9800000004'),
(1, 3, 'MotorOctane', 'YouTube', '9800000005'),
(1, 3, 'ZigWheels Reporter', 'Website', '9800000006');

-- ================================================
-- 13. Map Promoters to PR Firms
-- Note: User IDs will be 6=Demo Promoter, 7=John, 8=Sarah after insert
-- ================================================
INSERT INTO promoter_pr_firms (event_id, promoter_id, pr_firm_id) VALUES
(1, 6, 1),   -- Demo Promoter handles MediaWorks PR
(1, 7, 2),   -- John Promoter handles AutoPR Agency
(1, 8, 3);   -- Sarah Promoter handles Velocity Communications

-- ================================================
-- 14. Map PR Firms to Cars
-- Note: Car IDs will be 1-6 after insert
-- ================================================
INSERT INTO pr_firm_cars (event_id, pr_firm_id, car_id) VALUES
(1, 1, 1),   -- MediaWorks PR → A1
(1, 1, 2),   -- MediaWorks PR → A2
(1, 2, 3),   -- AutoPR Agency → B1
(1, 2, 4),   -- AutoPR Agency → B2
(1, 3, 5),   -- Velocity Communications → C1
(1, 3, 6);   -- Velocity Communications → C2

SELECT 'Migration complete!' as status;
