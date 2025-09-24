-- SolaFriq Laravel Database Setup
-- Run this SQL script in your MySQL database named 'sola'

USE sola;

-- Create users table
CREATE TABLE users (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NULL,
    email VARCHAR(255) NULL UNIQUE,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NULL,
    image VARCHAR(255) NULL,
    role ENUM('ADMIN', 'CLIENT') NOT NULL DEFAULT 'CLIENT',
    phone VARCHAR(255) NULL,
    address TEXT NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create solar_systems table
CREATE TABLE solar_systems (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    short_description VARCHAR(255) NOT NULL,
    capacity VARCHAR(255) NOT NULL,
    price DECIMAL(12, 2) NOT NULL,
    original_price DECIMAL(12, 2) NULL,
    installment_price DECIMAL(12, 2) NULL,
    installment_months INT NULL,
    image_url VARCHAR(255) NULL,
    gallery_images JSON NULL,
    use_case VARCHAR(255) NULL,
    gradient_colors VARCHAR(255) NULL,
    is_popular BOOLEAN NOT NULL DEFAULT FALSE,
    is_featured BOOLEAN NOT NULL DEFAULT FALSE,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create solar_system_features table
CREATE TABLE solar_system_features (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    feature_name VARCHAR(255) NOT NULL,
    feature_value VARCHAR(255) NULL,
    sort_order INT NOT NULL DEFAULT 0,
    solar_system_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (solar_system_id) REFERENCES solar_systems(id) ON DELETE CASCADE
);

-- Create solar_system_products table
CREATE TABLE solar_system_products (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(255) NOT NULL,
    product_description TEXT NULL,
    quantity INT NOT NULL DEFAULT 1,
    unit_price DECIMAL(12, 2) NULL,
    sort_order INT NOT NULL DEFAULT 0,
    solar_system_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (solar_system_id) REFERENCES solar_systems(id) ON DELETE CASCADE
);

-- Create solar_system_specifications table
CREATE TABLE solar_system_specifications (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    spec_name VARCHAR(255) NOT NULL,
    spec_value VARCHAR(255) NOT NULL,
    spec_category VARCHAR(255) NULL,
    sort_order INT NOT NULL DEFAULT 0,
    solar_system_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (solar_system_id) REFERENCES solar_systems(id) ON DELETE CASCADE
);

-- Create orders table
CREATE TABLE orders (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    total_amount DECIMAL(12, 2) NOT NULL,
    status ENUM('PENDING', 'PROCESSING', 'SHIPPED', 'DELIVERED', 'CANCELLED', 'REFUNDED') NOT NULL DEFAULT 'PENDING',
    payment_status ENUM('PENDING', 'PAID', 'FAILED', 'OVERDUE') NOT NULL DEFAULT 'PENDING',
    payment_method VARCHAR(255) NULL,
    tracking_number VARCHAR(255) NULL,
    notes TEXT NULL,
    user_id BIGINT UNSIGNED NULL,
    customer_name VARCHAR(255) NOT NULL,
    customer_email VARCHAR(255) NOT NULL,
    customer_phone VARCHAR(255) NULL,
    customer_address TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Create order_items table
CREATE TABLE order_items (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    price DECIMAL(12, 2) NOT NULL,
    quantity INT NOT NULL,
    image_url VARCHAR(255) NULL,
    type ENUM('solar_system', 'product', 'custom_package', 'custom_system') NOT NULL,
    order_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- Create invoices table
CREATE TABLE invoices (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    subtotal DECIMAL(12, 2) NOT NULL,
    tax DECIMAL(12, 2) NOT NULL,
    total DECIMAL(12, 2) NOT NULL,
    payment_status ENUM('PENDING', 'PAID', 'FAILED', 'OVERDUE') NOT NULL DEFAULT 'PENDING',
    order_id BIGINT UNSIGNED NOT NULL UNIQUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- Create installment_plans table
CREATE TABLE installment_plans (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    total_amount DECIMAL(12, 2) NOT NULL,
    down_payment DECIMAL(12, 2) NOT NULL,
    monthly_payment DECIMAL(12, 2) NOT NULL,
    payment_duration_months INT NOT NULL,
    status ENUM('PENDING', 'ACTIVE', 'COMPLETED', 'DEFAULTED') NOT NULL DEFAULT 'PENDING',
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    solar_system_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (solar_system_id) REFERENCES solar_systems(id) ON DELETE RESTRICT
);

-- Create installment_payments table
CREATE TABLE installment_payments (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    due_date DATE NOT NULL,
    amount DECIMAL(12, 2) NOT NULL,
    status ENUM('PENDING', 'PAID', 'FAILED', 'OVERDUE') NOT NULL DEFAULT 'PENDING',
    paid_at TIMESTAMP NULL,
    installment_plan_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (installment_plan_id) REFERENCES installment_plans(id) ON DELETE CASCADE
);

-- Create warranties table
CREATE TABLE warranties (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(255) NOT NULL,
    serial_number VARCHAR(255) NOT NULL UNIQUE,
    warranty_period_months INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('ACTIVE', 'EXPIRED', 'CLAIMED') NOT NULL DEFAULT 'ACTIVE',
    order_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create warranty_claims table
CREATE TABLE warranty_claims (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    claim_description TEXT NOT NULL,
    status ENUM('PENDING', 'APPROVED', 'REJECTED') NOT NULL DEFAULT 'PENDING',
    resolution_details TEXT NULL,
    warranty_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (warranty_id) REFERENCES warranties(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create system_logs table
CREATE TABLE system_logs (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    category VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    level ENUM('INFO', 'WARN', 'ERROR') NOT NULL,
    metadata JSON NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample data
-- Admin user (password: admin123)
INSERT INTO users (name, email, password, role, phone, address, email_verified_at) VALUES
('Admin User', 'admin@solafriq.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ADMIN', '+234-XXX-XXX-XXXX', 'Lagos, Nigeria', NOW()),
('Test Client', 'client@solafriq.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'CLIENT', '+234-802-XXX-XXXX', 'Victoria Island, Lagos, Nigeria', NOW());

-- Sample solar systems
INSERT INTO solar_systems (name, description, short_description, capacity, price, original_price, installment_price, installment_months, image_url, use_case, gradient_colors, is_popular, is_featured, is_active, sort_order) VALUES
('SolaFriq Home Starter 1kW', 'Complete solar energy solution perfect for residential applications. This system includes high-quality components with professional installation and 2-year warranty.', 'Complete 1kW solar system with battery backup and professional installation.', '1kW', 450000.00, 540000.00, 517500.00, 12, 'https://images.unsplash.com/photo-1509391366360-2e959784a276?w=800&h=600&fit=crop', 'Perfect for small homes', 'from-orange-400 to-yellow-500', FALSE, TRUE, TRUE, 0),
('SolaFriq Home Essential 3kW', 'Complete solar energy solution perfect for medium homes and small offices. This system includes high-quality components with professional installation and 2-year warranty.', 'Complete 3kW solar system with battery backup and professional installation.', '3kW', 980000.00, 1176000.00, 1127000.00, 12, 'https://images.unsplash.com/photo-1509391366360-2e959784a276?w=800&h=600&fit=crop', 'Perfect for medium homes', 'from-orange-400 to-yellow-500', TRUE, TRUE, TRUE, 1),
('SolaFriq Commercial Pro 10kW', 'Complete solar energy solution perfect for large properties and commercial applications. This system includes high-quality components with professional installation and 2-year warranty.', 'Complete 10kW solar system with battery backup and professional installation.', '10kW', 2850000.00, 3420000.00, 3277500.00, 12, 'https://images.unsplash.com/photo-1509391366360-2e959784a276?w=800&h=600&fit=crop', 'Perfect for large properties', 'from-orange-400 to-yellow-500', FALSE, FALSE, TRUE, 2);

-- Sample features for 1kW system
INSERT INTO solar_system_features (solar_system_id, feature_name, feature_value, sort_order) VALUES
(1, 'Solar Panels', '2 x 500W Monocrystalline', 0),
(1, 'Battery', '100Ah Lithium Battery', 1),
(1, 'Inverter', '1000W Pure Sine Wave', 2),
(1, 'Warranty', '2 Years Full Warranty', 3);

-- Sample products for 1kW system
INSERT INTO solar_system_products (solar_system_id, product_name, product_description, quantity, unit_price, sort_order) VALUES
(1, 'Monocrystalline Solar Panel', '500W High Efficiency Panel', 2, 70000.00, 0),
(1, 'Lithium Battery', '100Ah Deep Cycle Battery', 1, 180000.00, 1),
(1, 'Pure Sine Wave Inverter', '1000W Inverter', 1, 85000.00, 2),
(1, 'Charge Controller', '30A MPPT Controller', 1, 35000.00, 3),
(1, 'Installation Kit', 'Mounting, Wiring & Accessories', 1, 80000.00, 4);

-- Sample specifications for 1kW system
INSERT INTO solar_system_specifications (solar_system_id, spec_name, spec_value, spec_category, sort_order) VALUES
(1, 'System Voltage', '12V DC', 'Electrical', 0),
(1, 'Daily Energy Output', '4-6 kWh', 'Performance', 1),
(1, 'Backup Time', '6-8 hours', 'Performance', 2),
(1, 'Installation Time', '1-2 days', 'Installation', 3);

-- Sample features for 3kW system
INSERT INTO solar_system_features (solar_system_id, feature_name, feature_value, sort_order) VALUES
(2, 'Solar Panels', '6 x 500W Monocrystalline', 0),
(2, 'Battery Bank', '2 x 200Ah Lithium Batteries', 1),
(2, 'Inverter', '3000W Pure Sine Wave', 2),
(2, 'Smart Monitoring', 'Mobile App Integration', 3);

SELECT 'Database setup completed successfully!' as status;