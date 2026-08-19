# Complete MySQL Database Script for iTax

This file contains the complete, copy-pasteable MySQL script to set up your entire database structure and initial seed data in **Hostinger (phpMyAdmin / Remote MySQL)**.

---

## Instructions to Run in Hostinger (phpMyAdmin)

1. Open your **Hostinger hPanel**.
2. Go to **Databases** > **MySQL Databases** and open **phpMyAdmin** for your database (`tu583652021_tayyab`).
3. Click on the **SQL** tab in the top navigation bar.
4. Copy the entire SQL script below, paste it into the query box, and click **Go** (bottom right).

---

## Full SQL Script

```sql
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------
-- 1. Table structure for `migrations`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_07_26_114816_create_employees_table', 1),
(5, '2026_07_26_114816_create_payslips_table', 1),
(6, '2026_07_26_114817_create_tax_liabilities_table', 1),
(7, '2026_08_03_100000_create_tax_settings_table', 1);

-- --------------------------------------------------------
-- 2. Table structure for `users`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 3. Table structure for `password_reset_tokens`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 4. Table structure for `sessions`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 5. Table structure for `cache`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 6. Table structure for `cache_locks`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 7. Table structure for `jobs`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 8. Table structure for `job_batches`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 9. Table structure for `failed_jobs`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 10. Table structure for `employees`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `employees`;
CREATE TABLE `employees` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pps_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `department` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `job_title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `hourly_rate` decimal(8,2) NOT NULL DEFAULT '0.00',
  `salary` decimal(10,2) NOT NULL DEFAULT '0.00',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `tax_credit` decimal(8,2) NOT NULL DEFAULT '3750.00',
  `cutoff_point` decimal(8,2) NOT NULL DEFAULT '42000.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employees_email_unique` (`email`),
  UNIQUE KEY `employees_pps_number_unique` (`pps_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 11. Table structure for `payslips`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `payslips`;
CREATE TABLE `payslips` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `gross_pay` decimal(10,2) NOT NULL,
  `paye` decimal(10,2) NOT NULL,
  `usc` decimal(10,2) NOT NULL,
  `prsi` decimal(10,2) NOT NULL,
  `employer_prsi` decimal(10,2) NOT NULL,
  `net_pay` decimal(10,2) NOT NULL,
  `hours_worked` decimal(8,2) NOT NULL DEFAULT '0.00',
  `overtime_hours` decimal(8,2) NOT NULL DEFAULT '0.00',
  `bonus` decimal(8,2) NOT NULL DEFAULT '0.00',
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payslips_employee_id_foreign` (`employee_id`),
  CONSTRAINT `payslips_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 12. Table structure for `tax_liabilities`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `tax_liabilities`;
CREATE TABLE `tax_liabilities` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tax_period` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `paye` decimal(12,2) NOT NULL DEFAULT '0.00',
  `usc` decimal(12,2) NOT NULL DEFAULT '0.00',
  `prsi_employee` decimal(12,2) NOT NULL DEFAULT '0.00',
  `prsi_employer` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total_liability` decimal(12,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tax_liabilities_tax_period_unique` (`tax_period`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 13. Table structure for `tax_settings`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `tax_settings`;
CREATE TABLE `tax_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tax_settings_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Seed Data: Tax Settings
-- --------------------------------------------------------
INSERT INTO `tax_settings` (`key`, `value`, `display_name`, `category`, `type`, `created_at`, `updated_at`) VALUES
('paye_standard_rate', '0.20', 'PAYE Standard Rate', 'paye', 'percentage', NOW(), NOW()),
('paye_higher_rate', '0.40', 'PAYE Higher Rate', 'paye', 'percentage', NOW(), NOW()),
('paye_annual_cutoff', '42000.00', 'PAYE Standard Annual Cutoff Point', 'paye', 'amount', NOW(), NOW()),
('paye_annual_credit', '3750.00', 'PAYE Annual Tax Credit', 'paye', 'amount', NOW(), NOW()),
('usc_band_1_limit', '12012.00', 'USC Band 1 Limit (Annual)', 'usc', 'amount', NOW(), NOW()),
('usc_band_1_rate', '0.005', 'USC Band 1 Rate', 'usc', 'percentage', NOW(), NOW()),
('usc_band_2_limit', '27382.00', 'USC Band 2 Limit (Annual)', 'usc', 'amount', NOW(), NOW()),
('usc_band_2_rate', '0.02', 'USC Band 2 Rate', 'usc', 'percentage', NOW(), NOW()),
('usc_band_3_limit', '70044.00', 'USC Band 3 Limit (Annual)', 'usc', 'amount', NOW(), NOW()),
('usc_band_3_rate', '0.03', 'USC Band 3 Rate', 'usc', 'percentage', NOW(), NOW()),
('usc_band_4_rate', '0.08', 'USC Band 4 Rate (Balance)', 'usc', 'percentage', NOW(), NOW()),
('prsi_employee_rate', '0.04', 'PRSI Employee Rate (Class A)', 'prsi', 'percentage', NOW(), NOW()),
('prsi_employee_threshold', '352.00', 'PRSI Employee Weekly Threshold', 'prsi', 'amount', NOW(), NOW()),
('prsi_employee_max_credit', '12.00', 'PRSI Employee Max Weekly Credit', 'prsi', 'amount', NOW(), NOW()),
('prsi_employee_credit_tapered_threshold', '424.00', 'PRSI Employee Credit Tapered Weekly Limit', 'prsi', 'amount', NOW(), NOW()),
('prsi_employer_lower_rate', '0.088', 'PRSI Employer Lower Rate', 'prsi', 'percentage', NOW(), NOW()),
('prsi_employer_lower_threshold', '441.00', 'PRSI Employer Lower Weekly Threshold', 'prsi', 'amount', NOW(), NOW()),
('prsi_employer_higher_rate', '0.1105', 'PRSI Employer Higher Rate', 'prsi', 'percentage', NOW(), NOW());

-- --------------------------------------------------------
-- Seed Data: Sample Employees
-- --------------------------------------------------------
INSERT INTO `employees` (`name`, `email`, `pps_number`, `department`, `job_title`, `hourly_rate`, `salary`, `active`, `tax_credit`, `cutoff_point`, `created_at`, `updated_at`) VALUES
('Sean Higgins', 'sean.higgins@enterprise.ie', '1234567HA', 'Software Development', 'Senior Architect', 0.00, 85000.00, 1, 3750.00, 42000.00, NOW(), NOW()),
('Niamh Kelly', 'niamh.kelly@enterprise.ie', '9876543KB', 'HR & Operations', 'HR Director', 0.00, 52000.00, 1, 3750.00, 42000.00, NOW(), NOW()),
('Liam Murphy', 'liam.murphy@enterprise.ie', '7654321MA', 'Customer Success', 'Support Engineer', 24.50, 0.00, 1, 3750.00, 42000.00, NOW(), NOW()),
('Aoife Boyle', 'aoife.boyle@enterprise.ie', '4567890BA', 'Marketing', 'Growth Manager', 0.00, 42000.00, 1, 3750.00, 42000.00, NOW(), NOW());

COMMIT;
SET FOREIGN_KEY_CHECKS = 1;
```
