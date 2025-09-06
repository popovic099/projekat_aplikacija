-- Baze podataka
CREATE DATABASE IF NOT EXISTS finance_tracker CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE finance_tracker;

-- Tabela korisnika
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB;

-- Tabela računa
CREATE TABLE accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    starting_balance DECIMAL(12,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id)
) ENGINE=InnoDB;

-- Tabela kategorija
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    type ENUM('prihod', 'rashod') NOT NULL,
    color VARCHAR(7) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_category (user_id, name, type),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_type (user_id, type)
) ENGINE=InnoDB;

-- Tabela transakcija
CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    account_id INT NOT NULL,
    category_id INT NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    type ENUM('prihod', 'rashod') NOT NULL,
    occurred_on DATE NOT NULL,
    note TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT,
    INDEX idx_occurred (occurred_on),
    INDEX idx_user_date (user_id, occurred_on)
) ENGINE=InnoDB;

-- Tabela budžeta
CREATE TABLE budgets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    period ENUM('mesec', 'kvartal', 'godina') NOT NULL,
    period_start DATE NOT NULL,
    limit_amount DECIMAL(12,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_budget (user_id, category_id, period, period_start),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    INDEX idx_user_period (user_id, period_start)
) ENGINE=InnoDB;

-- Tabela ciljeva
CREATE TABLE goals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    target_amount DECIMAL(12,2) NOT NULL,
    current_amount DECIMAL(12,2) DEFAULT 0.00,
    deadline DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id)
) ENGINE=InnoDB;

-- Seed podaci
INSERT INTO users (name, email, password_hash, role) VALUES 
('Admin Korisnik', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Marko Marković', 'marko@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');

INSERT INTO accounts (user_id, name, starting_balance) VALUES
(2, 'Glavni račun', 50000.00),
(2, 'Štednja', 20000.00);

INSERT INTO categories (user_id, name, type, color) VALUES
(2, 'Plata', 'prihod', '#28a745'),
(2, 'Freelance', 'prihod', '#17a2b8'),
(2, 'Hrana', 'rashod', '#dc3545'),
(2, 'Transport', 'rashod', '#ffc107'),
(2, 'Računi', 'rashod', '#6c757d'),
(2, 'Zabava', 'rashod', '#e83e8c');

INSERT INTO transactions (user_id, account_id, category_id, amount, type, occurred_on, note) VALUES
(2, 1, 1, 75000.00, 'prihod', '2025-09-01', 'Mesečna plata'),
(2, 1, 3, 8500.00, 'rashod', '2025-09-02', 'Nedeljni šoping'),
(2, 1, 4, 2000.00, 'rashod', '2025-09-03', 'Gorivo'),
(2, 1, 5, 12000.00, 'rashod', '2025-09-05', 'Struja, voda, internet'),
(2, 1, 2, 15000.00, 'prihod', '2025-09-06', 'Web projekat');

INSERT INTO budgets (user_id, category_id, period, period_start, limit_amount) VALUES
(2, 3, 'mesec', '2025-09-01', 15000.00),
(2, 6, 'mesec', '2025-09-01', 10000.00);

INSERT INTO goals (user_id, title, target_amount, current_amount, deadline) VALUES
(2, 'Letovanje u Grčkoj', 100000.00, 35000.00, '2026-06-01'),
(2, 'Nov laptop', 150000.00, 20000.00, '2025-12-31');