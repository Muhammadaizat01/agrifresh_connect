-- AgriFresh Connect PostgreSQL Schema for Neon Console
-- Final Year Project: Muhammad Aizat Izzuddin Bin Azmi (B23101069) - AIMST University
-- Compatible with PostgreSQL 15+ / Neon.tech

-- 1. Roles table
CREATE TABLE IF NOT EXISTS roles (
  id BIGSERIAL PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  slug VARCHAR(255) NOT NULL UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Users table
CREATE TABLE IF NOT EXISTS users (
  id BIGSERIAL PRIMARY KEY,
  role_id BIGINT NOT NULL,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  phone VARCHAR(255),
  address TEXT,
  avatar VARCHAR(500),
  is_active SMALLINT DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Farmers table
CREATE TABLE IF NOT EXISTS farmers (
  id BIGSERIAL PRIMARY KEY,
  user_id BIGINT NOT NULL,
  farm_name VARCHAR(255) NOT NULL,
  kedah_district VARCHAR(255) NOT NULL,
  farm_location_details TEXT,
  farming_certification VARCHAR(255),
  cert_number VARCHAR(255),
  experience_years INT DEFAULT 5,
  rating DECIMAL(3,2) DEFAULT 4.90,
  famox_tier VARCHAR(100) DEFAULT 'Gold Supplier Partner',
  avatar VARCHAR(500),
  quote TEXT,
  is_approved SMALLINT DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4. Buyers table
CREATE TABLE IF NOT EXISTS buyers (
  id BIGSERIAL PRIMARY KEY,
  user_id BIGINT NOT NULL,
  company_name VARCHAR(255),
  buyer_type VARCHAR(100) DEFAULT 'Direct Consumer',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 5. Categories table
CREATE TABLE IF NOT EXISTS categories (
  id BIGSERIAL PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  name_ms VARCHAR(255) NOT NULL,
  slug VARCHAR(255) NOT NULL UNIQUE,
  icon VARCHAR(50) DEFAULT '🌱',
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 6. Products table
CREATE TABLE IF NOT EXISTS products (
  id BIGSERIAL PRIMARY KEY,
  batch_id VARCHAR(100) NOT NULL UNIQUE,
  farmer_id BIGINT NOT NULL,
  category_id BIGINT NOT NULL,
  name VARCHAR(255) NOT NULL,
  name_ms VARCHAR(255) NOT NULL,
  slug VARCHAR(255) NOT NULL,
  description TEXT,
  description_ms TEXT,
  harvest_date VARCHAR(100) NOT NULL,
  expiry_date DATE,
  quantity_available DECIMAL(10,2) NOT NULL DEFAULT 100.00,
  minimum_order DECIMAL(10,2) NOT NULL DEFAULT 1.00,
  unit VARCHAR(50) NOT NULL DEFAULT 'kg',
  price_per_unit DECIMAL(10,2) NOT NULL,
  middleman_price DECIMAL(10,2) NOT NULL,
  storage_temp VARCHAR(100) DEFAULT '4°C - 8°C Chilled',
  farm_location VARCHAR(255) DEFAULT 'Lunas, Kedah',
  grade VARCHAR(100) DEFAULT 'Grade A Premium',
  pesticide_status VARCHAR(255) DEFAULT 'MyGAP Lab Tested (0.00 ppm)',
  image_path VARCHAR(500),
  is_spotlight SMALLINT DEFAULT 0,
  spotlight_headline VARCHAR(255),
  spotlight_subtitle VARCHAR(255),
  tag VARCHAR(100) DEFAULT 'FRESH HARVEST',
  approval_status VARCHAR(50) DEFAULT 'approved',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 7. Orders table
CREATE TABLE IF NOT EXISTS orders (
  id BIGSERIAL PRIMARY KEY,
  user_id BIGINT NULL,
  order_number VARCHAR(255) NOT NULL UNIQUE,
  buyer_name VARCHAR(255) NOT NULL,
  buyer_role VARCHAR(255) DEFAULT 'Direct Consumer',
  phone VARCHAR(100),
  total_amount DECIMAL(10,2) NOT NULL,
  status VARCHAR(100) DEFAULT 'Order Placed & QR Tagged',
  payment_method VARCHAR(255) DEFAULT 'FPX Online Banking',
  shipping_address TEXT NOT NULL,
  notes TEXT,
  batch_code VARCHAR(100),
  driver VARCHAR(255) DEFAULT 'Famox Logistics Lunas (Van KDH 4410)',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS orders_user_id_idx ON orders (user_id);

-- 8. Order items table
CREATE TABLE IF NOT EXISTS order_items (
  id BIGSERIAL PRIMARY KEY,
  order_id BIGINT NOT NULL,
  product_name VARCHAR(255) NOT NULL,
  quantity DECIMAL(10,2) NOT NULL,
  unit VARCHAR(50) DEFAULT 'kg',
  price DECIMAL(10,2) NOT NULL,
  subtotal DECIMAL(10,2) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 9. Price index table
CREATE TABLE IF NOT EXISTS price_index (
  id BIGSERIAL PRIMARY KEY,
  crop_name VARCHAR(255) NOT NULL,
  direct_price DECIMAL(10,2) NOT NULL,
  middleman_price DECIMAL(10,2) NOT NULL,
  farmer_gain VARCHAR(50) NOT NULL,
  trend VARCHAR(100) NOT NULL DEFAULT 'Stable',
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 10. Activity logs table
CREATE TABLE IF NOT EXISTS activity_logs (
  id BIGSERIAL PRIMARY KEY,
  user_name VARCHAR(255) DEFAULT 'System',
  action VARCHAR(255) NOT NULL,
  description TEXT,
  ip_address VARCHAR(100) DEFAULT '127.0.0.1',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Default Initial Seed Data
INSERT INTO roles (name, slug) VALUES 
('Admin', 'admin'),
('Farmer', 'farmer'),
('Buyer', 'buyer')
ON CONFLICT (slug) DO NOTHING;

INSERT INTO categories (name, name_ms, slug, icon, description) VALUES
('Vegetables', 'Sayur-sayuran', 'vegetables', '🥬', 'Fresh greens and leafy harvest'),
('Fruits', 'Buah-buahan', 'fruits', '🍉', 'Sweet local tropical fruits'),
('Herbs & Spices', 'Herba & Rempah', 'herbs-spices', '🌿', 'Aromatic spices and fresh roots'),
('Tubers & Roots', 'Ubi & Akar', 'tubers-roots', '🥔', 'Organic root vegetables and tubers')
ON CONFLICT (slug) DO NOTHING;
