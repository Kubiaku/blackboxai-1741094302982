-- Create database
CREATE DATABASE IF NOT EXISTS sales_app;
USE sales_app;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    role ENUM('admin', 'sales_rep', 'manager') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Leads table
CREATE TABLE IF NOT EXISTS leads (
    id INT PRIMARY KEY AUTO_INCREMENT,
    company_name VARCHAR(100) NOT NULL,
    contact_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    status ENUM('new', 'contacted', 'qualified', 'lost') NOT NULL DEFAULT 'new',
    source VARCHAR(50),
    assigned_to INT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (assigned_to) REFERENCES users(id)
);

-- Opportunities table
CREATE TABLE IF NOT EXISTS opportunities (
    id INT PRIMARY KEY AUTO_INCREMENT,
    lead_id INT,
    name VARCHAR(100) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    stage ENUM('prospecting', 'qualification', 'needs_analysis', 'proposal', 'negotiation', 'closed_won', 'closed_lost') NOT NULL,
    probability INT,
    expected_close_date DATE,
    assigned_to INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (lead_id) REFERENCES leads(id),
    FOREIGN KEY (assigned_to) REFERENCES users(id)
);

-- Contacts table
CREATE TABLE IF NOT EXISTS contacts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    lead_id INT,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    position VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (lead_id) REFERENCES leads(id)
);

-- Activities table
CREATE TABLE IF NOT EXISTS activities (
    id INT PRIMARY KEY AUTO_INCREMENT,
    type ENUM('call', 'email', 'meeting', 'task') NOT NULL,
    subject VARCHAR(200) NOT NULL,
    description TEXT,
    due_date DATETIME,
    status ENUM('planned', 'completed', 'cancelled') NOT NULL DEFAULT 'planned',
    lead_id INT,
    opportunity_id INT,
    assigned_to INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (lead_id) REFERENCES leads(id),
    FOREIGN KEY (opportunity_id) REFERENCES opportunities(id),
    FOREIGN KEY (assigned_to) REFERENCES users(id)
);

-- Insert sample users with hashed passwords (password is 'password123' for all users)
INSERT INTO users (username, password, email, first_name, last_name, role)
VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@example.com', 'Admin', 'User', 'admin'),
('sales1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'sales1@example.com', 'John', 'Doe', 'sales_rep'),
('manager1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'manager1@example.com', 'Jane', 'Smith', 'manager');

-- Insert sample data for leads
INSERT INTO leads (company_name, contact_name, email, phone, status, source, assigned_to)
VALUES 
('Tech Solutions Inc.', 'John Smith', 'john@techsolutions.com', '555-0123', 'new', 'Website', 1),
('Global Innovations Ltd.', 'Sarah Johnson', 'sarah@globalinnovations.com', '555-0124', 'qualified', 'Referral', 1);

-- Insert sample opportunities
INSERT INTO opportunities (lead_id, name, amount, stage, probability, expected_close_date, assigned_to)
VALUES 
(1, 'Tech Solutions Software Deal', 50000.00, 'proposal', 70, '2024-04-30', 1),
(2, 'Global Innovations Partnership', 75000.00, 'qualification', 40, '2024-05-15', 1);

-- Insert sample contacts
INSERT INTO contacts (lead_id, first_name, last_name, email, phone, position)
VALUES 
(1, 'John', 'Smith', 'john@techsolutions.com', '555-0123', 'CTO'),
(2, 'Sarah', 'Johnson', 'sarah@globalinnovations.com', '555-0124', 'CEO');

-- Insert sample activities
INSERT INTO activities (type, subject, description, due_date, status, lead_id, assigned_to)
VALUES 
('call', 'Initial Contact', 'First call with Tech Solutions', '2024-03-10 10:00:00', 'planned', 1, 1),
('meeting', 'Proposal Review', 'Review proposal with Global Innovations', '2024-03-15 14:00:00', 'planned', 2, 1);
