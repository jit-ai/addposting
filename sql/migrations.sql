-- Create database if not exists
CREATE DATABASE IF NOT EXISTS add_posting CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE add_posting;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Postings table
CREATE TABLE IF NOT EXISTS postings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    category VARCHAR(100) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    state VARCHAR(100) NOT NULL,
    city VARCHAR(100) NOT NULL,
    contact VARCHAR(255) NOT NULL,
    images TEXT,
    status ENUM('active', 'inactive', 'pending') DEFAULT 'active',
    scheduled_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    image VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Add initial categories
INSERT IGNORE INTO categories (name, description) VALUES
('Electronics', 'All electronic items including phones, laptops, tablets, etc.'),
('Furniture', 'Furniture and home decor items'),
('Vehicles', 'Cars, bikes, and other vehicles'),
('Real Estate', 'Houses, apartments, land, and property'),
('Jobs', 'Job listings and career opportunities'),
('Services', 'Various services including tutoring, repair, etc.'),
('Clothing', 'Clothing, shoes, and accessories'),
('Books', 'Books, textbooks, and educational materials'),
('Sports', 'Sports equipment and accessories'),
('Other', 'Items that don''t fit into other categories');

-- Add admin user
INSERT IGNORE INTO users (name, email, password, role) VALUES
('Admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
-- Password: password123

-- Create indexes
CREATE INDEX idx_postings_user_id ON postings(user_id);
CREATE INDEX idx_postings_category ON postings(category);
CREATE INDEX idx_postings_status ON postings(status);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_users_status ON users(status);
