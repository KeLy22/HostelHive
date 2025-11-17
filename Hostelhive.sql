-- ===========================================
-- Database: HostelHive
-- ===========================================
CREATE DATABASE IF NOT EXISTS hostelhive;
USE hostelhive;

-- ===========================================
-- Table: admins
-- ===========================================
CREATE TABLE IF NOT EXISTS admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Pre-seeded admin with correct hash for password "Kely@2022"
INSERT INTO admins (name, email, password)
VALUES 
('Jannatul Ferdues Kely', 'jannatulferdues2022@gmail.com', '$2y$10$6Zt6Z5FJ0yCmSydqAdltGOinlfuThp6TQZl9kWn8gCq/ojT1r6tE6');

-- ===========================================
-- Table: students
-- ===========================================
CREATE TABLE IF NOT EXISTS students (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  phone VARCHAR(50),
  room_pref VARCHAR(50),
  status ENUM('pending','approved','rejected') DEFAULT 'pending',
  room_id INT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  room_no VARCHAR(20) DEFAULT NULL,
  fee_status ENUM('pending','paid') DEFAULT 'pending'
);

-- ===========================================
-- Table: rooms
-- ===========================================
CREATE TABLE IF NOT EXISTS rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_no VARCHAR(20) NOT NULL UNIQUE,
    room_type VARCHAR(50) DEFAULT 'Shared',
    capacity INT DEFAULT 2,
    fee DECIMAL(10,2) DEFAULT 3000.00,
    occupied INT DEFAULT 0
);
