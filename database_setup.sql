-- AgriFresh Connect MySQL Schema & Seeder
-- Final Year Project: Muhammad Aizat Izzuddin Bin Azmi (B23101069) - AIMST University
-- Partner: Famox Enterprise Sdn Bhd (Lunas, Kedah)

CREATE DATABASE IF NOT EXISTS `agrifresh_connect` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `agrifresh_connect`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `farmers`;
DROP TABLE IF EXISTS `buyers`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `roles`;
DROP TABLE IF EXISTS `price_index`;
DROP TABLE IF EXISTS `activity_logs`;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE `roles` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL UNIQUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE `users` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `role_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(255),
  `address` TEXT,
  `avatar` VARCHAR(500),
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE `farmers` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `farm_name` VARCHAR(255) NOT NULL,
  `kedah_district` VARCHAR(255) NOT NULL,
  `farm_location_details` TEXT,
  `farming_certification` VARCHAR(255),
  `cert_number` VARCHAR(255),
  `experience_years` INT DEFAULT 5,
  `rating` DECIMAL(3,2) DEFAULT 4.90,
  `famox_tier` VARCHAR(100) DEFAULT 'Gold Supplier Partner',
  `avatar` VARCHAR(500),
  `quote` TEXT,
  `is_approved` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE `categories` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `name_ms` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL UNIQUE,
  `icon` VARCHAR(50) DEFAULT '🌱',
  `description` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE `products` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `batch_id` VARCHAR(100) NOT NULL UNIQUE,
  `farmer_id` BIGINT UNSIGNED NOT NULL,
  `category_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `name_ms` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `description_ms` TEXT,
  `harvest_date` VARCHAR(100) NOT NULL,
  `expiry_date` DATE,
  `quantity_available` DECIMAL(10,2) NOT NULL DEFAULT 100.00,
  `minimum_order` DECIMAL(10,2) NOT NULL DEFAULT 1.00,
  `unit` VARCHAR(50) NOT NULL DEFAULT 'kg',
  `price_per_unit` DECIMAL(10,2) NOT NULL,
  `middleman_price` DECIMAL(10,2) NOT NULL,
  `storage_temp` VARCHAR(100) DEFAULT '4°C - 8°C Chilled',
  `farm_location` VARCHAR(255) DEFAULT 'Lunas, Kedah',
  `grade` VARCHAR(100) DEFAULT 'Grade A Premium',
  `pesticide_status` VARCHAR(255) DEFAULT 'MyGAP Lab Tested (0.00 ppm)',
  `image_path` VARCHAR(500),
  `is_spotlight` TINYINT(1) DEFAULT 0,
  `spotlight_headline` VARCHAR(255),
  `spotlight_subtitle` VARCHAR(255),
  `tag` VARCHAR(100) DEFAULT 'FRESH HARVEST',
  `approval_status` ENUM('pending','approved','rejected') DEFAULT 'approved',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE `orders` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` BIGINT UNSIGNED NULL,
  `order_number` VARCHAR(255) NOT NULL UNIQUE,
  `buyer_name` VARCHAR(255) NOT NULL,
  `buyer_role` VARCHAR(255) DEFAULT 'Direct Consumer',
  `phone` VARCHAR(100),
  `total_amount` DECIMAL(10,2) NOT NULL,
  `status` VARCHAR(100) DEFAULT 'Order Placed & QR Tagged',
  `payment_method` VARCHAR(255) DEFAULT 'FPX Online Banking',
  `shipping_address` TEXT NOT NULL,
  `notes` TEXT,
  `batch_code` VARCHAR(100),
  `driver` VARCHAR(255) DEFAULT 'Famox Logistics Lunas (Van KDH 4410)',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `orders_user_id_idx` (`user_id`)
) ENGINE=InnoDB;

CREATE TABLE `order_items` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_id` BIGINT UNSIGNED NOT NULL,
  `product_name` VARCHAR(255) NOT NULL,
  `quantity` DECIMAL(10,2) NOT NULL,
  `unit` VARCHAR(50) DEFAULT 'kg',
  `price` DECIMAL(10,2) NOT NULL,
  `subtotal` DECIMAL(10,2) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE `price_index` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `crop_name` VARCHAR(255) NOT NULL,
  `direct_price` DECIMAL(10,2) NOT NULL,
  `middleman_price` DECIMAL(10,2) NOT NULL,
  `farmer_gain` VARCHAR(50) NOT NULL,
  `trend` VARCHAR(100) NOT NULL DEFAULT 'Stable',
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE `activity_logs` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_name` VARCHAR(255) DEFAULT 'System',
  `action` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `ip_address` VARCHAR(100) DEFAULT '127.0.0.1',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
