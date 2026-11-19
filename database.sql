-- ============================================================
--  Ugay's Elegant Stitches — MySQL Database Schema
--  Run this file once to create all tables.
--  NOTE: No password hash is stored here.
--        Run setup.php after import to create the admin account.
-- ============================================================

SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;

-- ------------------------------------------------------------
-- Database (create if it doesn't exist, then use it)
-- ------------------------------------------------------------
CREATE DATABASE IF NOT EXISTS ugays_elegant_stitches
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE ugays_elegant_stitches;

-- ============================================================
-- TABLE: users
--   Stores admin / staff accounts.
--   Passwords are hashed via PHP password_hash() — see setup.php.
-- ============================================================
CREATE TABLE IF NOT EXISTS `users` (
  `user_id`    INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `username`   VARCHAR(60)      NOT NULL UNIQUE,
  `password`   VARCHAR(255)     NOT NULL,
  `full_name`  VARCHAR(120)     NOT NULL,
  `role`       ENUM('admin','staff') NOT NULL DEFAULT 'staff',
  `created_at` DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: customers
-- ============================================================
CREATE TABLE IF NOT EXISTS `customers` (
  `customer_id`    INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `first_name`     VARCHAR(80)   NOT NULL,
  `last_name`      VARCHAR(80)   NOT NULL,
  `contact_number` VARCHAR(30)   DEFAULT NULL,
  `email`          VARCHAR(120)  DEFAULT NULL,
  `address`        TEXT          DEFAULT NULL,
  `created_at`     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: inventory
--   Tracks fabric, supplies, finished garments, etc.
-- ============================================================
CREATE TABLE IF NOT EXISTS `inventory` (
  `item_id`      INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  `item_name`    VARCHAR(150)   NOT NULL,
  `category`     VARCHAR(80)    DEFAULT NULL,
  `description`  TEXT           DEFAULT NULL,
  `quantity`     INT            NOT NULL DEFAULT 0,
  `unit`         VARCHAR(30)    DEFAULT 'pcs',
  `unit_price`   DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
  `reorder_level` INT           NOT NULL DEFAULT 5,
  `created_at`   DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: orders
--   Tailoring / dressmaking orders.
-- ============================================================
CREATE TABLE IF NOT EXISTS `orders` (
  `order_id`      INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  `customer_id`   INT UNSIGNED   NOT NULL,
  `description`   TEXT           DEFAULT NULL,
  `measurements`  TEXT           DEFAULT NULL,
  `material`      VARCHAR(150)   DEFAULT NULL,
  `amount`        DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
  `status`        ENUM('Pending','In Progress','For Fitting','Completed','Cancelled')
                                 NOT NULL DEFAULT 'Pending',
  `due_date`      DATE           DEFAULT NULL,
  `notes`         TEXT           DEFAULT NULL,
  `created_at`    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`order_id`),
  CONSTRAINT `fk_orders_customer`
    FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: rentals
--   Gown / barong / costume rental transactions.
-- ============================================================
CREATE TABLE IF NOT EXISTS `rentals` (
  `rental_id`    INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  `customer_id`  INT UNSIGNED   NOT NULL,
  `item_id`      INT UNSIGNED   DEFAULT NULL,
  `item_name`    VARCHAR(150)   NOT NULL,
  `rental_date`  DATE           NOT NULL,
  `return_date`  DATE           DEFAULT NULL,
  `amount`       DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
  `deposit`      DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
  `status`       ENUM('Reserved','Rented','Returned','Cancelled')
                                NOT NULL DEFAULT 'Reserved',
  `notes`        TEXT           DEFAULT NULL,
  `created_at`   DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`rental_id`),
  CONSTRAINT `fk_rentals_customer`
    FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_rentals_item`
    FOREIGN KEY (`item_id`) REFERENCES `inventory` (`item_id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: appointments
-- ============================================================
CREATE TABLE IF NOT EXISTS `appointments` (
  `appointment_id`   INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_id`      INT UNSIGNED NOT NULL,
  `appointment_date` DATE         NOT NULL,
  `appointment_time` TIME         NOT NULL,
  `purpose`          VARCHAR(200) DEFAULT NULL,
  `status`           ENUM('Scheduled','Completed','Cancelled','No Show')
                                  NOT NULL DEFAULT 'Scheduled',
  `notes`            TEXT         DEFAULT NULL,
  `created_at`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`appointment_id`),
  CONSTRAINT `fk_appointments_customer`
    FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: payments
-- ============================================================
CREATE TABLE IF NOT EXISTS `payments` (
  `payment_id`     INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  `order_id`       INT UNSIGNED   DEFAULT NULL,
  `rental_id`      INT UNSIGNED   DEFAULT NULL,
  `customer_id`    INT UNSIGNED   NOT NULL,
  `amount`         DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
  `payment_method` ENUM('Cash','GCash','Bank Transfer','Card','Other')
                                  NOT NULL DEFAULT 'Cash',
  `payment_date`   DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reference_no`   VARCHAR(100)   DEFAULT NULL,
  `notes`          TEXT           DEFAULT NULL,
  `created_at`     DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`payment_id`),
  CONSTRAINT `fk_payments_customer`
    FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_payments_order`
    FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_payments_rental`
    FOREIGN KEY (`rental_id`) REFERENCES `rentals` (`rental_id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET foreign_key_checks = 1;

-- ============================================================
-- Setup reminder
-- ============================================================
-- After importing this file:
--   1. Open your browser and navigate to setup.php
--   2. It will create the admin account with a secure bcrypt hash.
--   3. DELETE setup.php immediately after the admin account is created.
-- ============================================================
