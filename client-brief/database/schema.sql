-- ============================================================================
-- ArchonDigital Client Brief — Database Schema (MySQL 8.0+ / MariaDB 10.4+)
-- Gunakan database terpisah khusus untuk aplikasi brief.archondigital.web.id
-- ============================================================================

SET NAMES utf8mb4;
SET time_zone = '+07:00';

CREATE TABLE IF NOT EXISTS `admins` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(160) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_login_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_admins_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `client_briefs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `reference_code` VARCHAR(32) NOT NULL,
  `full_name` VARCHAR(120) NOT NULL,
  `business_name` VARCHAR(150) NOT NULL,
  `whatsapp` VARCHAR(24) NOT NULL,
  `email` VARCHAR(160) NULL DEFAULT NULL,
  `website_type` ENUM(
    'Website UMKM',
    'Company Profile',
    'E-Commerce / Toko Online',
    'Custom Website Development'
  ) NOT NULL,
  `business_description` TEXT NOT NULL,
  `website_goals` TEXT NOT NULL,
  `required_pages` TEXT NOT NULL,
  `required_features` TEXT NOT NULL,
  `reference_url` VARCHAR(255) NULL DEFAULT NULL,
  `budget_range` ENUM(
    'Di bawah Rp500.000',
    'Rp500.000–Rp1.500.000',
    'Rp1.500.000–Rp3.500.000',
    'Di atas Rp3.500.000',
    'Masih ingin berkonsultasi'
  ) NOT NULL,
  `target_timeline` ENUM(
    'Segera',
    'Dalam 1 bulan',
    'Dalam 1–3 bulan',
    'Belum ditentukan'
  ) NOT NULL,
  `additional_notes` TEXT NULL DEFAULT NULL,
  `privacy_consent` TINYINT(1) NOT NULL DEFAULT 1,
  `status` ENUM(
    'new',
    'reviewing',
    'contacted',
    'completed',
    'archived'
  ) NOT NULL DEFAULT 'new',
  `admin_notes` TEXT NULL DEFAULT NULL,
  `reviewed_by_admin_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_client_briefs_reference_code` (`reference_code`),
  KEY `idx_client_briefs_status` (`status`),
  KEY `idx_client_briefs_created_at` (`created_at`),
  KEY `idx_client_briefs_whatsapp` (`whatsapp`),
  KEY `idx_client_briefs_full_name` (`full_name`),
  KEY `idx_client_briefs_business_name` (`business_name`),
  KEY `idx_client_briefs_reviewed_by` (`reviewed_by_admin_id`),
  CONSTRAINT `fk_client_briefs_reviewed_by_admin`
    FOREIGN KEY (`reviewed_by_admin_id`) REFERENCES `admins` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `rate_limits` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `bucket_key` VARCHAR(128) NOT NULL,
  `attempts` INT UNSIGNED NOT NULL DEFAULT 0,
  `first_attempt_at` INT UNSIGNED NOT NULL,
  `locked_until` INT UNSIGNED NULL DEFAULT NULL,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_rate_limits_bucket_key` (`bucket_key`),
  KEY `idx_rate_limits_locked_until` (`locked_until`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
