-- ============================================================
-- TrafficLens AI — Database Schema
-- Supabase PostgreSQL
-- Version: 1.0
-- ============================================================

-- Enable UUID generation
CREATE EXTENSION IF NOT EXISTS "pgcrypto";

-- ============================================================
-- 1. ADMINS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS admins (
    admin_id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT NOW()
);

-- ============================================================
-- 2. DRIVERS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS drivers (
    driver_id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    full_name VARCHAR(100) NOT NULL,
    license_number VARCHAR(30) UNIQUE NOT NULL,
    phone VARCHAR(15) NOT NULL,
    email VARCHAR(100) UNIQUE,
    address TEXT,
    created_at TIMESTAMP DEFAULT NOW()
);

-- ============================================================
-- 3. VEHICLES TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS vehicles (
    vehicle_id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    driver_id UUID NOT NULL,
    vehicle_number VARCHAR(20) UNIQUE NOT NULL,
    vehicle_type VARCHAR(30) NOT NULL,
    manufacturer VARCHAR(50) NOT NULL,
    model VARCHAR(50) NOT NULL,
    color VARCHAR(30),
    registration_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT NOW(),
    CONSTRAINT fk_vehicle_driver
        FOREIGN KEY (driver_id)
        REFERENCES drivers(driver_id)
        ON DELETE RESTRICT
);

-- ============================================================
-- 4. VIOLATION RULES TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS violation_rules (
    rule_id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    violation_type VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    fine_amount DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT NOW(),
    CONSTRAINT chk_fine_amount_positive
        CHECK (fine_amount >= 0)
);

-- ============================================================
-- 5. VIOLATIONS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS violations (
    violation_id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    driver_id UUID NOT NULL,
    vehicle_id UUID NOT NULL,
    rule_id UUID NOT NULL,
    location VARCHAR(150) NOT NULL,
    officer_name VARCHAR(100) NOT NULL,
    violation_date DATE NOT NULL,
    violation_time TIME NOT NULL,
    evidence_image TEXT,
    payment_status VARCHAR(20) DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT NOW(),
    CONSTRAINT fk_violation_driver
        FOREIGN KEY (driver_id)
        REFERENCES drivers(driver_id)
        ON DELETE RESTRICT,
    CONSTRAINT fk_violation_vehicle
        FOREIGN KEY (vehicle_id)
        REFERENCES vehicles(vehicle_id)
        ON DELETE RESTRICT,
    CONSTRAINT fk_violation_rule
        FOREIGN KEY (rule_id)
        REFERENCES violation_rules(rule_id)
        ON DELETE RESTRICT,
    CONSTRAINT chk_payment_status
        CHECK (payment_status IN ('Pending', 'Paid'))
);

-- ============================================================
-- 6. PAYMENTS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS payments (
    payment_id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    violation_id UUID NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(30) NOT NULL,
    transaction_id VARCHAR(100) UNIQUE NOT NULL,
    payment_date DATE NOT NULL,
    payment_status VARCHAR(20) DEFAULT 'Paid',
    created_at TIMESTAMP DEFAULT NOW(),
    CONSTRAINT fk_payment_violation
        FOREIGN KEY (violation_id)
        REFERENCES violations(violation_id)
        ON DELETE RESTRICT,
    CONSTRAINT chk_payment_amount_positive
        CHECK (amount >= 0),
    CONSTRAINT chk_payment_status_valid
        CHECK (payment_status IN ('Pending', 'Paid'))
);

-- ============================================================
-- INDEXES
-- ============================================================

-- Drivers
CREATE INDEX IF NOT EXISTS idx_drivers_license ON drivers(license_number);
CREATE INDEX IF NOT EXISTS idx_drivers_email ON drivers(email);

-- Vehicles
CREATE INDEX IF NOT EXISTS idx_vehicles_number ON vehicles(vehicle_number);
CREATE INDEX IF NOT EXISTS idx_vehicles_driver ON vehicles(driver_id);

-- Violation Rules
CREATE INDEX IF NOT EXISTS idx_rules_type ON violation_rules(violation_type);

-- Violations
CREATE INDEX IF NOT EXISTS idx_violations_driver ON violations(driver_id);
CREATE INDEX IF NOT EXISTS idx_violations_vehicle ON violations(vehicle_id);
CREATE INDEX IF NOT EXISTS idx_violations_rule ON violations(rule_id);
CREATE INDEX IF NOT EXISTS idx_violations_status ON violations(payment_status);
CREATE INDEX IF NOT EXISTS idx_violations_date ON violations(violation_date);

-- Payments
CREATE INDEX IF NOT EXISTS idx_payments_violation ON payments(violation_id);
CREATE INDEX IF NOT EXISTS idx_payments_transaction ON payments(transaction_id);
CREATE INDEX IF NOT EXISTS idx_payments_date ON payments(payment_date);

-- ============================================================
-- SEED DATA
-- ============================================================

-- Default Admin (password: admin123)
-- Hash generated with password_hash('admin123', PASSWORD_BCRYPT) in PHP
INSERT INTO admins (username, email, password_hash)
VALUES (
    'admin',
    'admin@trafficlens.ai',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
) ON CONFLICT (username) DO NOTHING;

-- Sample Violation Rules (Indian traffic fines)
INSERT INTO violation_rules (violation_type, description, fine_amount) VALUES
    ('Helmet Not Worn', 'Riding a two-wheeler without wearing a helmet', 500.00),
    ('Signal Jump', 'Crossing a red traffic signal', 1000.00),
    ('Overspeeding', 'Exceeding the prescribed speed limit', 1500.00),
    ('Driving Without License', 'Operating a vehicle without a valid driving license', 5000.00),
    ('Driving Without Seatbelt', 'Driving a four-wheeler without wearing a seatbelt', 1000.00),
    ('Using Mobile While Driving', 'Using a mobile phone while operating a vehicle', 1500.00),
    ('Wrong Side Driving', 'Driving on the wrong side of the road', 2000.00),
    ('Drunken Driving', 'Driving under the influence of alcohol', 10000.00),
    ('No Insurance', 'Driving without valid vehicle insurance', 2000.00),
    ('No Pollution Certificate', 'Driving without a valid pollution under control certificate', 1000.00),
    ('Triple Riding', 'More than two persons on a two-wheeler', 1000.00),
    ('Parking Violation', 'Parking in a no-parking zone or restricted area', 500.00)
ON CONFLICT (violation_type) DO NOTHING;
